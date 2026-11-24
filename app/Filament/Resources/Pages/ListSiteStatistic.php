<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\SiteStatisticResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSiteStatistic extends ListRecords
{
    protected static string $resource = SiteStatisticResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
