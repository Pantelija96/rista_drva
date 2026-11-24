<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
class LegacyPageController {
 public function __invoke(Request $request, string $legacy) {
 $path = ['index'=>'', 'mapa'=>'nas-zeleni-trag','drvo-za-maturanta'=>'pridruzi-se'][$legacy] ?? $legacy;
 return redirect('/sr'.($path ? '/'.$path : '').($request->getQueryString() ? '?'.$request->getQueryString() : '').($legacy==='mapa' ? '#mapa' : ''),301);
 }
}
