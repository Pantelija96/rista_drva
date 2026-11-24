@extends('layouts.app')

@section('title', __('public.contact_ecosync_zelena_diploma'))
@section('description', __('public.contact_ecosync_eoecosync_gmail_com_0606955554'))

@section('content')
<section class="ngo-subhero ngo-green">
<div class="container container-xl-custom">
<div class="row g-4 align-items-center">
<div class="col-lg-8">
<p class="ngo-eyebrow">{{ __('public.a_conversation_is_the_first_step') }}</p>
<h1>{{ __('public.contact') }}</h1>
<p class="ngo-lead">{{ __('public.the_future_is_planted_today_2') }}</p></div>
<div class="col-lg-4 d-none d-lg-block"><img src="{{ asset('assets/images/gardening.jpg') }}" alt="{{ __('public.illustrative_nature_photo') }}" class="img-fluid ngo-subhero-image" loading="lazy" decoding="async"></div></div></div></section>
<section class="ngo-section ">
<div class="container container-xl-custom">
<div class="row g-5">
<div class="col-lg-4">
<p class="ngo-eyebrow">{{ __('public.we_re_here_for_your_idea') }}</p>
<h2 class="ngo-heading">{{ __('public.let_s_connect') }}</h2>
<p>{{ __('public.ecosync') }}</p>
<div class="ngo-contact-links"><a href="mailto:eoecosync@gmail.com">eoecosync@gmail.com</a><a href="tel:+381606955554">0606955554</a></div>
<p>{{ __('public.for_questions_about_schools_volunteering_planting_and_supporting_the') }}</p>
<p class="ngo-small">{{ __('public.send_us_an_inquiry_using_the_form_or_contact') }}</p></div>
<div class="col-lg-8">
@if (session('inquiry_success'))<div class="alert alert-success" role="status">{{ session('inquiry_success') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('inquiries.store','contact') }}">@csrf
<input type="hidden" name="submission_token" value="{{ old('submission_token',(string) Illuminate\Support\Str::uuid()) }}">
<div class="ngo-honeypot" aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div>
<div class="row g-3">
<div class="col-sm-6"><label for="contact-name">{{ __('public.full_name_2') }}</label><input id="contact-name" name="name" type="text" class="form-control" required maxlength="160"  value="{{ old('name') }}"></div>
<div class="col-sm-6"><label for="contact-email">{{ __('public.email') }}</label><input id="contact-email" name="email" type="email" class="form-control" required maxlength="160" autocomplete="email" value="{{ old('email') }}"></div>
<div class="col-sm-6"><label for="contact-phone">{{ __('public.phone_optional') }}</label><input id="contact-phone" name="phone" type="tel" class="form-control"  maxlength="160" autocomplete="tel" value="{{ old('phone') }}"></div>
<div class="col-sm-6"><label for="contact-topic">{{ __('public.message_topic') }}</label><select id="contact-topic" name="topic" class="form-select" required><option value="">{{ __('public.select_an_option') }}</option><option value="general" @selected(old('topic') === 'general')>{{ __('public.general_inquiry') }}</option><option value="school" @selected(old('topic') === 'school')>{{ __('public.school_cooperation') }}</option><option value="volunteering" @selected(old('topic') === 'volunteering')>{{ __('public.volunteering') }}</option><option value="tree_donation" @selected(old('topic') === 'tree_donation')>{{ __('public.sapling_donation') }}</option><option value="financial_support" @selected(old('topic') === 'financial_support')>{{ __('public.financial_support') }}</option><option value="partnership" @selected(old('topic') === 'partnership')>{{ __('public.partnership') }}</option><option value="other" @selected(old('topic') === 'other')>{{ __('public.other_2') }}</option></select></div>
<div class="col-12"><label for="contact-message">{{ __('public.message') }}</label><textarea id="contact-message" name="message" class="form-control" rows="4" maxlength="3000" required>{{ old('message') }}</textarea></div>
<div class="col-12"><label class="ngo-consent" for="contact-consent"><input id="contact-consent" name="privacy_consent" value="1" type="checkbox" required> {{ __('public.i_consent_to_ecosync_processing_my_details_to_review') }}</label>
<p class="ngo-small">{{ __('public.required_fields_your_inquiry_and_contact_details_are_available') }} <a href="mailto:eoecosync@gmail.com">eoecosync@gmail.com</a>.</p><button class="btn btn-primary ngo-btn" type="submit">{{ __('public.send_inquiry') }} <i class="fas fa-arrow-right" aria-hidden="true"></i></button>
<p class="ngo-form-status mt-3" role="status" tabindex="-1"></p></div></div></form></div></div></div></section>
@endsection
