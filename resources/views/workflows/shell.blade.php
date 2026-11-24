@extends('layouts.app')
@section('title', $heading.' | EcoSync')
@section('description', __('public.ecosync_zelena_diploma_3'))
@section('content')
<section class="ngo-subhero ngo-green"><div class="container container-xl-custom"><p class="ngo-eyebrow">{{ __('public.zelena_diploma') }}</p><h1>{{ $heading }}</h1></div></section>
<section class="ngo-section"><div class="container container-xl-custom">
@if (session('notice'))<div class="alert alert-success" role="status">{{ session('notice') }}</div>@endif
@if ($errors->any())<div class="alert alert-danger" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@yield('workflow')
</div></section>
@endsection
