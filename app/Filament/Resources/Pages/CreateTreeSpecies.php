<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HandlesDomainValidation;
use App\Filament\Resources\TreeSpeciesResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTreeSpecies extends CreateRecord
{
    use HandlesDomainValidation;

    protected static string $resource = TreeSpeciesResource::class;
}
