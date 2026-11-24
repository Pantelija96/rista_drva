<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryAlbum extends ContentModel
{
    use HasPublicTranslations;

    protected $fillable = ['translations', 'title', 'slug', 'description', 'cover_image_path', 'school_id', 'school_generation_id', 'event_date', 'location_label', 'category', 'published', 'sort_order'];

    protected $hidden = [];

    protected function casts(): array
    {
        return ['translations' => 'array', 'published' => 'boolean', 'event_date' => 'date'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(SchoolGeneration::class, 'school_generation_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class);
    }

    public function scopePublished($query): void
    {
        $query->where('published', true);
        $query->where(fn ($q) => $q->whereNull('school_id')->orWhereHas('school', fn ($s) => $s->where('status', 'published')))
            ->where(fn ($q) => $q->whereNull('school_generation_id')->orWhereHas('generation', fn ($g) => $g->where('status', 'published')));
    }
}
