@extends('layouts.app')

@section('title', __('public.gallery_ecosync_zelena_diploma'))
@section('description', __('public.memories_that_grow_albums_of_schools_planting_volunteers_and'))

@section('content')
<section class="ngo-subhero ngo-green">
<div class="container container-xl-custom">
<div class="row g-4 align-items-center">
<div class="col-lg-8">
<p class="ngo-eyebrow">{{ __('public.gallery_of_green_stories') }}</p>
<h1>{{ __('public.memories_that_grow_2') }}</h1>
<p class="ngo-lead">{{ __('public.every_planting_workshop_and_shared_activity_is_part_of') }}</p></div>
<div class="col-lg-4 d-none d-lg-block"><img src="{{ asset('assets/images/seedlings.jpg') }}" alt="{{ __('public.illustrative_nature_photo') }}" class="img-fluid ngo-subhero-image" loading="lazy" decoding="async"></div></div></div></section>
<section class="ngo-section ">
<div class="container container-xl-custom">

<div class="ngo-gallery-toolbar" role="group" aria-label="{{ __('public.filter_albums') }}"><button data-gallery-filter="all" class="active" aria-pressed="true">{{ __('public.all_albums') }}</button><button data-gallery-filter="škole" class="" aria-pressed="false">{{ __('public.schools') }}</button><button data-gallery-filter="sadnje" class="" aria-pressed="false">{{ __('public.planting_events') }}</button><button data-gallery-filter="volonteri" class="" aria-pressed="false">{{ __('public.volunteers') }}</button><button data-gallery-filter="radionice" class="" aria-pressed="false">{{ __('public.workshops') }}</button><span id="album-count" role="status">{{ $albums->count() }} {{ __('public.albums') }}</span></div>
<div class="row g-4" id="album-grid">
@forelse ($albums as $index => $album)
    <x-album-card :album="$album" :index="$index" />
@empty
<p>{{ __('public.photos_from_our_activities_will_be_published_soon') }}</p>
@endforelse
</div>
<div class="ngo-gallery-cta">
<p class="ngo-eyebrow">{{ __('public.the_next_story_could_be_yours') }}</p>
<h2 class="ngo-heading">{{ __('public.see_you_at_the_next_green_step') }}</h2><a href="{{ route('join') }}" class="btn btn-modern btn-primary btn-arrow-effect-1 ngo-btn">{{ __('public.get_involved_2') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div></div></section>
@endsection
