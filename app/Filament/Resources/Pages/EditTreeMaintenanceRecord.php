<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\TreeMaintenanceRecordResource;
use Filament\Resources\Pages\EditRecord;

class EditTreeMaintenanceRecord extends EditRecord
{
    protected static string $resource = TreeMaintenanceRecordResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->record->getFillable() as $field) {
            $data[$field] = $this->record->getAttribute($field);
        }

        return $data;
    }
}
