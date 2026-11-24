@extends('layouts.app')
@section('title', __('public.server_title').' | EcoSync')
@section('description', __('public.server_text'))
@section('noindex', 'true')
@section('content')<section class="ngo-section"><div class="container"><h1>500 · {{ __('public.server_title') }}</h1><p>{{ __('public.server_text') }}</p><a href="{{ route('home') }}">{{ __('public.home') }}</a></div></section>@endsection
