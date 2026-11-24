<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record = $record;
        $admin = (bool) ($data['is_admin'] ?? false);
        unset($data['is_admin']);
        $record->fill($data);
        $record->is_admin = $admin;
        $record->save();

        return $record;
    }
}
