<?php
namespace App\Filament;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Textarea;
final class TranslationFields {
 public static function english(array $fields): Section {
  return Section::make('English — javni sadržaj')->description('Srpski je primarni jezik u osnovnim poljima. Prazan engleski prevod koristi srpski tekst; nedostajući prevodi prikazani su ispod polja. Imena organizacija i osoba ne prevodite.')->schema(array_map(fn($field) => Textarea::make('translations.en.'.$field)->label(ucfirst(str_replace('_',' ',$field)).' (English)')->maxLength(20000)->helperText(fn($state) => blank($state) ? 'Nedostaje engleski prevod — koristi se srpski.' : 'English translation supplied.'), $fields))->columns(2)->columnSpanFull()->collapsible();
 }
}
