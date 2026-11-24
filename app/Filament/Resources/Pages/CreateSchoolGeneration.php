<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HandlesDomainValidation;
use App\Filament\Resources\SchoolGenerationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSchoolGeneration extends CreateRecord
{
    use HandlesDomainValidation;

    protected static string $resource = SchoolGenerationResource::class;
}
