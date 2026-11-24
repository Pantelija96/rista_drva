<?php

return [
    'tile_url' => env('MAP_TILE_URL', 'https://tile.openstreetmap.org/{z}/{x}/{y}.png'),
    'attribution' => env('MAP_ATTRIBUTION', '&copy; OpenStreetMap contributors'),
    'latitude' => (float) env('MAP_DEFAULT_LAT', 44.6780),
    'longitude' => (float) env('MAP_DEFAULT_LNG', 20.7180),
    'zoom' => (int) env('MAP_DEFAULT_ZOOM', 11),
    'max_zoom' => (int) env('MAP_MAX_ZOOM', 19),
];
