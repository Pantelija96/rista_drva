@extends('layouts.app')

@section('title', __('public.our_green_footprint_ecosync_zelena_diploma'))
@section('description', __('public.the_digital_tree_map_and_green_community_of_the'))

@section('content')
<section class="ngo-subhero ngo-green">
<div class="container container-xl-custom">
<div class="row g-4 align-items-center">
<div class="col-lg-8">
<p class="ngo-eyebrow">{{ __('public.ecosync_zelena_diploma') }}</p>
<h1>{{ __('public.our_green_footprint') }}</h1>
<p class="ngo-lead">{{ __('public.every_planted_tree_has_its_place_its_story_and') }}</p></div>
<div class="col-lg-4 d-none d-lg-block"><img src="{{ asset('assets/images/forest.jpg') }}" alt="{{ __('public.illustrative_nature_photo') }}" class="img-fluid ngo-subhero-image" loading="lazy" decoding="async"></div></div></div></section>
<section class="ngo-section ngo-map-section" id="mapa">
<div class="container container-xl-custom">
<x-leaflet-map id="green-trace-map" />
<div class="ngo-map-explainer"><h2>{{ __('public.from_a_tree_to_lasting_care') }}</h2><p>{{ __('public.meet_schools_graduating_classes_and_trees_with_confirmed_locations') }}</p><p>{{ __('public.open_an_available_tree_s_profile_and_submit_an') }}</p></div>
</div></section>
<section class="ngo-section ngo-cream" id="zajednica">
<div class="container container-xl-custom">
<p class="ngo-eyebrow">{{ __('public.people_driving_change') }}</p>
<h2 class="ngo-heading">{{ __('public.green_community') }}</h2>
<div class="ngo-community-group">
<h3>{{ __('public.schools_and_educational_institutions') }}</h3>
<p>{{ __('public.14_partner_schools_the_official_list_of_schools_and') }}</p>
<div class="row g-4">
@forelse ($schools as $school)
<div class="col-md-6 col-lg-3"><article class="ngo-school-example"><div class="ngo-card-copy"><h4><a href="{{ route('schools.show',$school->slug) }}">{{ $school->name }}</a></h4><p>{{ $school->city }}</p><ul>@foreach ($school->generations as $generation)<li><a href="{{ route('generations.show',[$school->slug,$generation]) }}">{{ __('public.graduating_class_2') }}{{ $generation->graduation_year }}</a></li>@endforeach</ul></div></article></div>
@empty<p>{{ __('public.school_profiles_will_be_published_after_confirmation') }}</p>@endforelse
</div></div>
<div class="ngo-community-group">
<h3>{{ __('public.partners_and_support') }}</h3>
<p>{{ __('public.companies_organizations_and_local_partners_who_have_contributed_saplings') }}</p>
<div class="ngo-logo-grid"><span>{{ __('public.official_partners_will_be_published_after_confirmation') }}</span><span>{{ __('public.space_for_approved_logos') }}</span></div>
<p class="ngo-small mt-3">{{ __('public.main_project_funder_dm_drogerie_markt_final_sponsor_branding') }}</p></div>
<div class="ngo-community-group">
<h3>{{ __('public.adopt_a_tree_our_guardians') }}</h3>
<div class="row g-3">@forelse ($guardians as $guardian)<div class="col-md-4"><article class="ngo-guardian"><h4>{{ $guardian['name'] }}</h4><p>{{ __('public.active_adoptions') }} {{ $guardian['trees'] }}</p></article></div>@empty<p>{{ __('public.guardians_will_be_displayed_according_to_their_privacy_preference') }}</p>@endforelse</div></div>
<div class="ngo-actions"><a href="{{ route('join') }}#prijava-skole" class="btn btn-modern btn-primary btn-arrow-effect-1 ngo-btn">{{ __('public.get_your_school_involved') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a><a href="#mapa" class="ngo-text-link">{{ __('public.adopt_a_tree') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a><a href="{{ route('home') }}#podrzi-inicijativu" class="ngo-text-link">{{ __('public.support_the_initiative_2') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></a></div></div></section>
@endsection
