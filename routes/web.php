<?php
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\PublicLocale;

Route::prefix('{locale}')->where(['locale' => 'sr|en'])->middleware(PublicLocale::class)->group(base_path('routes/public.php'));
// Old GET links redirect; old POST endpoints remain usable for already open forms.
Route::middleware(PublicLocale::class)->name('legacy.')->group(base_path('routes/public.php'));

Route::get('/{legacy}.html', \App\Http\Controllers\LegacyPageController::class)->where('legacy', 'index|nasa-misija|pridruzi-se|nas-zeleni-trag|galerija|kontakt|mapa|drvo-za-maturanta');
