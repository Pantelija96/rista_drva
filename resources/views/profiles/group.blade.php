@extends('layouts.app')
@section('title', $group->localized('name'). ' | EcoSync')
@section('description', __('public.zelena_diploma_the_future_is_planted_today'))
@section('content')
<section class="ngo-subhero ngo-green"><div class="container container-xl-custom"><p class="ngo-eyebrow">{{ __('public.zelena_diploma') }}</p><h1>{{ $group->localized('name') }}</h1></div></section>
<section class="ngo-section"><div class="container container-xl-custom">
<p>{{ $group->localized('description') }}</p>
@if ($group->featured_image_path)<img class="img-fluid ngo-profile-image" src="{{ Storage::disk('public')->url($group->featured_image_path) }}" alt="{{ $group->localized('name') }}">@endif
@if ($group->school)<p><a href="{{ route('schools.show',$group->school->slug) }}">{{ $group->school->name }}</a></p>@endif
@if ($group->generation)<p><a href="{{ route('generations.show',[$group->school->slug,$group->generation]) }}">{{ __('public.graduating_class_2') }}{{ $group->generation->graduation_year }}</a></p>@endif
<p>{{ __('public.declared_total_trees') }} {{ $group->declared_tree_count ?? __('public.not_confirmed_2') }}<br>{{ __('public.individually_registered') }} {{ $group->trees_count }}</p>
@include('profiles.related')<x-leaflet-map id="group-map" :markers="$markers" />
</div></section>
@endsection
