<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\TreeMaintenanceRecordResource;
use App\Models\Tree;
use App\Services\Maintenance;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateTreeMaintenanceRecord extends CreateRecord
{
    protected static string $resource = TreeMaintenanceRecordResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $record = app(Maintenance::class)->create(Tree::findOrFail($data['tree_id']), $data, $data['photos'] ?? [], admin: auth()->user());
        $record->update(['translations'=>$data['translations'] ?? null]);
        return $record;
    }
}
