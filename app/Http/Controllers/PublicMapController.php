<?php

namespace App\Http\Controllers;

use App\Http\Requests\PublicMapRequest;
use App\Services\PublicMapData;

class PublicMapController
{
    public function __invoke(PublicMapRequest $request, PublicMapData $map)
    {
        return response()->json([
            'markers' => $map->markers(filters: $request->validated()),
            'note' => __('public.locations_are_confirmed_the_number_of_markers_is_not_the'),
        ]);
    }
}
