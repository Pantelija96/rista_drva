<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PublicLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = $request->route('locale');
        if ($locale === null && $request->isMethod('GET')) {
            $path = '/'.ltrim($request->path(), '/');
            $aliases = ['/index.html'=>'/', '/mapa.html'=>'/nas-zeleni-trag', '/drvo-za-maturanta.html'=>'/pridruzi-se'];
            $path = $aliases[$path] ?? preg_replace('/\.html$/', '', $path);
            $query = $request->getQueryString();
            return redirect('/sr'.($path === '/' ? '' : $path).($query ? '?'.$query : '').($request->path()==='mapa.html' ? '#mapa' : ''), 301);
        }
        $locale ??= 'sr';
        abort_unless(in_array($locale, ['sr', 'en'], true), 404);
        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);
        $request->session()->put('public_locale', $locale);
        // Remove the infrastructural parameter before controller argument binding.
        $request->route()->forgetParameter('locale');
        $response = $next($request);
        if ($request->is('*staratelj*', '*potvrda*', '*usvajanje/primljeno*')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
            $response->headers->set('Cache-Control', 'no-store, private');
            $response->headers->set('Referrer-Policy', 'no-referrer');
        }
        return $response;
    }
}
