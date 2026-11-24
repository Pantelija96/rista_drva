<?php

namespace App\Models;

class SiteStatistic extends ContentModel
{
    use HasPublicTranslations;

    protected $fillable = ['translations', 'key', 'label', 'value', 'suffix', 'sort_order', 'visible'];

    protected $hidden = [];

    protected function casts(): array
    {
        return ['translations' => 'array', 'visible' => 'boolean', 'value' => 'integer'];
    }
}
