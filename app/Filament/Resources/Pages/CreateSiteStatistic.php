<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HandlesDomainValidation;
use App\Filament\Resources\SiteStatisticResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSiteStatistic extends CreateRecord
{
    use HandlesDomainValidation;

    protected static string $resource = SiteStatisticResource::class;
}
