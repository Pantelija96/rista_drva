<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use App\Models\{TreeSpecies, Tree, GalleryAlbum, School, SchoolApplication, TreeAdoption, MailOutbox};
use App\Mail\WorkflowMail;
use App\Services\{Adoptions, Outbox};
use Illuminate\Support\Str;
class LocalizationTest extends TestCase {
 use RefreshDatabase;
 public function test_all_core_pages_in_both_languages_and_metadata(): void {
  $this->withoutVite();
  foreach(['sr','en'] as $locale) foreach(['','/nasa-misija','/pridruzi-se','/nas-zeleni-trag','/galerija','/kontakt'] as $page){
   $this->get('/'.$locale.$page)->assertOk()->assertSee('hreflang="en"',false)->assertSee('hreflang="sr"',false)->assertSee('rel="canonical"',false)->assertSee('lang="'.($locale==='sr'?'sr-Latn':'en').'"',false)->assertSee($locale==='en'?'Our Green Footprint':'Naš zeleni trag');
  }
 }
 public function test_legacy_redirects_preserve_query_and_reject_unknown_locale(): void {
  $this->get('/')->assertRedirect('/sr');
  $this->get('/nas-zeleni-trag?city=Beograd')->assertStatus(301)->assertRedirect('/sr/nas-zeleni-trag?city=Beograd');
  $this->get('/galerija.html?page=2')->assertRedirect('/sr/galerija?page=2');
  $this->get('/fr/kontakt')->assertNotFound();
  $this->get('/admin/login')->assertOk();
 }
 public function test_dynamic_translation_fallback_and_identity_preserving_switch(): void {
  $this->withoutVite();
  $species=TreeSpecies::create(['common_name'=>'Lipa','description'=>'Srpski opis','translations'=>['en'=>['common_name'=>'Linden','description'=>'English description']],'published'=>true]);
  $tree=Tree::create(['tree_species_id'=>$species->id,'public_visibility'=>true,'adoption_status'=>'available']);
  $this->get('/en/stabla/'.$tree->public_identifier.'?city=Beograd&token=secret')->assertOk()->assertSee('Linden')->assertSee('/sr/stabla/'.$tree->public_identifier.'?city=Beograd',false)->assertDontSee('token=secret');
  $this->get('/sr/vrste-drveca/'.$species->slug)->assertOk()->assertSee('Srpski opis')->assertDontSee('English description');
  $this->get('/en/vrste-drveca/'.$species->slug)->assertOk()->assertSee('English description');
  $species->update(['translations'=>null]);
  $this->get('/en/vrste-drveca/'.$species->slug)->assertOk()->assertSee('Srpski opis');
  $album=GalleryAlbum::create(['title'=>'Srpski album','published'=>true,'translations'=>['en'=>['title'=>'English album']]]);
  $this->get('/en/galerija/'.$album->slug)->assertOk()->assertSee('English album');
  $school=School::create(['name'=>'Naziv škole','city'=>'Beograd','status'=>'published','description'=>'Opis škole','translations'=>['en'=>['description'=>'School description']]]);
  $this->get('/en/skole/'.$school->slug)->assertOk()->assertSee('Naziv škole')->assertSee('School description');
 }
 public function test_english_validation_and_submission_locale_are_captured(): void {
  Mail::fake();$this->withoutVite();
  $this->post('/en/pridruzi-se',[])->assertSessionHasErrors(['school_name'=>'This field is required.']);
  $this->post('/en/pridruzi-se',['school_name'=>'Test school','city'=>'Test city','contact_person'=>'Test contact','email'=>'test@example.test','desired_planting_model'=>'symbolic_generation_tree','privacy_consent'=>1,'submission_token'=>(string)Str::uuid()])->assertSessionHasNoErrors();
  $this->assertSame('en',SchoolApplication::first()->preferred_locale);
  $this->assertSame('en',MailOutbox::where('event','school.ack')->first()->locale);
  app()->setLocale('sr');URL::defaults(['locale'=>'sr']);app(Outbox::class)->drain();
  Mail::assertSent(WorkflowMail::class,fn($mail)=>$mail->hasTo('test@example.test') && $mail->heading==='EcoSync — We have received your application' && $mail->locale==='en');
  $this->assertSame('sr',app()->getLocale());
 }
 public function test_queued_retry_preserves_language_and_secure_links_verify_across_locales(): void {
  Mail::fake();$this->withoutVite();
  $species=TreeSpecies::create(['common_name'=>'Test species','published'=>true]);
  $tree=Tree::create(['tree_species_id'=>$species->id,'public_visibility'=>true,'adoption_status'=>'available']);
  $this->post('/en/stabla/'.$tree->public_identifier.'/usvajanje',['guardian_name'=>'Test guardian','guardian_email'=>'guardian@example.test','adoption_type'=>'symbolic','public_display_preference'=>'anonymous','privacy_consent'=>1,'submission_token'=>(string)Str::uuid()])->assertSessionHasNoErrors();
  $adoption=TreeAdoption::first();$this->assertSame('en',$adoption->preferred_locale);
  $row=MailOutbox::where('event','adoption.verify')->first();$this->assertSame('en',$row->locale);
  $adoption->update(['preferred_locale'=>'sr']);$row->update(['attempts'=>1]);app()->setLocale('sr');app(Outbox::class)->drain();
  $mail=Mail::sent(WorkflowMail::class)->first();$this->assertNotNull($mail);$this->assertSame('en',$mail->locale);$this->assertStringContainsString('/en/usvajanje/potvrda/',$mail->url);
  parse_str(parse_url($mail->url,PHP_URL_FRAGMENT),$parts);
  $oldPath='/usvajanje/potvrda/'.$adoption->public_id;
  $this->get($oldPath)->assertRedirect('/sr'.$oldPath);
  $this->post('/sr'.$oldPath,['token'=>$parts['token']])->assertSessionHasNoErrors();
  $this->assertSame('pending_approval',$adoption->fresh()->status);
  $this->get('/en/staratelj/pristup')->assertHeader('X-Robots-Tag','noindex, nofollow');
 }
 public function test_icon_library_and_translated_progressions_are_present(): void {
  $this->withoutVite();$this->get('/en/nas-zeleni-trag')->assertOk()->assertSee('fontawesome-free/css/all.min.css')->assertSee('fas fa-school')->assertSee('Reset filters')->assertDontSee('🏫')->assertDontSee('🌳');
  $this->get('/en/pridruzi-se')->assertSee('Choose a planting model')->assertSee('Submit school information');
  $this->assertSame(array_keys(require lang_path('sr/public.php')),array_keys(require lang_path('en/public.php')));
 }
}
