<?php

namespace App\Models;

use App\Support\DomainRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

abstract class ContentModel extends Model
{
    // Lock school, generation and group rows in a consistent order for concurrent admin writes.
    public function save(array $options = [])
    {
        return DB::transaction(function () use ($options) {
            foreach (['school_id' => School::class, 'school_generation_id' => SchoolGeneration::class] as $field => $type) {
                if ($this->$field) {
                    $type::whereKey($this->$field)->lockForUpdate()->firstOrFail();
                }
            }
            if ($this instanceof SchoolGeneration && $this->exists) {
                static::whereKey($this->id)->lockForUpdate()->firstOrFail();
            }
            if ($this instanceof TreeGroup && $this->exists) {
                static::whereKey($this->id)->lockForUpdate()->firstOrFail();
            }
            if ($this->tree_group_id) {
                TreeGroup::whereKey($this->tree_group_id)->lockForUpdate()->firstOrFail();
            }
            if ($this instanceof SchoolApplication && in_array($this->status, ['approved', 'rejected'], true)) {
                $this->active_fingerprint = null;
            }
            $this->validateContent();
            $saved = parent::save($options);
            if ($this instanceof Tree && ! $this->public_identifier) {
                $this->public_identifier = 'ECO-'.str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
                parent::save($options);
            }

            return $saved;
        });
    }

    protected function validateContent(): void
    {
        if (! $this->exists && in_array('slug', $this->getFillable(), true) && blank($this->slug)) {
            $label = (string) ($this->name ?? $this->title ?? $this->common_name ?? '');
            $this->slug = Str::limit(Str::slug($label), 180, '').'-'.Str::lower(Str::random(8));
        }
        // Load database defaults into new models so validation and UI use the same state.
        $defaults = match (true) {
            $this instanceof School => ['status' => 'draft', 'institution_type' => 'secondary'],
            $this instanceof SchoolGeneration => ['program' => 'general', 'status' => 'draft'],
            $this instanceof Tree => ['physical_condition' => 'planned', 'adoption_status' => 'unavailable', 'public_visibility' => false],
            $this instanceof SchoolApplication => ['status' => 'pending'],
            $this instanceof GalleryAlbum => ['category' => 'sadnje', 'sort_order' => 0, 'published' => false],
            $this instanceof SiteStatistic => ['visible' => true, 'sort_order' => 0],
            default => [],
        };
        foreach ($defaults as $key => $value) {
            if ($this->$key === null) {
                $this->$key = $value;
            }
        }
        $data = [];
        foreach (array_keys($this->getAttributes()) as $key) {
            $data[$key] = $this->getAttribute($key);
        }
        Validator::make($data, DomainRules::for($this))->validate();
        if ($this->school_generation_id) {
            $generation = SchoolGeneration::findOrFail($this->school_generation_id);
            if ((int) $this->school_id !== (int) $generation->school_id) {
                throw ValidationException::withMessages(['school_generation_id' => 'Generacija mora pripadati izabranoj školi.']);
            }
        }
        if ($this instanceof Tree && $this->exists && $this->isDirty('public_identifier')) {
            throw ValidationException::withMessages(['public_identifier' => 'Javni identifikator je trajan.']);
        }
        if ($this instanceof Tree && $this->tree_group_id) {
            $group = TreeGroup::findOrFail($this->tree_group_id);
            if ($group->school_id != $this->school_id || $group->school_generation_id != $this->school_generation_id) {
                throw ValidationException::withMessages(['tree_group_id' => 'Škola i generacija stabla moraju odgovarati grupi.']);
            }
            if ($group->declared_tree_count !== null && $group->trees()->where('id', '!=', $this->id ?? 0)->count() >= $group->declared_tree_count) {
                throw ValidationException::withMessages(['tree_group_id' => 'Broj pojedinačnih stabala prelazi prijavljeni broj grupe.']);
            }
        }
        if ($this instanceof TreeGroup && $this->exists) {
            if ($this->declared_tree_count !== null && $this->trees()->count() > $this->declared_tree_count) {
                throw ValidationException::withMessages(['declared_tree_count' => 'Grupa već ima više registrovanih stabala.']);
            }
            if ($this->isDirty(['school_id', 'school_generation_id']) && $this->trees()->exists()) {
                throw ValidationException::withMessages(['school_id' => 'Najpre premestite stabla iz grupe pre promene škole ili generacije.']);
            }
        }
        if ($this instanceof SchoolGeneration && $this->exists && $this->isDirty('school_id') && ($this->trees()->exists() || $this->groups()->exists() || $this->albums()->exists())) {
            throw ValidationException::withMessages(['school_id' => 'Generacija sa povezanim zapisima ne može se premestiti u drugu školu.']);
        }
    }
}
