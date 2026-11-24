<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\TreeAdoptionResource;
use Filament\Resources\Pages\EditRecord;

class EditTreeAdoption extends EditRecord
{
    protected static string $resource = TreeAdoptionResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->record->getFillable() as $field) {
            $data[$field] = $this->record->getAttribute($field);
        }

        return $data;
    }
}
