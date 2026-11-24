@extends('layouts.app')

@section('title', __('public.our_mission_ecosync_zelena_diploma'))
@section('description', __('public.helping_every_generation_leave_a_green_footprint_meet_ecosync'))

@section('content')
<section class="ngo-section ">
<div class="container container-xl-custom">
<div class="row g-5 align-items-center ">
<div class="col-lg-7">
<p class="ngo-eyebrow">{{ __('public.01_our_mission') }}</p>
<h1 class="ngo-heading">{{ __('public.our_mission_2') }}</h1>
<h2 class="ngo-heading">{{ __('public.helping_every_generation_leave_a_green_footprint') }}</h2>
<p>{{ __('public.we_believe_the_end_of_one_educational_chapter_can') }}</p></div>
<div class="col-lg-5"><img src="{{ asset('assets/images/forest.jpg') }}" alt="{{ __('public.illustrative_nature_photo') }}" class="img-fluid ngo-editorial-image" loading="lazy" decoding="async"></div></div></div></section>
<section class="ngo-section ngo-cream">
<div class="container container-xl-custom">
<div class="row g-5 align-items-center flex-lg-row-reverse">
<div class="col-lg-7">
<p class="ngo-eyebrow">{{ __('public.02_our_idea') }}</p>
<h2 class="ngo-heading">{{ __('public.one_tree_for_each_graduating_class') }}</h2>
<p>{{ __('public.we_hope_to_plant_trees_every_year_in_honor') }}</p>
<p>{{ __('public.we_want_every_school_that_wishes_to_participate_to') }}</p></div>
<div class="col-lg-5"><img src="{{ asset('assets/images/seedlings.jpg') }}" alt="{{ __('public.illustrative_nature_photo') }}" class="img-fluid ngo-editorial-image" loading="lazy" decoding="async"></div></div></div></section>
<section class="ngo-section ">
<div class="container container-xl-custom">
<div class="row g-5 align-items-center ">
<div class="col-lg-7">
<p class="ngo-eyebrow">{{ __('public.03_our_vision') }}</p>
<h2 class="ngo-heading">{{ __('public.from_one_school_to_all_of_serbia') }}</h2>
<p>{{ __('public.our_vision_is_a_serbia_where_every_year_new') }}</p>
<p>{{ __('public.we_know_this_change_takes_time_cooperation_and_support') }}</p></div>
<div class="col-lg-5"><img src="{{ asset('assets/images/gardening.jpg') }}" alt="{{ __('public.illustrative_nature_photo') }}" class="img-fluid ngo-editorial-image" loading="lazy" decoding="async"></div></div></div></section>
<section class="ngo-section ngo-cream">
<div class="container container-xl-custom">
<div class="row g-5 align-items-center flex-lg-row-reverse">
<div class="col-lg-7">
<p class="ngo-eyebrow">{{ __('public.04_the_future_is_planted_today') }}</p>
<h2 class="ngo-heading">Jedan maturant = Jedno drvo.</h2>
<p>{{ __('public.together_we_create_a_tradition_where_every_generation_leaves') }}</p><a href="{{ route('join') }}" class="btn btn-modern btn-primary btn-arrow-effect-1 ngo-btn">{{ __('public.join_the_initiative') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div>
<div class="col-lg-5"><img src="{{ asset('assets/images/planting.jpg') }}" alt="{{ __('public.illustrative_nature_photo') }}" class="img-fluid ngo-editorial-image" loading="lazy" decoding="async"></div></div></div></section>
@endsection
