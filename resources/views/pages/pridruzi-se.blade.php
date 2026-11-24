@extends('layouts.app')

@section('title', __('public.get_involved_ecosync_zelena_diploma'))
@section('description', __('public.zelena_diploma_schools_choose_one_tree_for_a_graduating'))

@section('content')
<section class="ngo-subhero ngo-green">
<div class="container container-xl-custom">
<div class="row g-4 align-items-center">
<div class="col-lg-8">
<p class="ngo-eyebrow">JEDAN MATURANT = JEDNO STABLO</p>
<h1>{{ __('public.zelena_diploma_2') }}</h1>
<p class="ngo-lead">{{ __('public.a_green_memory_of_school_days_that_keeps_growing') }}</p></div>
<div class="col-lg-4 d-none d-lg-block"><img src="{{ asset('assets/images/seedlings.jpg') }}" alt="{{ __('public.illustrative_nature_photo') }}" class="img-fluid ngo-subhero-image" loading="lazy" decoding="async"></div></div></div></section>
<section class="ngo-section ">
<div class="container container-xl-custom">
<p class="ngo-eyebrow">{{ __('public.a_model_for_your_school') }}</p>
<h2 class="ngo-heading">{{ __('public.one_idea_two_ways_to_grow') }}</h2>
<p>{{ __('public.schools_can_choose_the_model_that_best_fits_their') }}</p>
<div class="row g-4">
<div class="col-md-6">
<article class="ngo-model h-100"><span>{{ __('public.model_01') }}</span>
<h3>{{ __('public.one_graduating_class_one_symbolic_tree') }}</h3>
<p>{{ __('public.by_choosing_and_planting_one_tree_together_a_graduating') }}</p>
<p class="ngo-model-bottom">{{ __('public.together_we_plant_memories_and_leave_a_footprint_for') }}</p></article></div>
<div class="col-md-6">
<article class="ngo-model h-100"><span>{{ __('public.model_02') }}</span>
<h3>{{ __('public.one_graduate_one_tree') }}</h3>
<p>{{ __('public.we_plant_one_tree_for_every_graduate_in_school') }}</p>
<p class="ngo-model-bottom">{{ __('public.every_planted_tree_honors_one_graduating_class_and_grows') }}</p></article></div></div></div></section>
<section class="ngo-section ">
<div class="container container-xl-custom">
<p class="ngo-eyebrow">{{ __('public.from_idea_to_planting') }}</p>
<h2 class="ngo-heading">{{ __('public.how_your_school_joins_the_story') }}</h2>
<div class="row g-4">
<div class="col-md-6 col-lg-3">
<article class="ngo-step"><span>01</span>
<h3>{{ __('public.we_talk') }}</h3>
<p>{{ __('public.we_learn_about_the_school_its_idea_and_its') }}</p></article></div>
<div class="col-md-6 col-lg-3">
<article class="ngo-step"><span>02</span>
<h3>{{ __('public.we_plan') }}</h3>
<p>{{ __('public.we_agree_on_the_model_location_planting_plan_and') }}</p></article></div>
<div class="col-md-6 col-lg-3">
<article class="ngo-step"><span>03</span>
<h3>{{ __('public.we_plant_together') }}</h3>
<p>{{ __('public.students_teachers_and_volunteers_take_part_in_a_green') }}</p></article></div>
<div class="col-md-6 col-lg-3">
<article class="ngo-step"><span>04</span>
<h3>{{ __('public.we_keep_caring') }}</h3>
<p>{{ __('public.together_we_care_for_trees_and_follow_their_growth') }}</p></article></div></div></div></section>
<section class="ngo-section ngo-cream">
<div class="container container-xl-custom">
<div class="row g-5">
<div class="col-lg-4">
<p class="ngo-eyebrow">{{ __('public.school_questions') }}</p>
<h2 class="ngo-heading">{{ __('public.good_to_know') }}</h2></div>
<div class="col-lg-8">
<div class="accordion accordion-modern-2" id="school-faq">
<div class="card card-default">
<div class="card-header" id="q-0">
<h3 class="card-title m-0"><button type="button" class="accordion-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#a-0" aria-expanded="false" aria-controls="a-0">{{ __('public.does_every_graduate_get_a_tree') }}</button></h3></div>
<div class="collapse" id="a-0" aria-labelledby="q-0" data-bs-parent="#school-faq">
<div class="card-body">
<p>{{ __('public.schools_choose_the_planting_model_that_best_fits_their') }}</p></div></div></div>
<div class="card card-default">
<div class="card-header" id="q-1">
<h3 class="card-title m-0"><button type="button" class="accordion-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#a-1" aria-expanded="false" aria-controls="a-1">{{ __('public.who_chooses_the_tree_species_and_planting_site') }}</button></h3></div>
<div class="collapse" id="a-1" aria-labelledby="q-1" data-bs-parent="#school-faq">
<div class="card-body">
<p>{{ __('public.we_agree_on_the_tree_species_and_planting_site') }}</p></div></div></div>
<div class="card card-default">
<div class="card-header" id="q-2">
<h3 class="card-title m-0"><button type="button" class="accordion-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#a-2" aria-expanded="false" aria-controls="a-2">{{ __('public.who_cares_for_the_trees_after_planting') }}</button></h3></div>
<div class="collapse" id="a-2" aria-labelledby="q-2" data-bs-parent="#school-faq">
<div class="card-body">
<p>{{ __('public.tree_care_continues_after_planting_schools_can_organize_care') }}</p></div></div></div>
<div class="card card-default">
<div class="card-header" id="q-3">
<h3 class="card-title m-0"><button type="button" class="accordion-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#a-3" aria-expanded="false" aria-controls="a-3">{{ __('public.how_can_our_school_get_involved') }}</button></h3></div>
<div class="collapse" id="a-3" aria-labelledby="q-3" data-bs-parent="#school-faq">
<div class="card-body">
<p>{{ __('public.school_faq_contact') }}</p></div></div></div>
<div class="card card-default">
<div class="card-header" id="q-4">
<h3 class="card-title m-0"><button type="button" class="accordion-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#a-4" aria-expanded="false" aria-controls="a-4">{{ __('public.can_a_school_organize_its_own_planting_event') }}</button></h3></div>
<div class="collapse" id="a-4" aria-labelledby="q-4" data-bs-parent="#school-faq">
<div class="card-body">
<p>{{ __('public.of_course_your_school_can_organize_its_own_event') }}</p></div></div></div>
<div class="card card-default">
<div class="card-header" id="q-5">
<h3 class="card-title m-0"><button type="button" class="accordion-toggle collapsed" data-bs-toggle="collapse" data-bs-target="#a-5" aria-expanded="false" aria-controls="a-5">{{ __('public.is_participation_free_for_schools') }}</button></h3></div>
<div class="collapse" id="a-5" aria-labelledby="q-5" data-bs-parent="#school-faq">
<div class="card-body">
<p>{{ __('public.yes_participation_is_free_depending_on_available_resources_and') }}</p></div></div></div></div></div></div></div></section>
<section class="ngo-section " id="prijava-skole">
<div class="container container-xl-custom">
<div class="row g-5">
<div class="col-lg-4">
<p class="ngo-eyebrow">{{ __('public.get_involved') }}</p>
<h2 class="ngo-heading">{{ __('public.get_your_school_involved') }}</h2>
<p>{{ __('public.let_s_choose_a_planting_model_together_email_or') }}</p>
<div class="ngo-contact-links"><a href="mailto:eoecosync@gmail.com">eoecosync@gmail.com</a><a href="tel:+381606955554">0606955554</a></div></div>
<div class="col-lg-8">
@if (session('school_application_success'))
<div class="alert alert-success" role="status">{{ session('school_application_success') }} <p>{{ __('flows.saved') }}</p></div>
@endif
@if ($errors->any())
<div class="alert alert-danger" role="alert"><p>{{ __('public.please_check_the_information_you_entered') }}</p><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
<x-workflow-progress kind="school" />
<form method="POST" action="{{ route('school-applications.store') }}">
@csrf
<input type="hidden" name="submission_token" value="{{ old('submission_token', (string) Illuminate\Support\Str::uuid()) }}">
<div class="ngo-honeypot" aria-hidden="true"><label>{{ __('public.your_website') }}<input name="website" type="text" tabindex="-1" autocomplete="off"></label></div>
<div class="row g-3">
<div class="col-sm-6"><label for="school-name">{{ __('public.school_faculty_name') }}</label><input id="school-name" name="school_name" type="text" class="form-control" required maxlength="160"  value="{{ old('school_name') }}"></div>
<div class="col-sm-6"><label for="school-city">{{ __('public.city_municipality_2') }}</label><input id="school-city" name="city" type="text" class="form-control" required maxlength="160"  value="{{ old('city') }}"></div>
<div class="col-sm-6"><label for="school-municipality">{{ __('public.municipality_optional') }}</label><input id="school-municipality" name="municipality" class="form-control" maxlength="160" value="{{ old('municipality') }}"></div>
<div class="col-sm-6"><label for="school-person">{{ __('public.contact_person_s_full_name') }}</label><input id="school-person" name="contact_person" type="text" class="form-control" required maxlength="160"  value="{{ old('contact_person') }}"></div>
<div class="col-sm-6"><label for="school-role">{{ __('public.contact_person_s_role_optional') }}</label><input id="school-role" name="contact_role" type="text" class="form-control"  maxlength="160"  value="{{ old('contact_role') }}"></div>
<div class="col-sm-6"><label for="school-email">{{ __('public.email') }}</label><input id="school-email" name="email" type="email" class="form-control" required maxlength="160" autocomplete="email" value="{{ old('email') }}"></div>
<div class="col-sm-6"><label for="school-phone">{{ __('public.phone_optional') }}</label><input id="school-phone" name="phone" type="tel" class="form-control"  maxlength="160" autocomplete="tel" value="{{ old('phone') }}"></div>
<div class="col-sm-6"><label for="school-model">{{ __('public.preferred_planting_model') }}</label><select id="school-model" name="desired_planting_model" class="form-select" required><option value="">{{ __('public.select_an_option') }}</option><option value="symbolic_generation_tree" @selected(old('desired_planting_model') === 'symbolic_generation_tree')>{{ __('public.one_symbolic_tree_for_the_graduating_class') }}</option><option value="one_tree_per_graduate" @selected(old('desired_planting_model') === 'one_tree_per_graduate')>{{ __('public.one_tree_for_every_graduate') }}</option><option value="undecided" @selected(old('desired_planting_model') === 'undecided')>{{ __('public.let_s_discuss_the_details') }}</option></select></div>
<div class="col-sm-6"><label for="school-students">{{ __('public.approximate_number_of_graduates_optional') }}</label><input id="school-students" name="estimated_graduate_count" type="number" class="form-control"  min="1" max="100000"  value="{{ old('estimated_graduate_count') }}"></div>
<div class="col-12"><label for="school-message">{{ __('public.message_additional_information_optional') }}</label><textarea id="school-message" name="message" class="form-control" rows="4" maxlength="3000">{{ old('message') }}</textarea></div>
<div class="col-12"><label class="ngo-consent" for="school-consent"><input id="school-consent" name="privacy_consent" value="1" type="checkbox" required @checked(old('privacy_consent'))> {{ __('public.i_consent_to_ecosync_processing_the_application_details_to') }}</label>
<p class="ngo-small">{{ __('public.required_fields_ecosync_uses_the_application_details_to_coordinate') }} <a href="mailto:eoecosync@gmail.com">eoecosync@gmail.com</a>.</p><button class="btn btn-primary ngo-btn" type="submit">{{ __('public.submit_application') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
<p class="ngo-form-status mt-3" role="status" tabindex="-1"></p></div></div></form></div></div></div></section>
@endsection
