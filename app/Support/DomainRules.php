<?php

namespace App\Support;

use App\Models\ContentModel;
use App\Models\GalleryAlbum;
use App\Models\School;
use App\Models\SchoolApplication;
use App\Models\SchoolGeneration;
use App\Models\SiteStatistic;
use App\Models\Tree;
use App\Models\TreeSpecies;
use Illuminate\Validation\Rule;

final class DomainRules
{
    public const PLANTING = ['symbolic_generation_tree' => 'Simbolično stablo generacije', 'one_tree_per_graduate' => 'Jedno stablo po maturantu'];

    public const STATUS = ['draft' => 'Nacrt', 'published' => 'Objavljeno', 'inactive' => 'Neaktivno'];

    public const ADOPTION = ['unavailable' => 'Nedostupno', 'available' => 'Dostupno', 'adopted' => 'Usvojeno'];

    public const CONDITION = ['planned' => 'Planirano', 'planted' => 'Posađeno', 'needs_attention' => 'Potrebna nega', 'damaged' => 'Oštećeno', 'replaced' => 'Zamenjeno', 'removed' => 'Uklonjeno'];

    public const INSTITUTIONS = ['primary' => 'Osnovna škola', 'secondary' => 'Srednja škola', 'faculty' => 'Fakultet', 'other' => 'Druga obrazovna ustanova'];

    public const APPLICATION = ['pending' => 'Na čekanju', 'reviewing' => 'U razmatranju', 'approved' => 'Odobreno', 'rejected' => 'Odbijeno'];

    public static function for(ContentModel $model): array
    {
        $rules = [];
        foreach ($model->getFillable() as $field) {
            $rules[$field] = ['nullable', 'string', 'max:255'];
        }
        if (array_key_exists('translations', $rules)) {
            $rules['translations'] = ['nullable', 'array:en'];
            $rules['translations.en'] = ['nullable', 'array'];
            $rules['translations.en.*'] = ['nullable', 'string', 'max:20000'];
        }
        foreach (['description', 'short_description', 'message', 'notes', 'internal_notes', 'caption', 'preferred_habitat', 'sunlight_requirements', 'water_requirements', 'planting_recommendations', 'maintenance_guidelines', 'ecological_benefits', 'interesting_facts'] as $field) {
            if (array_key_exists($field, $rules)) {
                $rules[$field] = ['nullable', 'string', 'max:20000'];
            }
        }
        foreach (['photo_paths', 'gallery_paths'] as $field) {
            if (array_key_exists($field, $rules)) {
                $rules[$field] = ['nullable', 'array', 'max:30'];
                $rules[$field.'.*'] = ['string', 'max:255', 'regex:/^ecosync\\/[a-zA-Z0-9_\\/.-]+$/'];
            }
        }
        foreach (['latitude' => [-90, 90], 'longitude' => [-180, 180]] as $field => [$min,$max]) {
            if (array_key_exists($field, $rules)) {
                $rules[$field] = ['nullable', 'numeric', "between:$min,$max", 'required_with:'.($field === 'latitude' ? 'longitude' : 'latitude')];
            }
        }
        foreach (['school_id' => 'schools', 'school_generation_id' => 'school_generations', 'tree_group_id' => 'tree_groups', 'tree_species_id' => 'tree_species', 'gallery_album_id' => 'gallery_albums'] as $field => $table) {
            if (array_key_exists($field, $rules)) {
                $rules[$field] = [in_array($field, ['tree_species_id', 'gallery_album_id']) ? 'required' : 'nullable', 'integer', Rule::exists($table, 'id')];
            }
        }
        foreach (['published', 'public_visibility', 'visible', 'privacy_consent'] as $field) {
            if (array_key_exists($field, $rules)) {
                $rules[$field] = ['boolean'];
            }
        }
        if (isset($rules['native_to_serbia'])) {
            $rules['native_to_serbia'] = ['nullable', 'boolean'];
        }
        foreach (['declared_tree_count', 'expected_graduates', 'estimated_graduate_count', 'value', 'sort_order', 'expected_lifespan_years'] as $field) {
            if (array_key_exists($field, $rules)) {
                $rules[$field] = ['nullable', 'integer', 'min:0', 'max:100000000'];
            }
        }
        if (isset($rules['typical_height_m'])) {
            $rules['typical_height_m'] = ['nullable', 'numeric', 'between:0,99999'];
        }
        foreach (['planted_at', 'event_date', 'consent_timestamp', 'notification_sent_at'] as $field) {
            if (array_key_exists($field, $rules)) {
                $rules[$field] = ['nullable', 'date'];
            }
        }
        foreach (['email', 'contact_email'] as $field) {
            if (array_key_exists($field, $rules)) {
                $rules[$field] = ['nullable', 'email', 'max:255'];
            }
        }
        foreach (['name', 'title', 'common_name', 'alt_text'] as $field) {
            if (array_key_exists($field, $rules) && ! ($model instanceof Tree) && ! ($model instanceof SchoolGeneration && $field === 'name')) {
                $rules[$field] = ['required', 'string', 'max:255'];
            }
        }
        if (isset($rules['slug'])) {
            $rules['slug'] = ['required', 'alpha_dash', 'max:200', Rule::unique($model->getTable(), 'slug')->ignore($model->getKey())];
        }
        if ($model instanceof School) {
            $rules['city'] = ['required', 'string', 'max:255'];
            $rules['institution_type'] = ['required', Rule::in(array_keys(self::INSTITUTIONS))];
        }
        if (isset($rules['status'])) {
            $rules['status'] = ['required', Rule::in(array_keys($model instanceof SchoolApplication ? self::APPLICATION : self::STATUS))];
        }
        if (isset($rules['planting_model'])) {
            $rules['planting_model'] = ['required', Rule::in(array_keys(self::PLANTING))];
        }
        if ($model instanceof SchoolGeneration) {
            $rules['school_id'] = ['required', 'exists:schools,id'];
            $rules['program'] = ['required', 'string', 'max:100'];
            $rules['graduation_year'] = ['required', 'integer', 'between:1900,2200', Rule::unique('school_generations')->where('school_id', $model->school_id)->where('program', $model->program)->ignore($model->getKey())];
        }
        if ($model instanceof Tree) {
            $rules['physical_condition'] = ['required', Rule::in(array_keys(self::CONDITION))];
            $rules['adoption_status'] = ['required', Rule::in(array_keys(self::ADOPTION))];
        }
        if ($model instanceof TreeSpecies) {
            $rules['deciduous_or_evergreen'] = ['nullable', Rule::in(['deciduous', 'evergreen'])];
        }
        if ($model instanceof GalleryAlbum) {
            $rules['category'] = ['required', Rule::in(['škole', 'sadnje', 'volonteri', 'radionice'])];
        }
        if ($model instanceof SiteStatistic) {
            $rules['key'] = ['required', 'alpha_dash', 'max:100', Rule::unique('site_statistics', 'key')->ignore($model->getKey())];
            $rules['value'] = ['required', 'integer', 'min:0', 'max:100000000'];
            $rules['label'] = ['required', 'string', 'max:255'];
            $rules['suffix'] = ['nullable', 'string', 'max:16'];
        }

        return $rules;
    }
}
