<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Settings extends Page
{
    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Podešavanja';

    protected static string|\UnitEnum|null $navigationGroup = 'ADMINISTRACIJA';

    protected string $view = 'filament.settings';

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin === true;
    }
}
