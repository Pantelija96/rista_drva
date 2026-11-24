@extends('layouts.app')
@section('title', $album->localized('title'). ' | EcoSync')
@section('description', __('public.zelena_diploma_the_future_is_planted_today'))
@section('content')
<section class="ngo-subhero ngo-green"><div class="container container-xl-custom"><p class="ngo-eyebrow">{{ __('public.zelena_diploma') }}</p><h1>{{ $album->localized('title') }}</h1></div></section>
<section class="ngo-section"><div class="container container-xl-custom">
<p>{{ $album->localized('description') }}</p><p>{{ $album->localized('location_label') }} {{ $album->event_date?->format('d.m.Y.') }}</p>
<div class="row g-4">@forelse ($album->images as $image)<div class="col-md-6 col-lg-4"><figure class="ngo-album"><a data-gallery-image href="{{ Storage::disk('public')->url($image->image_path) }}" title="{{ $image->localized('caption') }}"><img class="img-fluid" src="{{ Storage::disk('public')->url($image->image_path) }}" alt="{{ $image->localized('alt_text') }}" loading="lazy"></a>@if ($image->localized('caption'))<figcaption>{{ $image->localized('caption') }}</figcaption>@endif</figure></div>@empty<p>{{ __('public.photos_will_be_added_soon') }}</p>@endforelse</div>
</div></section>
@endsection
