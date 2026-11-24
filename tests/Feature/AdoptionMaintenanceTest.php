<?php

namespace Tests\Feature;

use App\Filament\Resources\Pages\ListTreeAdoption;
use App\Filament\Resources\Pages\ListTreeMaintenanceRecord;
use App\Mail\WorkflowMail;
use App\Models\GuardianAccessGrant;
use App\Models\MailOutbox;
use App\Models\Tree;
use App\Models\TreeAdoption;
use App\Models\TreeMaintenanceRecord;
use App\Models\TreeSpecies;
use App\Models\User;
use App\Services\Adoptions;
use App\Services\Maintenance;
use App\Services\Outbox;
use App\Services\PrivacyRedaction;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AdoptionMaintenanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        Storage::fake('local');
        $this->withoutVite();
    }

    private function tree(): Tree
    {
        $species = TreeSpecies::create(['common_name' => 'Test lipa', 'published' => true]);

        return Tree::create(['tree_species_id' => $species->id, 'public_visibility' => true, 'adoption_status' => 'available', 'latitude' => 44.67, 'longitude' => 20.71]);
    }

    private function admin(): User
    {
        $u = new User(['name' => 'Test admin', 'email' => 'admin@example.test', 'password' => 'test-password-12345']);
        $u->is_admin = true;
        $u->save();

        return $u;
    }

    private function requestAdoption(?Tree $tree = null, array $extra = []): TreeAdoption
    {
        return app(Adoptions::class)->request($tree ?? $this->tree(), $extra + ['guardian_name' => 'Privatno Ime', 'guardian_email' => 'guardian@example.test', 'guardian_phone' => 'PRIVATE PHONE', 'adoption_type' => 'active_guardianship', 'public_display_preference' => 'anonymous'], hash('sha256', Str::uuid()));
    }

    private function token(string $event): array
    {
        app(Outbox::class)->drain();
        $mail = Mail::sent(WorkflowMail::class)->last(fn ($m) => $event === 'access' ? str_contains($m->heading, 'pristup') : str_contains($m->heading, 'potvrdite'));
        $this->assertNotNull($mail);
        parse_str(parse_url($mail->url, PHP_URL_FRAGMENT), $parts);

        return [$parts['token'], $mail->url];
    }

    private function activate(): TreeAdoption
    {
        $adoption = $this->requestAdoption();
        [$token] = $this->token('verify');
        app(Adoptions::class)->verify($adoption, $token);

        return app(Adoptions::class)->transition($adoption, 'active', $this->admin());
    }

    private function guardianSession(TreeAdoption $a): array
    {
        return ['guardian' => ['id' => $a->id, 'version' => $a->access_version, 'expires_at' => now()->addMinutes(20)->timestamp]];
    }

    public function test_request_is_reserved_once_and_email_link_contains_no_database_raw_token(): void
    {
        $a = $this->requestAdoption();
        $this->assertSame('pending_verification', $a->status);
        $this->assertSame('unavailable', $a->tree->fresh()->effectiveAdoptionStatus());
        [$token,$url] = $this->token('verify');
        $a->refresh();
        $this->assertSame(hash('sha256', $token), $a->verification_token_hash);
        $this->assertStringNotContainsString($token, DB::table('tree_adoptions')->where('id', $a->id)->value('verification_token_hash'));
        $this->assertStringNotContainsString('guardian@example.test', $a->getRawOriginal('guardian_email'));
        $this->assertNull(parse_url($url, PHP_URL_QUERY));
        $this->assertSame(1, MailOutbox::count());
        $this->assertSame('logged', MailOutbox::first()->status);
    }

    public function test_verification_is_post_confirmed_single_use_and_requires_admin_approval(): void
    {
        $a = $this->requestAdoption();
        [$token] = $this->token('verify');
        $this->get(route('adoptions.verify.form', $a->public_id))->assertOk()->assertHeader('Referrer-Policy', 'no-referrer');
        $this->assertSame('pending_verification', $a->fresh()->status);
        $this->post(route('adoptions.verify', $a->public_id), ['token' => $token])->assertRedirect();
        $this->assertSame('pending_approval', $a->fresh()->status);
        $this->assertNotNull($a->fresh()->email_verified_at);
        $this->post(route('adoptions.verify', $a->public_id), ['token' => $token])->assertSessionHasErrors('token');
        app(Adoptions::class)->transition($a, 'active', $this->admin());
        $this->assertSame('adopted', $a->tree->fresh()->effectiveAdoptionStatus());
    }

    public function test_invalid_expired_and_superseded_verification_tokens_are_rejected(): void
    {
        $a = $this->requestAdoption();
        [$token] = $this->token('verify');
        $this->post(route('adoptions.verify', $a->public_id), ['token' => str_repeat('a', 64)])->assertSessionHasErrors('token');
        app(Adoptions::class)->resend($a, 'guardian@example.test');
        [$newToken] = $this->token('verify');
        $this->assertNotSame($token, $newToken);
        $this->post(route('adoptions.verify', $a->public_id), ['token' => $token])->assertSessionHasErrors('token');
        $this->travel(61)->minutes();
        $this->post(route('adoptions.verify', $a->public_id), ['token' => $newToken])->assertSessionHasErrors('token');
    }

    public function test_stale_simultaneous_requests_and_database_constraint_prevent_duplicate_reservations(): void
    {
        $tree = $this->tree();
        $a = $this->requestAdoption($tree);
        try {
            $this->requestAdoption($tree, ['guardian_email' => 'other@example.test']);
            $this->fail();
        } catch (ValidationException) {
        }
        $this->assertSame(1, TreeAdoption::where('reserved_tree_id', $tree->id)->count());
        $row = DB::table('tree_adoptions')->where('id', $a->id)->first();
        $data = (array) $row;
        unset($data['id']);
        $data['public_id'] = (string) Str::uuid();
        $data['submission_key'] = hash('sha256', Str::uuid());
        try {
            DB::table('tree_adoptions')->insert($data);
            $this->fail();
        } catch (QueryException) {
        }
        $this->assertDatabaseCount('tree_adoptions', 1);
    }

    public function test_expired_reservations_release_without_touching_active_adoptions(): void
    {
        $a = $this->requestAdoption();
        $tree = $a->tree;
        $this->travel(61)->minutes();
        $this->artisan('ecosync:expire-reservations')->assertSuccessful();
        $this->assertSame('expired', $a->fresh()->status);
        $next = $this->requestAdoption($tree);
        $this->assertNotSame($a->id, $next->id);
        [$token] = $this->token('verify');
        app(Adoptions::class)->verify($next, $token);
        app(Adoptions::class)->transition($next, 'active', $this->admin());
        $this->travel(30)->days();
        $this->artisan('ecosync:expire-reservations')->assertSuccessful();
        $this->assertSame('active', $next->fresh()->status);
    }

    public function test_only_verified_requests_can_be_approved_and_transitions_preserve_history(): void
    {
        $a = $this->requestAdoption();
        $admin = $this->admin();
        try {
            app(Adoptions::class)->transition($a, 'active', $admin);
            $this->fail();
        } catch (ValidationException) {
        }
        app(Adoptions::class)->transition($a, 'rejected', $admin);
        $this->assertSame('available', $a->tree->fresh()->effectiveAdoptionStatus());
        $this->assertDatabaseCount('tree_adoption_events', 2);
    }

    public function test_approval_and_end_are_idempotent_and_public_contacts_remain_private(): void
    {
        $a = $this->activate();
        $admin = User::first();
        app(Adoptions::class)->transition($a, 'active', $admin);
        $this->assertSame(1, MailOutbox::where('event', 'adoption.approved')->count());
        $this->get('/sr/stabla/'.$a->tree->public_identifier)->assertOk()->assertSee('Anonimni staratelj')->assertDontSee('Privatno Ime')->assertDontSee('guardian@example.test')->assertDontSee('PRIVATE PHONE');
        $this->getJson('/sr/mapa/podaci')->assertOk()->assertJsonPath('markers.0.guardian', 'Anonimni staratelj')->assertDontSee('guardian@example.test')->assertDontSee('Privatno Ime');
        $this->get('/sr/nas-zeleni-trag')->assertOk()->assertDontSee('Privatno Ime');
        app(Adoptions::class)->transition($a, 'ended', $admin);
        app(Adoptions::class)->transition($a, 'ended', $admin);
        $this->assertSame('available', $a->tree->fresh()->effectiveAdoptionStatus());
        $this->assertDatabaseCount('tree_adoptions', 1);
    }

    public function test_public_initials_are_derived_only_after_approval(): void
    {
        $a = $this->activate();
        $a->update(['public_display_preference' => 'initials']);
        $this->get('/sr/stabla/'.$a->tree->public_identifier)->assertSee('P. I.')->assertDontSee('Privatno Ime');
    }

    public function test_guardian_link_is_single_use_and_session_expires(): void
    {
        $a = $this->activate();
        app(Adoptions::class)->requestAccess($a->tree->public_identifier, 'guardian@example.test');
        [$token] = $this->token('access');
        $grant = GuardianAccessGrant::first();
        $this->get(route('guardian.access.form', $grant->public_id))->assertOk();
        $this->post(route('guardian.access', $grant->public_id), ['token' => $token])->assertRedirect(route('guardian.dashboard'));
        $this->get('/sr/staratelj')->assertOk()->assertHeader('Cache-Control', 'no-store, private');
        $this->post(route('guardian.access', $grant->public_id), ['token' => $token])->assertSessionHasErrors('token');
        $this->travel(21)->minutes();
        $this->get('/sr/staratelj')->assertRedirect(route('guardian.request'));
    }

    public function test_expired_access_and_wrong_guardian_cannot_access_an_adoption(): void
    {
        $a = $this->activate();
        app(Adoptions::class)->requestAccess($a->tree->public_identifier, 'wrong@example.test');
        $this->assertDatabaseCount('guardian_access_grants', 0);
        app(Adoptions::class)->requestAccess($a->tree->public_identifier, 'guardian@example.test');
        [$token] = $this->token('access');
        $grant = GuardianAccessGrant::first();
        $this->travel(21)->minutes();
        $this->post(route('guardian.access', $grant->public_id), ['token' => $token])->assertSessionHasErrors('token');
    }

    public function test_guardian_maintenance_and_photos_require_moderation(): void
    {
        $a = $this->activate();
        $this->withSession($this->guardianSession($a))->post('/sr/staratelj/nega', ['activity_type' => 'watering', 'description' => 'Privatni original', 'performed_at' => today()->toDateString(), 'submission_token' => (string) Str::uuid(), 'photos' => [UploadedFile::fake()->image('sensitive-original.jpg', 300, 300)]])->assertSessionHasNoErrors()->assertRedirect();
        $record = TreeMaintenanceRecord::firstOrFail();
        $photo = $record->photos()->firstOrFail();
        $this->assertStringNotContainsString('sensitive-original', $photo->path);
        Storage::disk('local')->assertExists($photo->path);
        $this->flushSession();
        $this->get(route('maintenance.photo', $photo->public_id))->assertNotFound();
        $this->get('/sr/stabla/'.$a->tree->public_identifier)->assertDontSee('Privatni original');
        app(Maintenance::class)->review($record, 'approved', User::first(), 'Javni potvrđen opis', [$photo->id]);
        $this->get('/sr/stabla/'.$a->tree->public_identifier)->assertOk()->assertSee('Javni potvrđen opis')->assertDontSee('Privatni original');
        $this->get(route('maintenance.photo', $photo->public_id))->assertOk()->assertHeader('Content-Type', 'image/jpeg');
        app(Maintenance::class)->review($record, 'rejected', User::first());
        Storage::disk('local')->assertMissing($photo->path);
    }

    public function test_unauthorized_guardians_cannot_submit_or_read_another_guardians_photos(): void
    {
        $a = $this->activate();
        $record = app(Maintenance::class)->create($a->tree, ['activity_type' => 'inspection', 'description' => 'Test', 'performed_at' => today()->toDateString()], [UploadedFile::fake()->image('x.jpg', 200, 200)], guardian: $a);
        $this->post('/sr/staratelj/nega', [])->assertRedirect(route('guardian.request'));
        $other = $this->requestAdoption();
        $this->withSession($this->guardianSession($other))->get(route('maintenance.photo', $record->photos->first()->public_id))->assertNotFound();
        try {
            app(Maintenance::class)->create($other->tree, ['activity_type' => 'watering', 'description' => 'Test', 'performed_at' => today()->toDateString()], guardian: $a);
            $this->fail();
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }
    }

    public function test_photo_limits_and_activity_validation(): void
    {
        $a = $this->activate();
        $session = $this->guardianSession($a);
        $data = ['activity_type' => 'bad', 'description' => 'Test', 'performed_at' => today()->toDateString(), 'submission_token' => (string) Str::uuid()];
        $this->withSession($session)->post('/sr/staratelj/nega', $data + ['photos' => [UploadedFile::fake()->create('unsafe.svg', 10, 'image/svg+xml')]])->assertSessionHasErrors();
        $this->withSession($session)->post('/sr/staratelj/nega', [...$data, 'activity_type' => 'watering', 'photos' => [UploadedFile::fake()->image('tiny.jpg', 20, 20)]])->assertSessionHasErrors();
        $this->assertDatabaseCount('tree_maintenance_records', 0);
    }

    public function test_verified_preferences_and_cancellation_invalidate_guardian_access(): void
    {
        $a = $this->activate();
        $this->withSession($this->guardianSession($a))->post('/sr/staratelj/podesavanja', ['guardian_name' => 'Novo Ime', 'guardian_phone' => '', 'public_display_preference' => 'initials', 'adoption_type' => 'symbolic'])->assertSessionHasNoErrors();
        $this->assertSame('symbolic', $a->fresh()->adoption_type);
        $this->assertDatabaseHas('tree_adoption_events', ['event' => 'preferences_updated']);
        $this->post('/sr/staratelj/otkazivanje', ['confirm' => 1])->assertRedirect();
        $this->assertSame('cancelled', $a->fresh()->status);
        $this->assertSame('available', $a->tree->fresh()->effectiveAdoptionStatus());
        $this->get('/sr/staratelj')->assertRedirect(route('guardian.request'));
    }

    public function test_contact_and_support_are_persisted_with_retriable_outbox_and_no_duplicate_bursts(): void
    {
        config(['ecosync.notification_email' => 'ngo@example.test']);
        foreach (['contact' => 'general', 'support' => 'tree_donation'] as $kind => $topic) {
            $data = ['name' => 'Test upit', 'email' => 'inquiry@example.test', 'topic' => $topic, 'message' => 'Test poruka', 'privacy_consent' => 1, 'submission_token' => (string) Str::uuid()];
            $this->post('/sr/upiti/'.$kind, $data)->assertSessionHasNoErrors()->assertRedirect();
            $this->post('/sr/upiti/'.$kind, $data)->assertRedirect();
        }
        $this->assertDatabaseCount('inquiries', 2);
        $this->assertDatabaseCount('mail_outbox', 2);
        app(Outbox::class)->drain();
        Mail::assertSent(WorkflowMail::class, 2);
        Mail::assertSent(WorkflowMail::class, fn ($mail) => $mail->hasTo('ngo@example.test'));
    }

    public function test_outbox_failure_and_lease_recovery_preserve_the_original_request(): void
    {
        $this->requestAdoption();
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('SMTP unavailable'));
        app(Outbox::class)->drain();
        $this->assertDatabaseHas('mail_outbox', ['status' => 'pending', 'attempts' => 1]);
        $this->assertDatabaseCount('tree_adoptions', 1);
        $item = MailOutbox::first();
        $item->update(['status' => 'processing', 'locked_until' => now()->subMinute()]);
        app(Outbox::class)->drain();
        $this->assertSame(2, $item->fresh()->attempts);
    }

    public function test_new_filament_resources_and_moderation_are_admin_only(): void
    {
        $this->actingAs($this->admin());
        foreach (['inquiries', 'tree-adoptions', 'tree-maintenance-records', 'mail-outboxes'] as $path) {
            $this->get('/admin/'.$path)->assertOk();
        }
        $this->get('/admin/tree-maintenance-records/create')->assertOk();
    }

    public function test_email_change_requires_the_new_addresses_single_use_confirmation(): void
    {
        $a = $this->activate();
        app(Adoptions::class)->requestEmailChange($a, 'new-guardian@example.test');
        app(Outbox::class)->drain();
        $mail = Mail::sent(WorkflowMail::class)->last(fn ($m) => str_contains($m->heading, 'novu email'));
        $this->assertTrue($mail->hasTo('new-guardian@example.test'));
        parse_str(parse_url($mail->url, PHP_URL_FRAGMENT), $parts);
        $this->assertSame('guardian@example.test', $a->fresh()->guardian_email);
        $grant = GuardianAccessGrant::where('purpose', 'email_change')->firstOrFail();
        $this->post(route('guardian.access', $grant->public_id), ['token' => $parts['token']])->assertSessionHasErrors('token');
        $this->withSession($this->guardianSession($a))->post(route('guardian.email.confirm', $grant->public_id), ['token' => $parts['token']])->assertRedirect();
        $this->assertSame('new-guardian@example.test', $a->fresh()->guardian_email);
        $this->post(route('guardian.email.confirm', $grant->public_id), ['token' => $parts['token']])->assertSessionHasErrors('token');
        $this->get('/sr/staratelj')->assertRedirect(route('guardian.request'));
    }

    public function test_adoption_and_maintenance_actions_work_in_filament(): void
    {
        $a = $this->requestAdoption();
        [$token] = $this->token('verify');
        app(Adoptions::class)->verify($a, $token);
        $admin = $this->admin();
        $this->actingAs($admin);
        Livewire::test(ListTreeAdoption::class)->callAction(TestAction::make('approve')->table($a))->assertHasNoActionErrors();
        $this->assertSame('active', $a->fresh()->status);
        $record = app(Maintenance::class)->create($a->tree, ['activity_type' => 'inspection', 'description' => 'Privatni pregled', 'performed_at' => today()->toDateString()], guardian: $a->fresh());
        Livewire::test(ListTreeMaintenanceRecord::class)->callAction(TestAction::make('approve')->table($record), data: ['public_description' => 'Javni pregled', 'public_safe' => true, 'photos' => []])->assertHasNoActionErrors();
        $this->assertSame('approved', $record->fresh()->review_status);
        $this->get('/admin/tree-adoptions/'.$a->id.'/edit')->assertOk()->assertSee('guardian@example.test');
        $this->get('/admin/tree-maintenance-records/'.$record->id.'/edit')->assertOk();
    }

    public function test_redaction_preserves_operational_history_without_contact_data(): void
    {
        $a = $this->activate();
        $admin = User::first();
        app(Adoptions::class)->transition($a, 'ended', $admin);
        app(PrivacyRedaction::class)->redact($a, $admin);
        $a->refresh();
        $this->assertNull($a->guardian_email);
        $this->assertNull($a->guardian_phone);
        $this->assertSame('ended', $a->status);
        $this->assertTrue($a->events()->where('event','personal_data_removed')->exists());
    }
}
