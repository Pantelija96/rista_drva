<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\SchoolGenerationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSchoolGeneration extends ListRecords
{
    protected static string $resource = SchoolGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
