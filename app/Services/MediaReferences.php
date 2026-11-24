<?php

namespace App\Services;

use App\Models\GalleryAlbum;
use App\Models\GalleryImage;
use App\Models\School;
use App\Models\Tree;
use App\Models\TreeGroup;
use App\Models\TreeSpecies;

final class MediaReferences
{
    public function exists(string $path): bool
    {
        foreach ([School::class => ['logo_path', 'featured_image_path'], Tree::class => ['featured_image_path', 'photo_paths'], TreeGroup::class => ['featured_image_path'], TreeSpecies::class => ['main_image_path', 'gallery_paths'], GalleryAlbum::class => ['cover_image_path'], GalleryImage::class => ['image_path']] as $model => $fields) {
            foreach ($fields as $field) {
                $query = $model::query();
                $exists = in_array($field, ['photo_paths', 'gallery_paths'], true) ? $query->whereJsonContains($field, $path)->exists() : $query->where($field, $path)->exists();
                if ($exists) {
                    return true;
                }
            }
        }

        return false;
    }
}
