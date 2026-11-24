<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HandlesDomainValidation;
use App\Filament\Resources\TreeGroupResource;
use Filament\Resources\Pages\EditRecord;

class EditTreeGroup extends EditRecord
{
    use HandlesDomainValidation;

    protected static string $resource = TreeGroupResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->record->getFillable() as $field) {
            $data[$field] = $this->record->getAttribute($field);
        }

        return $data;
    }
}
