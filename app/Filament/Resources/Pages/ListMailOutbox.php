<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\MailOutboxResource;
use Filament\Resources\Pages\ListRecords;

class ListMailOutbox extends ListRecords
{
    protected static string $resource = MailOutboxResource::class;
}
