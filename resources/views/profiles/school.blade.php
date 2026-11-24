@extends('layouts.app')
@section('title', $school->name. ' | EcoSync')
@section('description', __('public.zelena_diploma_the_future_is_planted_today'))
@section('content')
<section class="ngo-subhero ngo-green"><div class="container container-xl-custom"><p class="ngo-eyebrow">{{ __('public.zelena_diploma') }}</p><h1>{{ $school->name }}</h1></div></section>
<section class="ngo-section"><div class="container container-xl-custom">
@if ($school->logo_path)<img class="img-fluid" style="max-width:180px" src="{{ Storage::disk('public')->url($school->logo_path) }}" alt="{{ $school->name }}">@endif
<p class="ngo-eyebrow">{{ $school->city }}</p><p>{{ $school->localized('description') }}</p>
@if ($school->featured_image_path)<img class="img-fluid ngo-profile-image" src="{{ Storage::disk('public')->url($school->featured_image_path) }}" alt="{{ $school->name }}">@endif
<h2>{{ __('public.graduating_classes') }}</h2><ul>@forelse ($generations as $generation)<li><a href="{{ route('generations.show',[$school->slug,$generation]) }}">{{ $generation->graduation_year }} — {{ $generation->localized('name') ?? $generation->program }}</a></li>@empty<li>{{ __('public.graduating_classes_have_not_been_published_yet') }}</li>@endforelse</ul>
@include('profiles.related')
<x-leaflet-map id="school-map" :markers="$markers" />
</div></section>
@endsection
