<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolGeneration extends ContentModel
{
    use HasPublicTranslations;

    protected $fillable = ['translations', 'school_id', 'graduation_year', 'program', 'name', 'planting_model', 'expected_graduates', 'notes', 'status'];

    protected $hidden = ['notes'];

    protected function casts(): array
    {
        return ['translations' => 'array', 'graduation_year' => 'integer', 'expected_graduates' => 'integer'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function trees(): HasMany
    {
        return $this->hasMany(Tree::class);
    }

    public function groups(): HasMany
    {
        return $this->hasMany(TreeGroup::class);
    }

    public function albums(): HasMany
    {
        return $this->hasMany(GalleryAlbum::class);
    }

    public function scopePublished($query): void
    {
        $query->where('status', 'published');
        $query->whereHas('school', fn ($q) => $q->where('status', 'published'));
    }
}
