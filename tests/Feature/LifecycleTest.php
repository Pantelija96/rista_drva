<?php

namespace Tests\Feature;

use App\Mail\NewSchoolApplication;
use App\Models\GalleryAlbum;
use App\Models\SchoolApplication;
use App\Models\SiteStatistic;
use App\Models\TreeGroup;
use App\Models\TreeSpecies;
use App\Models\User;
use App\Services\ApproveSchoolApplication;
use App\Services\BulkRegisterTrees;
use App\Services\ChangeSchoolApplicationStatus;
use App\Services\Outbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class LifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_to_tree_gallery_and_statistics_lifecycle(): void
    {
        Mail::fake();
        Storage::fake('public');
        config(['ecosync.notification_email' => 'ngo@example.test']);
        $this->post('/sr/pridruzi-se', [
            'school_name' => 'Test lifecycle škola', 'city' => 'Beograd', 'contact_person' => 'Test predstavnik', 'email' => 'school@example.test',
            'desired_planting_model' => 'one_tree_per_graduate', 'privacy_consent' => 1, 'submission_token' => (string) Str::uuid(),
        ])->assertSessionHasNoErrors()->assertRedirect();
        $application = SchoolApplication::firstOrFail();
        app(Outbox::class)->drain();
        Mail::assertSent(NewSchoolApplication::class);
        $admin = new User(['name' => 'Test administrator', 'email' => 'admin@example.test', 'password' => 'test-only-123456']);
        $admin->is_admin = true;
        $admin->save();
        $this->actingAs($admin)->get('/admin')->assertOk();
        app(ChangeSchoolApplicationStatus::class)->handle($application, 'reviewing');
        $school = app(ApproveSchoolApplication::class)->handle($application, null, 2026, 'one_tree_per_graduate');
        $school->update(['status' => 'published', 'latitude' => 44.678, 'longitude' => 20.718]);
        $generation = $school->generations()->firstOrFail();
        $generation->update(['status' => 'published']);
        $this->assertSame(2026, $generation->graduation_year);
        $species = TreeSpecies::create(['common_name' => 'Test lipa', 'published' => true]);
        $group = TreeGroup::create(['name' => 'Test grupa', 'school_id' => $school->id, 'school_generation_id' => $generation->id, 'declared_tree_count' => 20, 'published' => true, 'latitude' => 44.679, 'longitude' => 20.719]);
        $trees = app(BulkRegisterTrees::class)->handle(['school_id' => $school->id, 'school_generation_id' => $generation->id, 'tree_group_id' => $group->id, 'tree_species_id' => $species->id], 3);
        foreach ($trees as $index => $tree) {
            $tree->update(['latitude' => 44.679 + $index / 10000, 'longitude' => 20.719 + $index / 10000, 'public_visibility' => true, 'adoption_status' => 'available']);
        }
        $this->getJson('/sr/mapa/podaci')->assertOk()->assertJsonCount(5, 'markers');
        $this->getJson('/sr/mapa/podaci?category=group')->assertJsonPath('markers.0.declared_tree_count', 20)->assertJsonPath('markers.0.registered_tree_count', 3);
        $album = GalleryAlbum::create(['title' => 'Test album', 'school_id' => $school->id, 'school_generation_id' => $generation->id, 'published' => true]);
        foreach ([1, 2] as $index) {
            $album->images()->create(['image_path' => UploadedFile::fake()->image('test.jpg', 200, 200)->store('ecosync', 'public'), 'alt_text' => 'Test fotografija '.$index, 'published' => true]);
        }
        $this->get('/sr/galerija/'.$album->slug)->assertOk()->assertSee('Test fotografija 1')->assertSee('Test fotografija 2');
        SiteStatistic::create(['key' => 'lifecycle_statistic', 'label' => 'Test pokazatelj', 'value' => 123]);
        $this->get('/sr')->assertOk()->assertSee('Test pokazatelj')->assertSee('123');
    }
}
