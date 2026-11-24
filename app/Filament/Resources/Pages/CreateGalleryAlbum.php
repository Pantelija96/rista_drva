<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\HandlesDomainValidation;
use App\Filament\Resources\GalleryAlbumResource;
use Filament\Resources\Pages\CreateRecord;

class CreateGalleryAlbum extends CreateRecord
{
    use HandlesDomainValidation;

    protected static string $resource = GalleryAlbumResource::class;
}
