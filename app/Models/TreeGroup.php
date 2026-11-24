<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TreeGroup extends ContentModel
{
    use HasPublicTranslations;

    protected $fillable = ['translations', 'name', 'slug', 'school_id', 'school_generation_id', 'description', 'latitude', 'longitude', 'planted_at', 'declared_tree_count', 'featured_image_path', 'published'];

    protected $hidden = [];

    protected function casts(): array
    {
        return ['translations' => 'array', 'published' => 'boolean', 'latitude' => 'float', 'longitude' => 'float', 'planted_at' => 'date', 'declared_tree_count' => 'integer'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(SchoolGeneration::class, 'school_generation_id');
    }

    public function trees(): HasMany
    {
        return $this->hasMany(Tree::class);
    }

    public function scopePublished($query): void
    {
        $query->where('published', true);
        $query->where(fn ($q) => $q->whereNull('school_id')->orWhereHas('school', fn ($s) => $s->where('status', 'published')))
            ->where(fn ($q) => $q->whereNull('school_generation_id')->orWhereHas('generation', fn ($g) => $g->where('status', 'published')));
    }
}
