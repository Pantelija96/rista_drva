@extends('workflows.shell', ['heading'=>__('public.my_adoption')])
@section('workflow')
@if(app(App\Services\AdoptionCertificates::class)->eligible($adoption))
<p><a class="btn btn-primary ngo-btn" href="{{ route('certificates.download',$adoption->public_id) }}">{{ __('certificates.download') }}</a></p>
<p class="ngo-small">{{ __('certificates.current_status') }} {{ __('certificates.disclaimer') }}</p>
@endif
<p>{{ $adoption->tree->public_identifier }} · {{ $adoption->tree->species->localized('common_name') }}</p>
<form method="POST" action="{{ route('guardian.logout') }}">@csrf<button class="ngo-text-link">{{ __('public.end_secure_session') }}</button></form>
<h2>{{ __('public.new_maintenance_report') }}</h2>
<form method="POST" action="{{ route('guardian.maintenance') }}" enctype="multipart/form-data">@csrf
<input type="hidden" name="submission_token" value="{{ (string) Illuminate\Support\Str::uuid() }}">
<label>{{ __('public.activity_type') }}<select name="activity_type" class="form-select" required>@foreach (App\Models\TreeMaintenanceRecord::ACTIVITIES as $value=>$label)<option value="{{ $value }}">{{ App\Support\PublicText::label($label) }}</option>@endforeach</select></label>
<label>{{ __('public.date') }}<input name="performed_at" type="date" class="form-control" required max="{{ now()->format('Y-m-d') }}"></label>
<label>{{ __('public.description') }}<textarea name="description" class="form-control" required maxlength="3000" rows="4"></textarea></label>
<label>{{ __('public.photos_up_to_5_jpg_png_webp_up_to') }}<input name="photos[]" type="file" class="form-control" accept="image/jpeg,image/png,image/webp" multiple></label>
<p class="ngo-small">{{ __('public.reports_and_photos_remain_private_until_reviewed_do_not') }}</p>
<button class="btn btn-primary ngo-btn" type="submit">{{ __('public.submit_report_for_review') }}</button></form>
<h2>{{ __('public.my_reports') }}</h2>
@forelse ($records as $record)<article class="ngo-participation"><h3>{{ App\Support\PublicText::label(App\Models\TreeMaintenanceRecord::ACTIVITIES[$record->activity_type]) }}</h3><p>{{ $record->performed_at->format('d.m.Y.') }} · {{ App\Support\PublicText::label(App\Models\TreeMaintenanceRecord::REVIEWS[$record->review_status]) }}</p><p>{{ $record->description }}</p><div class="row g-3">@foreach ($record->photos as $photo)@if ($photo->review_status !== 'rejected')<div class="col-md-4"><img class="img-fluid" src="{{ route('maintenance.photo',$photo->public_id) }}" alt="{{ $photo->alt_text }}"></div>@endif @endforeach</div></article>@empty<p>{{ __('public.no_reports_submitted_yet') }}</p>@endforelse
<h2>{{ __('public.public_display_and_contact_settings') }}</h2>
<form method="POST" action="{{ route('guardian.preferences') }}">@csrf
<label>{{ __('public.full_name') }}<input name="guardian_name" class="form-control" value="{{ $adoption->guardian_name }}" required maxlength="160"></label>
<label>{{ __('public.phone_optional') }}<input name="guardian_phone" class="form-control" value="{{ $adoption->guardian_phone }}" maxlength="50"></label>
<label>{{ __('public.public_display') }}<select name="public_display_preference" class="form-select">@foreach (App\Models\TreeAdoption::PREFERENCES as $value=>$label)<option value="{{ $value }}" @selected($adoption->public_display_preference===$value)>{{ App\Support\PublicText::label($label) }}</option>@endforeach</select></label>
<label>{{ __('public.adoption_type') }}<select name="adoption_type" class="form-select">@foreach (App\Models\TreeAdoption::TYPES as $value=>$label)<option value="{{ $value }}" @selected($adoption->adoption_type===$value)>{{ App\Support\PublicText::label($label) }}</option>@endforeach</select></label>
<p>{{ __('public.settings_can_be_changed_through_this_time_limited_email') }}</p>
<button type="submit" class="btn btn-primary ngo-btn">{{ __('public.save_settings') }}</button></form>
<h2>{{ __('public.change_email_address') }}</h2><form method="POST" action="{{ route('guardian.email.request') }}">@csrf<label>{{ __('public.new_email_address') }}<input type="email" name="email" class="form-control" required maxlength="160"></label><button class="btn btn-primary ngo-btn" type="submit">{{ __('public.request_verification_of_new_address') }}</button></form>
<h2>{{ __('public.cancel_adoption') }}</h2><form method="POST" action="{{ route('guardian.cancel') }}">@csrf<label><input type="checkbox" name="confirm" value="1" required> {{ __('public.i_want_to_end_my_adoption_and_release_the') }}</label><button class="btn btn-primary ngo-btn" type="submit">{{ __('public.cancel_adoption_2') }}</button></form>
@endsection
