<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HandlesDomainValidation;
use App\Filament\Resources\SchoolApplicationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSchoolApplication extends CreateRecord
{
    use HandlesDomainValidation;

    protected static string $resource = SchoolApplicationResource::class;
}
