<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HandlesDomainValidation;
use App\Filament\Resources\TreeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTree extends CreateRecord
{
    use HandlesDomainValidation;

    protected static string $resource = TreeResource::class;
}
