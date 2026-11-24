<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryImage extends ContentModel
{
    use HasPublicTranslations;

    protected $fillable = ['translations', 'gallery_album_id', 'image_path', 'alt_text', 'caption', 'sort_order', 'published'];

    protected $hidden = [];

    protected function casts(): array
    {
        return ['translations' => 'array', 'published' => 'boolean'];
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }
}
