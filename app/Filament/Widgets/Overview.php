<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SchoolApplicationResource;
use App\Filament\Resources\SchoolResource;
use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\School;
use App\Models\SchoolApplication;
use App\Models\SchoolGeneration;
use App\Models\Tree;
use App\Models\TreeGroup;
use App\Models\TreeSpecies;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class Overview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Škole', School::count())->url(SchoolResource::getUrl()),
            Stat::make('Zahtevi na čekanju', SchoolApplication::where('status', 'pending')->count())->url(SchoolApplicationResource::getUrl()),
            Stat::make('Generacije', SchoolGeneration::count()), Stat::make('Registrovana stabla', Tree::count()),
            Stat::make('Grupe stabala', TreeGroup::count()), Stat::make('Vrste drveća', TreeSpecies::count()),
            Stat::make('Galerije', GalleryAlbum::count()), Stat::make('Fotografije', GalleryImage::count()),
            Stat::make('Dostupna stabla', Tree::where('adoption_status', 'available')->count()),
            Stat::make('Usvojena stabla', Tree::where('adoption_status', 'adopted')->count()),
        ];
    }
}
