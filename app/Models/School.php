<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends ContentModel
{
    use HasPublicTranslations;

    protected $fillable = ['translations', 'name', 'slug', 'institution_type', 'city', 'municipality', 'address', 'latitude', 'longitude', 'contact_name', 'contact_email', 'contact_phone', 'description', 'logo_path', 'featured_image_path', 'status'];

    protected $hidden = ['contact_name', 'contact_email', 'contact_phone'];

    protected function casts(): array
    {
        return ['translations' => 'array', 'latitude' => 'float', 'longitude' => 'float'];
    }

    public function generations(): HasMany
    {
        return $this->hasMany(SchoolGeneration::class);
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

    public function applications(): HasMany
    {
        return $this->hasMany(SchoolApplication::class);
    }

    public function scopePublished($query): void
    {
        $query->where('status', 'published');
    }
}
