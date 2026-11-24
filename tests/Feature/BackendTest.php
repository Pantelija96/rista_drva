<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\CreateGalleryImage;
use App\Filament\Resources\Pages\CreateSchool;
use App\Filament\Resources\Pages\CreateTree;
use App\Filament\Resources\Pages\CreateUser;
use App\Filament\Resources\Pages\EditGalleryAlbum;
use App\Filament\Resources\Pages\EditSiteStatistic;
use App\Filament\Resources\Pages\ListSchoolApplication;
use App\Mail\NewSchoolApplication;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\School;
use App\Models\SchoolApplication;
use App\Models\SchoolGeneration;
use App\Models\SiteStatistic;
use App\Models\Tree;
use App\Models\TreeGroup;
use App\Models\TreeSpecies;
use App\Models\User;
use App\Services\ApproveSchoolApplication;
use App\Services\BulkRegisterTrees;
use App\Services\ChangeSchoolApplicationStatus;
use App\Services\Outbox;
use Database\Seeders\SiteStatisticSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Auth\Pages\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class BackendTest extends TestCase
{
    use RefreshDatabase;

    private function school(array $data = []): School
    {
        return School::create($data + ['name' => 'Test škola', 'slug' => 'skola-'.uniqid(), 'city' => 'Beograd', 'status' => 'published', 'contact_email' => 'private@example.test']);
    }

    private function species(array $data = []): TreeSpecies
    {
        return TreeSpecies::create($data + ['common_name' => 'Lipa', 'slug' => 'lipa-'.uniqid(), 'published' => true]);
    }

    private function generation(School $school): SchoolGeneration
    {
        return SchoolGeneration::create(['school_id' => $school->id, 'graduation_year' => 2026, 'planting_model' => 'symbolic_generation_tree', 'status' => 'published']);
    }

    private function administrator(): User
    {
        $user = new User(['name' => 'Test admin', 'email' => 'admin@example.test', 'password' => 'test-secure-12345']);
        $user->is_admin = true;
        $user->save();

        return $user;
    }

    private function application(): SchoolApplication
    {
        return SchoolApplication::create(['school_name' => 'Test škola', 'city' => 'Beograd', 'contact_person' => 'Kontakt', 'email' => 'private@example.test', 'desired_planting_model' => 'one_tree_per_graduate', 'privacy_consent' => true, 'consent_timestamp' => now(), 'submission_key' => hash('sha256', uniqid())]);
    }

    private function payload(): array
    {
        return ['school_name' => 'Test škola', 'city' => 'Beograd', 'contact_person' => 'Kontakt', 'email' => 'school@example.test', 'desired_planting_model' => 'undecided', 'privacy_consent' => '1', 'submission_token' => (string) Str::uuid()];
    }

    public function test_admin_access_login_and_guest_restrictions(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk();
        $this->get('/admin/register')->assertNotFound();
        $user = $this->administrator();
        $this->assertTrue(Hash::check('test-secure-12345', $user->password));
        Livewire::test(Login::class)->fillForm(['email' => $user->email, 'password' => 'test-secure-12345'])->call('authenticate')->assertHasNoFormErrors();
        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertOk();
        $this->post('/admin/logout')->assertRedirect();
        $this->assertGuest();
    }

    public function test_non_admin_is_denied_panel_and_policies(): void
    {
        $user = User::create(['name' => 'User', 'email' => 'user@example.test', 'password' => 'test-secure-12345']);
        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->assertFalse(Gate::forUser($user)->allows('create', School::class));
    }

    public function test_admin_resources_render_including_private_contact_fields(): void
    {
        $this->actingAs($this->administrator());
        foreach (['schools', 'school-generations', 'tree-species', 'tree-groups', 'trees', 'gallery-albums', 'gallery-images', 'site-statistics', 'users'] as $resource) {
            $this->get('/admin/'.$resource)->assertOk();
            $this->get('/admin/'.$resource.'/create')->assertOk();
        }
        $application = $this->application();
        $this->get('/admin/school-applications/'.$application->id.'/edit')->assertOk()->assertSee('private@example.test');
        $this->get('/admin/school-applications/create')->assertForbidden();
        $this->get('/admin/settings')->assertOk();
        $this->get('/admin/map')->assertOk();
    }

    public function test_school_application_is_validated_persisted_notified_and_retries_are_safe(): void
    {
        Mail::fake();
        config(['ecosync.notification_email' => 'notifications@example.test']);
        $payload = $this->payload();
        $payload['school_name'] = '<b>Test škola</b>';
        $this->post('/sr/pridruzi-se', $payload)->assertSessionHasNoErrors()->assertRedirect(route('join').'#prijava-skole');
        $this->post('/sr/pridruzi-se', $payload)->assertRedirect();
        $this->post('/sr/pridruzi-se', [...$payload, 'submission_token' => (string) Str::uuid()])->assertRedirect();
        $this->assertDatabaseCount('school_applications', 1);
        $application = SchoolApplication::first();
        $this->assertSame('Test škola', $application->school_name);
        $this->assertNotNull($application->consent_timestamp);
        $this->assertNull($application->notification_sent_at);
        $this->assertDatabaseCount('mail_outbox', 2);
        app(Outbox::class)->drain();
        Mail::assertSent(NewSchoolApplication::class, 1);
        Mail::assertSent(NewSchoolApplication::class, fn ($mail) => $mail->hasTo('notifications@example.test') && str_contains($mail->render(), '/admin/school-applications/'.$application->id.'/edit'));
    }

    public function test_invalid_consent_honeypot_and_models_are_rejected(): void
    {
        $payload = $this->payload();
        $this->post('/sr/pridruzi-se', [...$payload, 'privacy_consent' => false, 'email' => 'invalid', 'desired_planting_model' => 'fake', 'website' => 'spam'])->assertSessionHasErrors(['privacy_consent', 'email', 'desired_planting_model', 'website']);
        $this->assertDatabaseCount('school_applications', 0);
    }

    public function test_application_rate_limiting(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/sr/pridruzi-se', [])->assertRedirect();
        }
        $this->post('/sr/pridruzi-se', [])->assertStatus(429);
    }

    public function test_mail_failure_preserves_saved_application(): void
    {
        config(['ecosync.notification_email' => 'notifications@example.test']);
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Transport failed'));
        $this->post('/sr/pridruzi-se', $this->payload())->assertSessionHasNoErrors()->assertRedirect();
        app(Outbox::class)->drain();
        $this->assertDatabaseCount('school_applications', 1);
        $this->assertDatabaseHas('mail_outbox', ['status' => 'pending', 'attempts' => 1]);
        $this->assertNull(SchoolApplication::first()->notification_sent_at);
    }

    public function test_approval_is_transactional_idempotent_and_creates_generation(): void
    {
        $application = $this->application();
        $service = app(ApproveSchoolApplication::class);
        $school = $service->handle($application, null, 2026, 'one_tree_per_graduate');
        $this->assertSame($school->id, $service->handle($application)->id);
        $this->assertDatabaseCount('schools', 1);
        $this->assertDatabaseCount('school_generations', 1);
        $this->assertSame('approved', $application->fresh()->status);
        $this->assertSame('draft', $school->status);
    }

    public function test_existing_school_requires_explicit_selection_and_rejected_application_cannot_be_approved(): void
    {
        $school = $this->school();
        $application = $this->application();
        $service = app(ApproveSchoolApplication::class);
        try {
            $service->handle($application);
            $this->fail('An explicit school selection is required.');
        } catch (ValidationException) {
        }
        $this->assertSame($school->id, $service->handle($application, $school->id)->id);
        $rejected = $this->application();
        $rejected->update(['status' => 'rejected']);
        $this->expectException(ValidationException::class);
        $service->handle($rejected);
    }

    public function test_invalid_generation_rolls_back_school_approval(): void
    {
        $application = $this->application();
        try {
            app(ApproveSchoolApplication::class)->handle($application, null, 2026, 'undecided');
            $this->fail();
        } catch (ValidationException) {
        }
        $this->assertDatabaseCount('schools', 0);
        $this->assertSame('pending', $application->fresh()->status);
    }

    public function test_tree_group_bulk_limit_identifiers_and_missing_coordinates(): void
    {
        $school = $this->school();
        $generation = $this->generation($school);
        $species = $this->species();
        $group = TreeGroup::create(['name' => 'Grupa', 'slug' => 'grupa', 'school_id' => $school->id, 'school_generation_id' => $generation->id, 'declared_tree_count' => 3]);
        $data = ['school_id' => $school->id, 'school_generation_id' => $generation->id, 'tree_group_id' => $group->id, 'tree_species_id' => $species->id];
        $trees = app(BulkRegisterTrees::class)->handle($data, 3);
        $this->assertCount(3, array_unique(array_map(fn ($tree) => $tree->public_identifier, $trees)));
        $this->assertStringStartsWith('ECO-', $trees[0]->public_identifier);
        $this->assertNull($trees[0]->latitude);
        try {
            app(BulkRegisterTrees::class)->handle($data, 2);
            $this->fail();
        } catch (ValidationException) {
        }
        $this->assertDatabaseCount('trees', 3);
        try {
            $group->update(['declared_tree_count' => 2]);
            $this->fail();
        } catch (ValidationException) {
        }
        $this->assertSame(3, $group->fresh()->declared_tree_count);
    }

    public function test_school_generation_and_group_must_match(): void
    {
        $school = $this->school();
        $other = $this->school();
        $generation = $this->generation($school);
        $this->expectException(ValidationException::class);
        Tree::create(['tree_species_id' => $this->species()->id, 'school_id' => $other->id, 'school_generation_id' => $generation->id]);
    }

    public function test_species_and_coordinates_validation(): void
    {
        try {
            Tree::create(['tree_species_id' => 999]);
            $this->fail();
        } catch (ValidationException) {
        }
        try {
            $this->school(['latitude' => 91, 'longitude' => 20]);
            $this->fail();
        } catch (ValidationException) {
        }
        $this->expectException(ValidationException::class);
        $this->species(['typical_height_m' => -1]);
    }

    public function test_generation_duplicates_programs_and_move_protection(): void
    {
        $school = $this->school();
        $generation = $this->generation($school);
        try {
            $this->generation($school);
            $this->fail();
        } catch (ValidationException) {
        }
        SchoolGeneration::create(['school_id' => $school->id, 'graduation_year' => 2026, 'program' => 'other', 'planting_model' => 'symbolic_generation_tree']);
        TreeGroup::create(['name' => 'Grupa', 'slug' => 'g', 'school_id' => $school->id, 'school_generation_id' => $generation->id]);
        $this->expectException(ValidationException::class);
        $generation->update(['school_id' => $this->school()->id]);
    }

    public function test_public_tree_map_privacy_and_parent_visibility(): void
    {
        $school = $this->school(['latitude' => 44.8, 'longitude' => 20.4]);
        $species = $this->species();
        $tree = Tree::create(['tree_species_id' => $species->id, 'school_id' => $school->id, 'latitude' => 44.81, 'longitude' => 20.41, 'public_visibility' => true, 'notes' => 'PRIVATE NOTE']);
        $this->get('/sr/stabla/'.$tree->public_identifier)->assertOk()->assertDontSee('private@example.test')->assertDontSee('PRIVATE NOTE');
        $this->get('/sr/mapa/podaci')->assertOk()->assertJsonCount(2, 'markers')->assertDontSee('private@example.test')->assertDontSee('PRIVATE NOTE')->assertDontSee('contact_email');
        $tree->update(['public_visibility' => false]);
        $this->get('/sr/stabla/'.$tree->public_identifier)->assertNotFound();
        $this->get('/sr/mapa/podaci')->assertJsonCount(1, 'markers');
        $tree->update(['public_visibility' => true]);
        $school->update(['status' => 'draft']);
        $this->get('/sr/stabla/'.$tree->public_identifier)->assertNotFound();
        $this->get('/sr/mapa/podaci')->assertJsonCount(0, 'markers');
    }

    public function test_unlocated_schools_and_groups_do_not_get_invented_coordinates(): void
    {
        $school = $this->school();
        $generation = $this->generation($school);
        $group = TreeGroup::create(['name' => 'Trideset stabala', 'slug' => 'trideset', 'school_id' => $school->id, 'school_generation_id' => $generation->id, 'declared_tree_count' => 30, 'latitude' => 44.8, 'longitude' => 20.4, 'published' => true]);
        app(BulkRegisterTrees::class)->handle(['school_id' => $school->id, 'school_generation_id' => $generation->id, 'tree_group_id' => $group->id, 'tree_species_id' => $this->species()->id], 5);
        $this->get('/sr/mapa/podaci')->assertJsonCount(1, 'markers')->assertJsonPath('markers.0.type', 'group');
        $this->get('/sr/grupe-stabala/trideset')->assertOk()->assertSee('30')->assertSee('5');
        $this->get('/sr/skole/'.$school->slug.'/generacije/'.$generation->id)->assertOk();
        $other = $this->school();
        $this->get('/sr/skole/'.$other->slug.'/generacije/'.$generation->id)->assertNotFound();
    }

    public function test_gallery_publication_image_safety_and_cleanup(): void
    {
        Storage::fake('public');
        $album = GalleryAlbum::create(['title' => 'Album', 'slug' => 'album', 'published' => false]);
        Storage::disk('public')->put('ecosync/first.jpg', 'image');
        $image = $album->images()->create(['image_path' => 'ecosync/first.jpg', 'alt_text' => 'Test slika', 'published' => true]);
        $this->get('/sr/galerija/album')->assertNotFound();
        $album->update(['published' => true]);
        $this->get('/sr/galerija')->assertOk()->assertSee('Album');
        $this->get('/sr/galerija/album')->assertOk()->assertSee('Test slika');
        $image->update(['published' => false]);
        $this->get('/sr/galerija/album')->assertDontSee('Test slika');
        $this->assertDatabaseCount('gallery_images', 1);
    }

    public function test_admin_image_upload_accepts_images_and_rejects_disallowed_files(): void
    {
        Storage::fake('public');
        $this->actingAs($this->administrator());
        $album = GalleryAlbum::create(['title' => 'Album', 'slug' => 'album']);
        Livewire::test(CreateGalleryImage::class)
            ->fillForm(['gallery_album_id' => $album->id, 'image_path' => UploadedFile::fake()->image('approved.jpg', 200, 200), 'alt_text' => 'Fotografija', 'published' => true, 'sort_order' => 0])
            ->call('create')->assertHasNoFormErrors();
        $image = GalleryImage::first();
        Storage::disk('public')->assertExists($image->image_path);
        $this->assertNotSame('approved.jpg', basename($image->image_path));
        Livewire::test(CreateGalleryImage::class)
            ->fillForm(['gallery_album_id' => $album->id, 'image_path' => UploadedFile::fake()->create('script.svg', 10, 'image/svg+xml'), 'alt_text' => 'SVG'])
            ->call('create')->assertHasFormErrors(['image_path']);
    }

    public function test_statistics_editor_and_confirmed_seed_are_not_overwritten(): void
    {
        $this->seed(SiteStatisticSeeder::class);
        $stat = SiteStatistic::where('key', 'volunteers')->first();
        $stat->update(['value' => 250]);
        $this->seed(SiteStatisticSeeder::class);
        $this->assertSame(250, $stat->fresh()->value);
        $this->get('/sr')->assertOk()->assertSee('250+')->assertSee('1.230');
        $this->actingAs($this->administrator());
        Livewire::test(EditSiteStatistic::class, ['record' => $stat->id])->fillForm(['value' => 275])->call('save')->assertHasNoFormErrors();
        $this->get('/sr')->assertSee('275+');
        $this->assertDatabaseMissing('site_statistics', ['key' => 'planting_events']);
    }

    public function test_first_admin_command_and_duplicate_rejection(): void
    {
        $this->artisan('ecosync:create-admin')->expectsQuestion('Ime', 'Admin')->expectsQuestion('Email', 'first@example.test')->expectsQuestion('Lozinka (najmanje 12 znakova)', 'very-secure-12345')->expectsQuestion('Ponovi lozinku', 'very-secure-12345')->assertSuccessful();
        $this->assertTrue(User::first()->is_admin);
        $this->artisan('ecosync:create-admin')->expectsQuestion('Ime', 'Admin')->expectsQuestion('Email', 'first@example.test')->expectsQuestion('Lozinka (najmanje 12 znakova)', 'very-secure-12345')->assertFailed();
        $this->assertDatabaseCount('users', 1);
    }

    public function test_gallery_multi_upload_and_reorder_workflow(): void
    {
        Storage::fake('public');
        $this->actingAs($this->administrator());
        $album = GalleryAlbum::create(['title' => 'Galerija', 'slug' => 'galerija']);
        Livewire::test(EditGalleryAlbum::class, ['record' => $album->id])
            ->callAction('photos', data: ['images' => [UploadedFile::fake()->image('first.jpg', 300, 300), UploadedFile::fake()->image('second.jpg', 300, 300)], 'alt_text' => 'Opis'])
            ->assertHasNoActionErrors();
        $this->assertDatabaseCount('gallery_images', 2);
        foreach ($album->images as $image) {
            Storage::disk('public')->assertExists($image->image_path);
        }
    }

    public function test_admin_approval_action_is_available_and_links_the_school(): void
    {
        $this->actingAs($this->administrator());
        $application = $this->application();
        Livewire::test(ListSchoolApplication::class)
            ->callAction(TestAction::make('approve')->table($application), data: [])
            ->assertHasNoActionErrors();
        $this->assertSame('approved', $application->fresh()->status);
        $this->assertDatabaseCount('schools', 1);
    }

    public function test_file_replacement_and_delete_remove_only_unreferenced_files(): void
    {
        Storage::fake('public');
        // Commit callbacks intentionally wait until the enclosing test transaction commits.
        $album = GalleryAlbum::create(['title' => 'Galerija', 'slug' => 'galerija']);
        Storage::disk('public')->put('ecosync/old.jpg', 'x');
        Storage::disk('public')->put('ecosync/new.jpg', 'y');
        $image = $album->images()->create(['image_path' => 'ecosync/old.jpg', 'alt_text' => 'Slika']);
        $image->update(['image_path' => 'ecosync/new.jpg']);
        DB::commit();
        Storage::disk('public')->assertMissing('ecosync/old.jpg');
        Storage::disk('public')->assertExists('ecosync/new.jpg');
        $image->delete();
        Storage::disk('public')->assertMissing('ecosync/new.jpg');
        $album->delete();
        DB::beginTransaction();
    }

    public function test_identifier_is_immutable_and_last_admin_cannot_be_demoted(): void
    {
        $tree = Tree::create(['tree_species_id' => $this->species()->id]);
        try {
            $tree->public_identifier = 'OTHER';
            $tree->save();
            $this->fail();
        } catch (ValidationException) {
        }
        $admin = $this->administrator();
        $this->expectException(ValidationException::class);
        $admin->is_admin = false;
        $admin->save();
    }

    public function test_admin_relationship_validation_errors_are_attached_to_form_fields(): void
    {
        $this->actingAs($this->administrator());
        $group = TreeGroup::create(['name' => 'Grupa', 'slug' => 'group-limit', 'declared_tree_count' => 0]);
        Livewire::test(CreateTree::class)
            ->fillForm(['tree_species_id' => $this->species()->id, 'tree_group_id' => $group->id, 'physical_condition' => 'planned', 'adoption_status' => 'unavailable', 'public_visibility' => false])
            ->call('create')->assertHasFormErrors(['tree_group_id']);
        $this->assertDatabaseCount('trees', 0);
    }

    public function test_login_rejects_wrong_password_and_throttles_repeated_attempts(): void
    {
        $user = $this->administrator();
        $login = Livewire::test(Login::class)->fillForm(['email' => $user->email, 'password' => 'incorrect-password']);
        for ($i = 0; $i < 5; $i++) {
            $login->call('authenticate');
        }
        $login->call('authenticate')->assertNotified();
        $this->assertGuest();
    }

    public function test_oversized_and_small_images_are_rejected(): void
    {
        Storage::fake('public');
        $this->actingAs($this->administrator());
        $album = GalleryAlbum::create(['title' => 'Album', 'slug' => 'upload-validation']);
        foreach ([UploadedFile::fake()->image('tiny.jpg', 20, 20), UploadedFile::fake()->image('large.jpg', 200, 200)->size(6000)] as $upload) {
            Livewire::test(CreateGalleryImage::class)
                ->fillForm(['gallery_album_id' => $album->id, 'image_path' => $upload, 'alt_text' => 'Slika'])
                ->call('create')->assertHasFormErrors(['image_path']);
        }
        $this->assertDatabaseCount('gallery_images', 0);
    }

    public function test_admin_creation_requires_an_email_and_nonadmin_cannot_execute_livewire_crud(): void
    {
        $this->actingAs($this->administrator());
        Livewire::test(CreateUser::class)
            ->fillForm(['name' => 'Novi admin', 'email' => '', 'password' => 'strong-password-1234', 'is_admin' => true])
            ->call('create')->assertHasFormErrors(['email' => 'required']);
        $ordinary = User::create(['name' => 'Običan nalog', 'email' => 'ordinary@example.test', 'password' => 'strong-password-1234']);
        $this->actingAs($ordinary);
        Livewire::test(CreateSchool::class)->assertForbidden();
    }

    public function test_stale_status_actions_cannot_reject_an_already_approved_request(): void
    {
        $application = $this->application();
        app(ApproveSchoolApplication::class)->handle($application);
        $this->expectException(ValidationException::class);
        app(ChangeSchoolApplicationStatus::class)->handle($application, 'rejected');
    }

    public function test_new_slugs_are_generated_and_confirmed_event_counter_replaces_placeholder(): void
    {
        $school = School::create(['name' => 'Škola sa automatskom oznakom', 'city' => 'Beograd']);
        $this->assertStringStartsWith('skola-sa-automatskom-oznakom-', $school->slug);
        SiteStatistic::create(['key' => 'planting_events', 'label' => 'Održane akcije', 'value' => 12]);
        $this->get('/sr')->assertOk()->assertSee('Održane akcije')->assertDontSee('Realizovane akcije sadnje · čeka se potvrđen broj');
    }
}
