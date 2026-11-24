@extends('layouts.app')
@section('title', __('public.expired_title').' | EcoSync')
@section('description', __('public.expired_text'))
@section('noindex', 'true')
@section('content')<section class="ngo-section"><div class="container"><h1>419 · {{ __('public.expired_title') }}</h1><p>{{ __('public.expired_text') }}</p><a href="{{ route('home') }}">{{ __('public.home') }}</a></div></section>@endsection
