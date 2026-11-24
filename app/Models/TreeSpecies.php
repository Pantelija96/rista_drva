<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class TreeSpecies extends ContentModel
{
    use HasPublicTranslations;

    protected $fillable = ['translations', 'common_name', 'scientific_name', 'slug', 'short_description', 'description', 'main_image_path', 'gallery_paths', 'native_to_serbia', 'deciduous_or_evergreen', 'typical_height_m', 'expected_lifespan_years', 'preferred_habitat', 'sunlight_requirements', 'water_requirements', 'planting_recommendations', 'maintenance_guidelines', 'ecological_benefits', 'interesting_facts', 'published'];

    protected $hidden = [];

    protected function casts(): array
    {
        return ['translations' => 'array', 'published' => 'boolean', 'native_to_serbia' => 'boolean', 'gallery_paths' => 'array'];
    }

    protected $table = 'tree_species';

    public function trees(): HasMany
    {
        return $this->hasMany(Tree::class);
    }

    public function scopePublished($query): void
    {
        $query->where('published', true);
    }
}
