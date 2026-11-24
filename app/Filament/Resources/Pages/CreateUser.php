<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (isset($data['email'])) {
            $data['email'] = strtolower(trim($data['email']));
        }

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        $record = new User;
        $admin = (bool) ($data['is_admin'] ?? false);
        unset($data['is_admin']);
        $record->fill($data);
        $record->is_admin = $admin;
        $record->save();

        return $record;
    }
}
