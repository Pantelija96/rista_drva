@extends('layouts.app')
@section('title', __('public.not_found_title').' | EcoSync')
@section('description', __('public.not_found_text'))
@section('noindex', 'true')
@section('content')<section class="ngo-section"><div class="container"><h1>404 · {{ __('public.not_found_title') }}</h1><p>{{ __('public.not_found_text') }}</p><a href="{{ route('home') }}">{{ __('public.home') }}</a></div></section>@endsection
