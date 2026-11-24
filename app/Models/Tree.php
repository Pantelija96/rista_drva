<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Tree extends ContentModel
{
    use HasPublicTranslations;

    protected $fillable = ['translations', 'tree_species_id', 'school_id', 'school_generation_id', 'tree_group_id', 'title', 'description', 'latitude', 'longitude', 'planted_at', 'physical_condition', 'adoption_status', 'public_visibility', 'featured_image_path', 'photo_paths', 'notes'];

    protected $hidden = ['notes'];

    protected function casts(): array
    {
        return ['translations' => 'array', 'public_visibility' => 'boolean', 'latitude' => 'float', 'longitude' => 'float', 'planted_at' => 'date', 'photo_paths' => 'array'];
    }

    public function species(): BelongsTo
    {
        return $this->belongsTo(TreeSpecies::class, 'tree_species_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function generation(): BelongsTo
    {
        return $this->belongsTo(SchoolGeneration::class, 'school_generation_id');
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(TreeGroup::class, 'tree_group_id');
    }

    public function adoptions()
    {
        return $this->hasMany(TreeAdoption::class);
    }

    public function reservedAdoption()
    {
        return $this->hasOne(TreeAdoption::class, 'reserved_tree_id')->where(fn ($q) => $q->where('status', 'active')->orWhere(fn ($s) => $s->whereIn('status', ['pending_verification', 'pending_approval'])->where('reservation_expires_at', '>', now())));
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(TreeMaintenanceRecord::class);
    }

    public function effectiveAdoptionStatus(): string
    {
        $reservation = $this->reservedAdoption;
        if ($reservation?->status === 'active') {
            return 'adopted';
        }
        if ($reservation) {
            return 'unavailable';
        }

        return $this->adoption_status;
    }

    public function scopeAdoptionStatus($query, string $status): void
    {
        if ($status === 'available') {
            $query->where('adoption_status', 'available')->whereDoesntHave('reservedAdoption');
        } elseif ($status === 'adopted') {
            $query->where(fn ($q) => $q->whereHas('reservedAdoption', fn ($a) => $a->where('status', 'active'))->orWhere(fn ($s) => $s->where('adoption_status', 'adopted')->whereDoesntHave('reservedAdoption', fn ($a) => $a->where('status', '!=', 'active'))));
        } else {
            $query->where(fn ($q) => $q->where('adoption_status', 'unavailable')->whereDoesntHave('reservedAdoption', fn ($a) => $a->where('status', 'active'))->orWhereHas('reservedAdoption', fn ($a) => $a->where('status', '!=', 'active')));
        }
    }

    public function scopePublished($query): void
    {
        $query->where('public_visibility', true)->whereHas('species', fn ($q) => $q->where('published', true))
            ->where(fn ($q) => $q->whereNull('school_id')->orWhereHas('school', fn ($s) => $s->where('status', 'published')))
            ->where(fn ($q) => $q->whereNull('school_generation_id')->orWhereHas('generation', fn ($g) => $g->where('status', 'published')))
            ->where(fn ($q) => $q->whereNull('tree_group_id')->orWhereHas('group', fn ($g) => $g->where('published', true)));
    }
}
