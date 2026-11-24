<?php

use App\Http\Controllers\AdoptionController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\InquiryController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PublicMapController;
use App\Http\Controllers\PublicProfileController;
use App\Http\Controllers\SchoolApplicationController;
use App\Http\Middleware\GuardianSession;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/nasa-misija', [PageController::class, 'mission'])->name('mission');
Route::get('/pridruzi-se', [PageController::class, 'join'])->name('join');
Route::get('/nas-zeleni-trag', [PageController::class, 'greenTrace'])->name('green-trace');
Route::get('/galerija', [PageController::class, 'gallery'])->name('gallery');
Route::get('/kontakt', [PageController::class, 'contact'])->name('contact');


Route::post('/pridruzi-se', [SchoolApplicationController::class, 'store'])->middleware('throttle:school-applications')->name('school-applications.store');
Route::get('/mapa/podaci', PublicMapController::class)->middleware('throttle:60,1')->name('map.markers');
Route::get('/skole/{school:slug}', [PublicProfileController::class, 'school'])->name('schools.show');
Route::get('/skole/{school:slug}/generacije/{generation}', [PublicProfileController::class, 'generation'])->name('generations.show');
Route::get('/stabla/{identifier}', [PublicProfileController::class, 'tree'])->name('trees.show');
Route::get('/grupe-stabala/{slug}', [PublicProfileController::class, 'group'])->name('groups.show');
Route::get('/vrste-drveca/{slug}', [PublicProfileController::class, 'species'])->name('species.show');
Route::get('/galerija/{slug}', [PublicProfileController::class, 'album'])->name('albums.show');

Route::post('/upiti/{kind}', [InquiryController::class, 'store'])->whereIn('kind', ['contact', 'support'])->middleware('throttle:public-workflows')->name('inquiries.store');
Route::view('/usvajanje/primljeno', 'workflows.received')->name('adoptions.received');
Route::post('/stabla/{identifier}/usvajanje', [AdoptionController::class, 'store'])->middleware('throttle:public-workflows')->name('adoptions.store');
Route::post('/usvajanje/ponovi-potvrdu', [AdoptionController::class, 'resend'])->middleware('throttle:secure-links')->name('adoptions.resend');
Route::get('/usvajanje/potvrda/{adoption:public_id}', [AdoptionController::class, 'verifyForm'])->middleware('throttle:secure-links')->name('adoptions.verify.form');
Route::post('/usvajanje/potvrda/{adoption:public_id}', [AdoptionController::class, 'verify'])->middleware('throttle:secure-links')->name('adoptions.verify');
Route::view('/staratelj/pristup', 'workflows.access-request')->name('guardian.request');
Route::post('/staratelj/pristup', [GuardianController::class, 'request'])->middleware('throttle:secure-links')->name('guardian.request.store');
Route::get('/staratelj/potvrda/{grant:public_id}', [GuardianController::class, 'accessForm'])->middleware('throttle:secure-links')->name('guardian.access.form');
Route::post('/staratelj/potvrda/{grant:public_id}', [GuardianController::class, 'access'])->middleware('throttle:secure-links')->name('guardian.access');
Route::middleware(GuardianSession::class)->group(function () {
    Route::get('/staratelj', [GuardianController::class, 'dashboard'])->name('guardian.dashboard');
    Route::post('/staratelj/nega', [MaintenanceController::class, 'store'])->middleware('throttle:public-workflows')->name('guardian.maintenance');
    Route::post('/staratelj/podesavanja', [GuardianController::class, 'preferences'])->middleware('throttle:public-workflows')->name('guardian.preferences');
    Route::post('/staratelj/email',[\App\Http\Controllers\GuardianController::class,'emailRequest'])->middleware('throttle:secure-links')->name('guardian.email.request');
    Route::post('/staratelj/otkazivanje',[\App\Http\Controllers\GuardianController::class,'cancel'])->name('guardian.cancel');
    Route::post('/staratelj/odjava', [GuardianController::class, 'logout'])->name('guardian.logout');
});
Route::get('/nega/fotografije/{photo:public_id}', [MaintenanceController::class, 'photo'])->name('maintenance.photo');

Route::get('/staratelj/email/potvrda/{grant:public_id}',[\App\Http\Controllers\GuardianController::class,'emailForm'])->middleware('throttle:secure-links')->name('guardian.email.form');
Route::post('/staratelj/email/potvrda/{grant:public_id}',[\App\Http\Controllers\GuardianController::class,'emailConfirm'])->middleware('throttle:secure-links')->name('guardian.email.confirm');

Route::get('/staratelj/sertifikat/{adoption:public_id}', [\App\Http\Controllers\TreeDocumentsController::class, 'certificate'])->middleware('throttle:public-workflows')->name('certificates.download');
Route::get('/stabla/{identifier}/qr.svg', [\App\Http\Controllers\TreeDocumentsController::class, 'qr'])->middleware('throttle:60,1')->name('trees.qr');
Route::get('/stabla/{identifier}/oznaka.pdf', [\App\Http\Controllers\TreeDocumentsController::class, 'label'])->middleware('throttle:public-workflows')->name('trees.label');
