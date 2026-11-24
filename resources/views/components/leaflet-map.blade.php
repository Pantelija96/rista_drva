@props([
    'id' => null, 'markers' => null, 'center' => null, 'zoom' => null,
    'editable' => false, 'filters' => true, 'clustering' => true,
    'fitToMarkers' => true, 'categories' => ['school', 'tree', 'adopted', 'group'],
])
@php
    $mapId = $id ?? 'map-'.Illuminate\Support\Str::uuid();
    $settings = [
        'tileUrl' => config('maps.tile_url'),
        'attribution' => config('maps.attribution'),
        'center' => $center ?? ['lat' => config('maps.latitude'), 'lng' => config('maps.longitude')],
        'zoom' => $zoom ?? config('maps.zoom'), 'maxZoom' => config('maps.max_zoom'),
        'markers' => $markers, 'url' => route('map.markers'), 'editable' => $editable,
        'clustering' => $clustering, 'fitToMarkers' => $fitToMarkers, 'categories' => $categories,
    ];
@endphp
<div class="ecosync-map-container" data-leaflet-map>
    <script type="application/json" data-map-settings>{!! Illuminate\Support\Js::encode($settings) !!}</script>
    @if ($filters && !$editable)
        <div class="ngo-map-toolbar ecosync-map-filters" role="group" aria-label="{{ __('public.map_filters') }}">
            <label>{{ __('public.find_on_the_map') }}<input type="search" class="form-control" data-map-search placeholder="{{ __('public.school_name_species_or_id') }}"></label>
            <label>{{ __('public.show') }}<select class="form-select" data-map-filter="category"><option value="">{{ __('public.all_locations') }}</option><option value="school">{{ __('public.schools') }}</option><option value="tree">{{ __('public.individual_trees') }}</option><option value="adopted">{{ __('public.adopted_trees') }}</option><option value="group">{{ __('public.tree_groups') }}</option></select></label>
            <label>{{ __('public.city_municipality') }}<select class="form-select" data-map-filter="city"><option value="">{{ __('public.all_cities_municipalities') }}</option></select></label>
            <label>{{ __('public.school') }}<select class="form-select" data-map-filter="school_id"><option value="">{{ __('public.all_schools') }}</option></select></label>
            <label>{{ __('public.graduating_class') }}<select class="form-select" data-map-filter="graduation_year"><option value="">{{ __('public.all_graduating_classes') }}</option></select></label>
            <label>{{ __('public.tree_species') }}<select class="form-select" data-map-filter="species_id"><option value="">{{ __('public.all_species') }}</option></select></label>
            <label>{{ __('public.adoption') }}<select class="form-select" data-map-filter="adoption_status"><option value="">{{ __('public.all_statuses') }}</option><option value="available">{{ __('public.available') }}</option><option value="adopted">{{ __('public.adopted') }}</option><option value="unavailable">{{ __('public.unavailable') }}</option></select></label>
        </div>
    @endif
    <div id="{{ $mapId }}" class="ecosync-leaflet-map" aria-label="{{ $editable ? __('public.choose_a_confirmed_location') : __('public.map_of_confirmed_locations') }}" role="region" wire:ignore></div>
    <p data-map-status role="status">{{ __('public.loading_map') }}</p>
    @unless ($editable)
        <div class="ngo-map-legend"><span><i class="fas fa-school" aria-hidden="true"></i> {{ __('public.school') }}</span><span><i class="fas fa-tree" aria-hidden="true"></i> {{ __('public.tree') }}</span><span><i class="fas fa-heart" aria-hidden="true"></i> {{ __('public.adopted_tree') }}</span><span><i class="fas fa-layer-group" aria-hidden="true"></i> {{ __('public.tree_group') }}</span></div>
        <p class="ngo-small">{{ __('public.a_group_s_declared_count_includes_its_individual_trees') }}</p>
        <div class="d-flex gap-3 mb-3"><button type="button" class="btn btn-outline-primary" data-map-reset>{{ __('flows.reset') }}</button><button type="button" class="btn btn-outline-primary" data-map-clear>{{ __('flows.clear') }}</button></div>
        <div data-map-preview class="ngo-map-preview-card" aria-live="polite" hidden></div>
        <div data-map-links class="ngo-map-results"></div>
    @endunless
    <noscript><p>{{ __('public.enable_javascript_to_use_the_interactive_map_you_can') }}</p></noscript>
</div>
