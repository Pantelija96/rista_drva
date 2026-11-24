<?php

namespace App\Services;

use App\Models\School;
use App\Models\Tree;
use App\Models\TreeGroup;
use Illuminate\Database\Eloquent\Builder;

class PublicMapData
{
    public function markers(?School $school = null, ?int $generationId = null, ?Tree $tree = null, ?TreeGroup $group = null, array $filters = []): array
    {
        $schools = School::published()->with(['generations' => fn ($q) => $q->published()->select(['id', 'school_id', 'graduation_year'])])->whereNotNull('latitude')->whereNotNull('longitude')
            ->when($school, fn ($q) => $q->whereKey($school->id))
            ->when($generationId || $tree || $group, fn ($q) => $q->whereRaw('1=0'))
            ->when(($filters['school_id'] ?? null), fn ($q, $id) => $q->whereKey($id))
            ->when(($filters['city'] ?? null), fn ($q, $city) => $this->city($q, $city))
            ->when(($filters['graduation_year'] ?? null), fn ($q, $year) => $q->whereHas('generations', fn ($g) => $g->published()->where('graduation_year', $year)))
            ->when(($filters['search'] ?? null), fn ($q, $search) => $q->where(fn ($s) => $s->where('name', 'like', '%'.$search.'%')->orWhere('city', 'like', '%'.$search.'%')->orWhere('municipality', 'like', '%'.$search.'%')))
            ->when(($filters['category'] ?? 'school') !== 'school' || filled($filters['species_id'] ?? null) || filled($filters['adoption_status'] ?? null), fn ($q) => $q->whereRaw('1=0'))
            ->select(['id', 'name', 'slug', 'city', 'municipality', 'latitude', 'longitude'])->orderBy('id')->limit(2000)->get();

        $groups = TreeGroup::published()->whereNotNull('latitude')->whereNotNull('longitude')
            ->with(['school:id,name,city,municipality', 'generation:id,graduation_year'])
            ->when($school, fn ($q) => $q->where('school_id', $school->id))
            ->when($generationId, fn ($q) => $q->where('school_generation_id', $generationId))
            ->when($group, fn ($q) => $q->whereKey($group->id))->when($tree, fn ($q) => $q->whereRaw('1=0'))
            ->when(($filters['category'] ?? 'group') !== 'group' || filled($filters['species_id'] ?? null) || filled($filters['adoption_status'] ?? null), fn ($q) => $q->whereRaw('1=0'))
            ->when(($filters['search'] ?? null), fn ($q, $search) => $q->where(fn ($g) => $g->where('name', 'like', '%'.$search.'%')->orWhereHas('school', fn ($s) => $s->where('name', 'like', '%'.$search.'%')->orWhere('city', 'like', '%'.$search.'%')->orWhere('municipality', 'like', '%'.$search.'%'))))
            ->select(['id', 'name', 'translations', 'slug', 'school_id', 'school_generation_id', 'declared_tree_count', 'latitude', 'longitude'])->withCount('trees');
        $this->relationships($groups, $filters);
        $groups = $groups->orderBy('id')->limit(2000)->get();

        $trees = Tree::published()->whereNotNull('latitude')->whereNotNull('longitude')
            ->with(['species:id,common_name,translations', 'school:id,name,city,municipality', 'generation:id,graduation_year', 'reservedAdoption:id,tree_id,reserved_tree_id,status,reservation_expires_at,guardian_name,public_display_preference'])
            ->when($school, fn ($q) => $q->where('school_id', $school->id))
            ->when($generationId, fn ($q) => $q->where('school_generation_id', $generationId))
            ->when($tree, fn ($q) => $q->whereKey($tree->id))->when($group, fn ($q) => $q->where('tree_group_id', $group->id))
            ->when(in_array($filters['category'] ?? null, ['school', 'group'], true), fn ($q) => $q->whereRaw('1=0'))
            ->when(($filters['category'] ?? null) === 'adopted', fn ($q) => $q->adoptionStatus('adopted'))
            ->when(($filters['species_id'] ?? null), fn ($q, $id) => $q->where('tree_species_id', $id))
            ->when(($filters['adoption_status'] ?? null), fn ($q, $status) => $q->adoptionStatus($status))
            ->when(($filters['search'] ?? null), fn ($q, $search) => $q->where(fn ($t) => $t->where('title', 'like', '%'.$search.'%')->orWhere('public_identifier', 'like', '%'.$search.'%')->orWhereHas('species', fn ($s) => $s->where('common_name', 'like', '%'.$search.'%'))->orWhereHas('school', fn ($s) => $s->where('name', 'like', '%'.$search.'%')->orWhere('city', 'like', '%'.$search.'%')->orWhere('municipality', 'like', '%'.$search.'%'))))
            ->select(['id', 'public_identifier', 'tree_species_id', 'school_id', 'school_generation_id', 'tree_group_id', 'title', 'translations', 'latitude', 'longitude', 'adoption_status', 'planted_at']);
        $this->relationships($trees, $filters);
        $trees = $trees->orderBy('id')->limit(5000)->get();

        return [
            ...$schools->map(fn ($s) => [
                'id' => 'school-'.$s->id, 'identifier' => $s->id, 'type' => 'school', 'title' => $s->name, 'subtitle' => $s->city,
                'lat' => $s->latitude, 'lng' => $s->longitude, 'url' => route('schools.show', $s->slug),
                'graduation_years' => $s->generations->pluck('graduation_year')->unique()->values()->all(),
                'school_id' => $s->id, 'school_name' => $s->name, 'city' => $s->city, 'municipality' => $s->municipality,
            ])->all(),
            ...$groups->map(fn ($g) => [
                'id' => 'group-'.$g->id, 'identifier' => $g->slug, 'type' => 'group', 'title' => $g->localized('name'),
                'subtitle' => 'Prijavljeno ukupno: '.($g->declared_tree_count ?? 'nije potvrđeno').'; pojedinačno registrovano: '.$g->trees_count.($g->school ? ' · '.$g->school->name : ''),
                'lat' => $g->latitude, 'lng' => $g->longitude, 'url' => route('groups.show', $g->slug),
                'declared_tree_count' => $g->declared_tree_count, 'registered_tree_count' => $g->trees_count,
                'school_id' => $g->school_id, 'school_name' => $g->school?->name, 'city' => $g->school?->city, 'municipality' => $g->school?->municipality,
                'graduation_year' => $g->generation?->graduation_year,
            ])->all(),
            ...$trees->map(fn ($t) => [
                'id' => 'tree-'.$t->id, 'identifier' => $t->public_identifier, 'type' => $t->effectiveAdoptionStatus() === 'adopted' ? 'adopted' : 'tree',
                'title' => ($t->localized('title') ?: $t->species->localized('common_name')).' · '.$t->public_identifier, 'subtitle' => $t->school?->name ?? 'Zelena diploma',
                'guardian' => $t->reservedAdoption?->status === 'active' ? $t->reservedAdoption->displayName() : null, 'adoption_status' => $t->effectiveAdoptionStatus(), 'lat' => $t->latitude, 'lng' => $t->longitude, 'url' => route('trees.show', $t->public_identifier),
                'group_id' => $t->tree_group_id ? 'group-'.$t->tree_group_id : null,
                'school_id' => $t->school_id, 'school_name' => $t->school?->name, 'city' => $t->school?->city, 'municipality' => $t->school?->municipality,
                'graduation_year' => $t->generation?->graduation_year, 'species_id' => $t->tree_species_id, 'species' => $t->species->localized('common_name'),
                'planted_at' => $t->planted_at?->format('Y-m-d'),
            ])->all(),
        ];
    }

    private function city(Builder $query, string $city): void
    {
        $query->where(fn ($q) => $q->where('city', $city)->orWhere('municipality', $city));
    }

    private function relationships(Builder $query, array $filters): void
    {
        $query->when(($filters['school_id'] ?? null), fn ($q, $id) => $q->where('school_id', $id))
            ->when(($filters['city'] ?? null), fn ($q, $city) => $q->whereHas('school', fn ($s) => $this->city($s, $city)))
            ->when(($filters['graduation_year'] ?? null), fn ($q, $year) => $q->whereHas('generation', fn ($g) => $g->where('graduation_year', $year)));
    }
}
