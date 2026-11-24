<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Map extends Page
{
    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Mapa';

    protected static string|\UnitEnum|null $navigationGroup = 'STABLA I MAPA';

    protected string $view = 'filament.map';

    public static function canAccess(): bool
    {
        return auth()->user()?->is_admin === true;
    }
}
