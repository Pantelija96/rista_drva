<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\MailOutboxResource;
use Filament\Resources\Pages\EditRecord;

class EditMailOutbox extends EditRecord
{
    protected static string $resource = MailOutboxResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->record->getFillable() as $field) {
            $data[$field] = $this->record->getAttribute($field);
        }

        return $data;
    }
}
