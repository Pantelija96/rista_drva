<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HandlesDomainValidation;
use App\Filament\Resources\SchoolApplicationResource;
use Filament\Resources\Pages\EditRecord;

class EditSchoolApplication extends EditRecord
{
    use HandlesDomainValidation;

    protected static string $resource = SchoolApplicationResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->record->getFillable() as $field) {
            $data[$field] = $this->record->getAttribute($field);
        }

        return $data;
    }
}
