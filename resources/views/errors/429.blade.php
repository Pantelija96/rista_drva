@extends('layouts.app')
@section('title', __('public.rate_title').' | EcoSync')
@section('description', __('public.rate_text'))
@section('noindex', 'true')
@section('content')<section class="ngo-section"><div class="container"><h1>429 · {{ __('public.rate_title') }}</h1><p>{{ __('public.rate_text') }}</p><a href="{{ route('home') }}">{{ __('public.home') }}</a></div></section>@endsection
