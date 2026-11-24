@extends('layouts.app')
@section('title', $species->localized('common_name'). ' | EcoSync')
@section('description', __('public.zelena_diploma_the_future_is_planted_today'))
@section('content')
<section class="ngo-subhero ngo-green"><div class="container container-xl-custom"><p class="ngo-eyebrow">{{ __('public.zelena_diploma') }}</p><h1>{{ $species->localized('common_name') }}</h1></div></section>
<section class="ngo-section"><div class="container container-xl-custom">
<p class="ngo-eyebrow">{{ $species->scientific_name }}</p>
@if ($species->main_image_path)<img class="img-fluid ngo-profile-image" src="{{ Storage::disk('public')->url($species->main_image_path) }}" alt="{{ $species->localized('common_name') }}">@endif
<p>{{ $species->localized('short_description') }}</p><p>{{ $species->localized('description') }}</p>
@foreach (['preferred_habitat'=>__('public.habitat'),'sunlight_requirements'=>__('public.sunlight'),'water_requirements'=>__('public.water'),'planting_recommendations'=>__('public.planting'),'maintenance_guidelines'=>__('public.care'),'ecological_benefits'=>__('public.ecological_benefits'),'interesting_facts'=>__('public.interesting_facts')] as $field=>$label)
@if ($species->localized($field))<h2>{{ App\Support\PublicText::label($label) }}</h2><p>{{ $species->localized($field) }}</p>@endif
@endforeach
<dl>
@if ($species->native_to_serbia !== null)<dt>{{ __('public.native_to_serbia') }}</dt><dd>{{ $species->native_to_serbia ? __('public.yes') : __('public.no') }}</dd>@endif
@if ($species->deciduous_or_evergreen)<dt>{{ __('public.type') }}</dt><dd>{{ $species->deciduous_or_evergreen === 'deciduous' ? __('public.deciduous') : __('public.evergreen') }}</dd>@endif
@if ($species->typical_height_m)<dt>{{ __('public.typical_height') }}</dt><dd>{{ $species->typical_height_m }} m</dd>@endif
@if ($species->expected_lifespan_years)<dt>{{ __('public.expected_lifespan') }}</dt><dd>{{ $species->expected_lifespan_years }} {{ __('public.years') }}</dd>@endif
</dl>
<div class="row g-4">@foreach ($species->gallery_paths ?? [] as $path)<div class="col-md-4"><a data-gallery-image href="{{ Storage::disk('public')->url($path) }}"><img class="img-fluid" src="{{ Storage::disk('public')->url($path) }}" alt="{{ $species->localized('common_name') }}" loading="lazy"></a></div>@endforeach</div>
</div></section>
@endsection
