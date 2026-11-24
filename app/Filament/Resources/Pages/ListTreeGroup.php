<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\TreeGroupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTreeGroup extends ListRecords
{
    protected static string $resource = TreeGroupResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
