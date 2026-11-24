<?php

namespace App\Http\Controllers;

use App\Models\GalleryAlbum;
use App\Models\School;
use App\Models\SchoolGeneration;
use App\Models\Tree;
use App\Models\TreeGroup;
use App\Models\TreeSpecies;
use App\Services\PublicMapData;

class PublicProfileController
{
    public function school(School $school, PublicMapData $map)
    {
        abort_unless($school->status === 'published', 404);

        return view('profiles.school', ['school' => $school, 'generations' => $school->generations()->published()->orderByDesc('graduation_year')->get(),
            'trees' => $school->trees()->published()->with('species')->get(), 'groups' => $school->groups()->published()->withCount('trees')->get(),
            'albums' => $school->albums()->published()->with(['images' => fn ($q) => $q->where('published', true)->orderBy('sort_order')])->get(),
            'markers' => $map->markers(school: $school)]);
    }

    public function generation(School $school, SchoolGeneration $generation, PublicMapData $map)
    {
        abort_unless($school->status === 'published' && $generation->status === 'published' && $generation->school_id === $school->id, 404);

        return view('profiles.generation', ['school' => $school, 'generation' => $generation,
            'trees' => $generation->trees()->published()->with('species')->get(), 'groups' => $generation->groups()->published()->withCount('trees')->get(),
            'albums' => $generation->albums()->published()->with(['images' => fn ($q) => $q->where('published', true)->orderBy('sort_order')])->get(),
            'markers' => $map->markers(school: $school, generationId: $generation->id)]);
    }

    public function tree(string $identifier, PublicMapData $map)
    {
        $tree = Tree::published()->where('public_identifier', $identifier)->with(['species', 'school', 'generation', 'group', 'reservedAdoption'])->firstOrFail();

        return view('profiles.tree', ['tree' => $tree, 'markers' => $map->markers(tree: $tree), 'maintenance' => $tree->maintenanceRecords()->where('review_status', 'approved')->with(['photos' => fn ($q) => $q->where('review_status', 'approved')])->orderByDesc('performed_at')->get()]);
    }

    public function group(string $slug, PublicMapData $map)
    {
        $group = TreeGroup::published()->where('slug', $slug)->with(['school', 'generation'])->withCount('trees')->firstOrFail();

        return view('profiles.group', ['group' => $group, 'trees' => $group->trees()->published()->with('species')->get(), 'markers' => $map->markers(group: $group)]);
    }

    public function species(string $slug)
    {
        return view('profiles.species', ['species' => TreeSpecies::published()->where('slug', $slug)->firstOrFail()]);
    }

    public function album(string $slug)
    {
        $album = GalleryAlbum::published()->where('slug', $slug)->with(['images' => fn ($q) => $q->where('published', true)->orderBy('sort_order')->orderBy('id')])->firstOrFail();

        return view('profiles.album', compact('album'));
    }
}
