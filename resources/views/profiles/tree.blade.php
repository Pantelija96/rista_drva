@extends('layouts.app')
@section('title', ($tree->localized('title') ?: $tree->species->localized('common_name')).' · '.$tree->public_identifier. ' | EcoSync')
@section('description', __('public.zelena_diploma_the_future_is_planted_today'))
@section('content')
<section class="ngo-subhero ngo-green"><div class="container container-xl-custom"><p class="ngo-eyebrow">{{ __('public.zelena_diploma') }}</p><h1>{{ ($tree->localized('title') ?: $tree->species->localized('common_name')).' · '.$tree->public_identifier }}</h1></div></section>
<section class="ngo-section"><nav class="container" aria-label="{{ __('public.breadcrumbs') }}"><a href="{{ route('green-trace') }}">{{ __('flows.back') }}</a></nav><div class="container container-xl-custom">
@if ($tree->featured_image_path)<img class="img-fluid ngo-profile-image" src="{{ Storage::disk('public')->url($tree->featured_image_path) }}" alt="{{ $tree->public_identifier }}">@endif
<p>{{ $tree->localized('description') }}</p><p>{{ __('public.species') }} <a href="{{ route('species.show',$tree->species->slug) }}">{{ $tree->species->localized('common_name') }}</a> {{ $tree->species->scientific_name }}</p>
<p>{{ $tree->species->localized('short_description') }}</p><p>{{ $tree->species->localized('maintenance_guidelines') }}</p>
<p>{{ __('public.planting_date') }} {{ $tree->planted_at?->format('d.m.Y.') ?? __('public.not_confirmed') }}</p>
<p>{{ App\Support\PublicText::label(App\Support\DomainRules::CONDITION[$tree->physical_condition]) }} · {{ App\Support\PublicText::label(App\Support\DomainRules::ADOPTION[$tree->effectiveAdoptionStatus()]) }}</p>
@if ($tree->school)<p>{{ __('public.school_2') }} <a href="{{ route('schools.show',$tree->school->slug) }}">{{ $tree->school->name }}</a></p>@endif
@if ($tree->generation)<p><a href="{{ route('generations.show',[$tree->school->slug,$tree->generation]) }}">{{ __('public.graduating_class_2') }}{{ $tree->generation->graduation_year }}</a></p>@endif
@if ($tree->group)<p><a href="{{ route('groups.show',$tree->group->slug) }}">{{ $tree->group->localized('name') }}</a></p>@endif
<div class="row g-4">@foreach ($tree->photo_paths ?? [] as $path)<div class="col-md-4"><a data-gallery-image href="{{ Storage::disk('public')->url($path) }}"><img class="img-fluid" src="{{ Storage::disk('public')->url($path) }}" alt="{{ $tree->public_identifier }}" loading="lazy"></a></div>@endforeach</div>
@if ($errors->any())<div class="alert alert-danger" role="alert"><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
@if ($tree->reservedAdoption?->status === 'active')<p>{{ __('public.guardian') }} {{ $tree->reservedAdoption->displayName() }}</p>@endif
@if ($tree->effectiveAdoptionStatus() === 'available')
<button class="btn btn-primary ngo-btn" type="button" data-bs-toggle="modal" data-bs-target="#adopt-modal">{{ __('public.adopt_a_tree') }}</button>
<div class="modal fade" id="adopt-modal" tabindex="-1" aria-labelledby="adopt-title" aria-hidden="true"><div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h2 id="adopt-title" class="modal-title fs-4">{{ __('public.adopt_a_tree_2') }} {{ $tree->public_identifier }}</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('public.close') }}"></button></div><div class="modal-body"><x-adoption-form :tree="$tree" /></div></div></div></div>
@endif
<p><a href="{{ route('guardian.request') }}">{{ __('public.guardian_access') }}</a></p>
<h2>{{ __('public.approved_maintenance_history') }}</h2>
@forelse ($maintenance as $record)<article class="ngo-participation"><h3>{{ App\Support\PublicText::label(App\Models\TreeMaintenanceRecord::ACTIVITIES[$record->activity_type]) }}</h3><p>{{ $record->performed_at->format('d.m.Y.') }}</p><p>{{ $record->localized('public_description') }}</p><div class="row g-3">@foreach ($record->photos as $photo)<div class="col-md-4"><a data-gallery-image href="{{ route('maintenance.photo',$photo->public_id) }}"><img class="img-fluid" src="{{ route('maintenance.photo',$photo->public_id) }}" alt="{{ $photo->alt_text }}" loading="lazy"></a></div>@endforeach</div></article>@empty<p>{{ __('public.approved_maintenance_reports_will_appear_after_review') }}</p>@endforelse
<details class="ngo-tree-qr"><summary>{{ __('certificates.qr') }}</summary><p>{{ __('certificates.qr_help') }}</p><img src="{{ route('trees.qr',$tree->public_identifier) }}" alt="{{ __('certificates.qr') }} · {{ $tree->public_identifier }}" width="150" height="150" loading="lazy"><p><a href="{{ route('trees.qr',['identifier'=>$tree->public_identifier,'download'=>1]) }}">{{ __('certificates.qr_download') }}</a></p></details>
<x-leaflet-map id="tree-map" :markers="$markers" />
</div></section>
@endsection
