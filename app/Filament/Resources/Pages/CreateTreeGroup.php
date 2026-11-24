<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HandlesDomainValidation;
use App\Filament\Resources\TreeGroupResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTreeGroup extends CreateRecord
{
    use HandlesDomainValidation;

    protected static string $resource = TreeGroupResource::class;
}
