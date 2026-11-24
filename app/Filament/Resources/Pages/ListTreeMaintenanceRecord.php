<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\TreeMaintenanceRecordResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTreeMaintenanceRecord extends ListRecords
{
    protected static string $resource = TreeMaintenanceRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
