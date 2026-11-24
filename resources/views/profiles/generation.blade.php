@extends('layouts.app')
@section('title', ($generation->localized('name') ?: __('public.graduating_class_2').$generation->graduation_year). ' | EcoSync')
@section('description', __('public.zelena_diploma_the_future_is_planted_today'))
@section('content')
<section class="ngo-subhero ngo-green"><div class="container container-xl-custom"><p class="ngo-eyebrow">{{ __('public.zelena_diploma') }}</p><h1>{{ ($generation->localized('name') ?: __('public.graduating_class_2').$generation->graduation_year) }}</h1></div></section>
<section class="ngo-section"><div class="container container-xl-custom">
<p><a href="{{ route('schools.show',$school->slug) }}">{{ $school->name }}</a></p>
<p>{{ __('public.graduation_year') }} {{ $generation->graduation_year }}</p><p>{{ App\Support\PublicText::label(App\Support\DomainRules::PLANTING[$generation->planting_model]) }}</p>
@include('profiles.related')
<x-leaflet-map id="generation-map" :markers="$markers" />
</div></section>
@endsection
