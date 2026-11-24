<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Resources\GalleryAlbumResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGalleryAlbum extends ListRecords
{
    protected static string $resource = GalleryAlbumResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
