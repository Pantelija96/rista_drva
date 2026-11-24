<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\TreeSpeciesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTreeSpecies extends ListRecords
{
    protected static string $resource = TreeSpeciesResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
