<?php
namespace App\Models;
trait HasPublicTranslations {
 public function localized(string $field): ?string {
  $english = data_get($this->translations, 'en.'.$field);
  return app()->getLocale() === 'en' && filled($english) ? $english : $this->getAttribute($field);
 }
}
