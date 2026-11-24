<?php
namespace App\Support;
use Illuminate\Http\Request;
final class LocaleUrls {
 public static function for(string $locale, bool $filters = true): string {
  $locale = in_array($locale,['sr','en'],true) ? $locale : 'sr';
  $path=preg_replace('~^/(sr|en)(?=/|$)~','',request()->getPathInfo());
  $query=$filters ? request()->only(['category','city','school_id','graduation_year','species_id','adoption_status','search','page']) : [];
  return url('/'.$locale.($path==='/'?'':$path)).($query?'?'.http_build_query($query):'');
 }
}
