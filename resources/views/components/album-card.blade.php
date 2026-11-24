@props(['album', 'index' => null])
<div class="col-md-6 col-lg-4" data-album-category="{{ $album->category }}">
<article class="ngo-album">
<a href="{{ route('albums.show',$album->slug) }}" class="ngo-album-open" aria-label="{{ __('public.open_album') }} {{ $album->localized('title') }}">
<span class="thumb-info thumb-info-no-borders"><span class="thumb-info-wrapper">
@if ($album->cover_image_path)
<img src="{{ Storage::disk('public')->url($album->cover_image_path) }}" alt="{{ $album->localized('title') }}" class="img-fluid" loading="lazy">
@elseif ($album->images->first())
<img src="{{ Storage::disk('public')->url($album->images->first()->image_path) }}" alt="{{ $album->images->first()->localized('alt_text') }}" class="img-fluid" loading="lazy">
@endif
<span class="ngo-photo-open"><i class="fas fa-expand" aria-hidden="true"></i></span></span></span></a>
<div class="ngo-album-copy"><span class="ngo-eyebrow">{{ $album->images->count() }} {{ __('public.photo_count') }}</span>
<h3><a href="{{ route('albums.show',$album->slug) }}">{{ $album->localized('title') }}</a></h3><p>{{ $album->localized('location_label') }} @if ($album->event_date)<br>{{ $album->event_date->format('d.m.Y.') }}@endif</p></div>
</article></div>
