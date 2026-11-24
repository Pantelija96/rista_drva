@props(['tree'])
<x-workflow-progress /><p>{{ __('flows.types') }}</p><p>{{ __('flows.expiry', ['minutes'=>config('adoptions.verification_minutes')]) }}</p>
<form method="POST" action="{{ route('adoptions.store',$tree->public_identifier) }}" class="ngo-adoption-form">@csrf
<input type="hidden" name="submission_token" value="{{ old('submission_token',(string) Illuminate\Support\Str::uuid()) }}">
<div class="ngo-honeypot" aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div>
<div class="row g-3">
<div class="col-sm-6"><label for="adopt-name">{{ __('public.full_name_2') }}</label><input id="adopt-name" name="guardian_name" type="text" class="form-control" required maxlength="160" value="{{ old('guardian_name') }}"></div>
<div class="col-sm-6"><label for="adopt-email">{{ __('public.email') }}</label><input id="adopt-email" name="guardian_email" type="email" class="form-control" required maxlength="160" value="{{ old('guardian_email') }}"></div>
<div class="col-sm-6"><label for="adopt-phone">{{ __('public.phone_optional') }}</label><input id="adopt-phone" name="guardian_phone" type="tel" class="form-control" maxlength="50" value="{{ old('guardian_phone') }}"></div>
<div class="col-sm-6"><label for="adopt-type">{{ __('public.adoption_type_2') }}</label><select id="adopt-type" name="adoption_type" class="form-select" required>@foreach (App\Models\TreeAdoption::TYPES as $value=>$label)<option value="{{ $value }}" @selected(old('adoption_type')===$value)>{{ App\Support\PublicText::label($label) }}</option>@endforeach</select></div>
<div class="col-sm-6"><label for="adopt-public">{{ __('public.public_name_display') }}</label><select id="adopt-public" name="public_display_preference" class="form-select" required>@foreach (App\Models\TreeAdoption::PREFERENCES as $value=>$label)<option value="{{ $value }}" @selected(old('public_display_preference','anonymous')===$value)>{{ App\Support\PublicText::label($label) }}</option>@endforeach</select></div>
<div class="col-12"><label class="ngo-consent"><input name="privacy_consent" value="1" type="checkbox" required> {{ __('public.i_consent_to_ecosync_processing_my_contact_details_for') }}</label>
<p class="ngo-small">{{ __('flows.expiry', ['minutes'=>config('adoptions.reservation_minutes')]) }}</p>
<button class="btn btn-primary ngo-btn" type="submit">{{ __('public.submit_adoption_request') }}</button></div></div>
</form>
