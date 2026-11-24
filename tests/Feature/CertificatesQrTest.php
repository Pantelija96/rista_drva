<?php
namespace Tests\Feature;

use App\Models\{Tree, TreeSpecies, TreeAdoption, User};
use App\Services\{Adoptions, AdoptionCertificates, TreeQr};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;
use Zxing\QrReader;

class CertificatesQrTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        Mail::fake();
        config(['app.url' => 'https://ecosync.example.test']);
    }

    private function adoption(string $status = 'active', string $preference = 'full_name'): TreeAdoption
    {
        $species = TreeSpecies::create(['common_name'=>'Lipa čćšžđ', 'scientific_name'=>'Tilia', 'published'=>true, 'translations'=>['en'=>['common_name'=>'Linden']]]);
        $tree = Tree::create(['tree_species_id'=>$species->id, 'public_visibility'=>true, 'adoption_status'=>'available']);
        $adoption = app(Adoptions::class)->request($tree, ['guardian_name'=>'Čedomir Đorđević', 'guardian_email'=>'private@example.test', 'guardian_phone'=>'PRIVATE PHONE', 'adoption_type'=>'symbolic', 'public_display_preference'=>$preference], hash('sha256',Str::uuid()));
        $adoption->update(['status'=>$status, 'email_verified_at'=>now(), 'approved_at'=>now(), 'internal_notes'=>'PRIVATE NOTES']);
        return $adoption->fresh();
    }

    private function admin(): User
    {
        $user = new User(['name'=>'Test admin', 'email'=>'admin@example.test', 'password'=>'test-password-12345']);
        $user->is_admin=true; $user->save(); return $user;
    }

    private function guardianSession(TreeAdoption $adoption): array
    {
        return ['guardian'=>['id'=>$adoption->id,'version'=>$adoption->access_version,'expires_at'=>now()->addMinutes(10)->timestamp]];
    }

    public function test_payment_instructions_hidden_by_default_and_reactivated_without_removing_form(): void
    {
        $this->get('/sr')->assertOk()->assertDontSee('class="ngo-payment"',false)->assertSee('id="support-name"',false)->assertSee('class="col-12"',false);
        config(['ecosync.show_payment_instructions'=>true]);
        $this->get('/en')->assertOk()->assertSee('SAMPLE PAYMENT INSTRUCTIONS')->assertSee('class="ngo-payment"',false);
    }

    public function test_certificate_is_available_only_to_active_verified_approved_authorized_guardian_or_admin(): void
    {
        $a=$this->adoption();$url='/sr/staratelj/sertifikat/'.$a->public_id;
        $this->get($url)->assertNotFound();
        $this->withSession($this->guardianSession($a))->get($url)->assertOk()->assertHeader('Content-Type','application/pdf')->assertHeader('Cache-Control','no-store, private')->assertHeader('X-Robots-Tag','noindex, nofollow');
        foreach(['pending_verification','pending_approval','rejected','expired','cancelled','ended'] as $status){
            $a->update(['status'=>$status]);
            $this->withSession($this->guardianSession($a))->get($url)->assertNotFound();
        }
        $a->update(['status'=>'active','email_verified_at'=>null]);
        $this->withSession($this->guardianSession($a))->get($url)->assertNotFound();
        $a->update(['email_verified_at'=>now(),'approved_at'=>null]);
        $this->withSession($this->guardianSession($a))->get($url)->assertNotFound();
        $a->update(['approved_at'=>now()]);
        $this->actingAs($this->admin())->get('/en/staratelj/sertifikat/'.$a->public_id)->assertOk();
    }

    public function test_other_guardian_expired_session_and_revoked_version_cannot_download(): void
    {
        $a=$this->adoption();$b=$this->adoption();$url='/en/staratelj/sertifikat/'.$a->public_id;
        $this->withSession($this->guardianSession($b))->get($url)->assertNotFound();
        $session=$this->guardianSession($a);$session['guardian']['expires_at']=now()->subSecond()->timestamp;
        $this->withSession($session)->get($url)->assertNotFound();
        $session=$this->guardianSession($a);$a->increment('access_version');
        $this->withSession($session)->get($url)->assertNotFound();
        $user=User::create(['name'=>'Nonadmin','email'=>'user@example.test','password'=>'test-password-12345']);
        $this->flushSession();$this->actingAs($user)->get($url)->assertNotFound();
    }

    public function test_certificate_projection_respects_name_privacy_and_reference_stays_stable(): void
    {
        $a=$this->adoption();$service=app(AdoptionCertificates::class);$ref=$service->reference($a);
        $data=$service->data($a);$this->assertSame('Čedomir Đorđević',$data['guardian']);
        $this->assertSame('Lipa čćšžđ',$data['species']);
        $this->assertStringNotContainsString('private@example.test',json_encode($data));
        $this->assertStringNotContainsString('PRIVATE PHONE',json_encode($data));
        $this->assertStringNotContainsString('PRIVATE NOTES',json_encode($data));
        $this->assertArrayNotHasKey('id',$data);
        $a->update(['public_display_preference'=>'initials']);$this->assertSame('Č. Đ.',$service->data($a)['guardian']);
        $a->update(['public_display_preference'=>'anonymous']);$this->assertSame('Anonimni staratelj',$service->data($a)['guardian']);
        app()->setLocale('en');$data=$service->data($a);$this->assertSame('Anonymous guardian',$data['guardian']);$this->assertSame('Linden',$data['species']);
        $this->assertSame($ref,$service->reference($a->fresh()));
        $html=view('pdf.certificate',$data)->render();$this->assertStringContainsString('Tree Adoption Certificate',$html);$this->assertStringNotContainsString('Čedomir',$html);$this->assertStringNotContainsString('private@example.test',$html);
        $this->assertStringStartsWith('%PDF-',$service->certificate($a));
        $a->update(['status'=>'ended']);$this->assertFalse($service->eligible($a));$this->assertSame($ref,$service->reference($a));
    }

    public function test_certificate_remains_one_a4_page_with_long_names_and_uses_embedded_fonts(): void
    {
        $a = $this->adoption();
        $a->update(['guardian_name'=>str_repeat('Čedomir Đorđević ',9)]);
        $a->tree->species->update(['common_name'=>str_repeat('Lipa čćšžđ ',20)]);
        $pdf = app(AdoptionCertificates::class)->certificate($a);
        preg_match('~/Type /Pages.*?/Count\\s+(\\d+)~s', $pdf, $matches);
        $this->assertSame('1', $matches[1] ?? null);
        $this->assertStringContainsString('/FontFile2', $pdf);
        $this->assertStringContainsString('/MediaBox [0.000 0.000 595.280 841.890]', $pdf);
    }

    public function test_qr_is_scannable_and_uses_configured_canonical_origin_in_both_languages(): void
    {
        $a=$this->adoption();$tree=$a->tree;$qr=app(TreeQr::class);
        foreach(['sr','en'] as $locale){
            $expected='https://ecosync.example.test/'.$locale.'/stabla/'.$tree->public_identifier;
            $this->assertSame($expected,$qr->publicUrl($tree,$locale));
            $reader=new QrReader($qr->png($tree,$locale),QrReader::SOURCE_TYPE_BLOB);
            $this->assertSame($expected,$reader->text());
            $this->get('/'.$locale.'/stabla/'.$tree->public_identifier.'/qr.svg?download=1')->assertOk()->assertHeader('Content-Type','image/svg+xml; charset=UTF-8')->assertHeader('Cache-Control','no-store, private')->assertDontSee('private@example.test')->assertDontSee('token');
        }
        $this->assertStringStartsWith('<?xml',$qr->svg($tree));
        $this->get('/sr/stabla/'.$tree->public_identifier.'/oznaka.pdf')->assertForbidden();
        $this->actingAs($this->admin())->get('/en/stabla/'.$tree->public_identifier.'/oznaka.pdf')->assertOk()->assertHeader('Content-Type','application/pdf');
    }

    public function test_unpublished_trees_have_no_qr_label_or_new_certificate(): void
    {
        $a=$this->adoption();$tree=$a->tree;$tree->update(['public_visibility'=>false]);
        $this->get('/sr/stabla/'.$tree->public_identifier.'/qr.svg')->assertNotFound();
        $this->actingAs($this->admin())->get('/sr/stabla/'.$tree->public_identifier.'/oznaka.pdf')->assertNotFound();
        $this->get('/sr/staratelj/sertifikat/'.$a->public_id)->assertNotFound();
    }
}
