@extends('workflows.shell')
@section('workflow')
<p>{{ $message }}</p>
<form method="POST" action="{{ $action }}" data-secure-token>
@csrf
<label for="secure-token">{{ __('public.code_from_your_email_link') }}</label>
<input id="secure-token" name="token" type="password" class="form-control" autocomplete="off" required minlength="64" maxlength="64">
<p class="ngo-small">{{ __('public.the_code_is_read_from_the_email_link_and') }}</p>
<button class="btn btn-primary ngo-btn mt-3" type="submit">{{ __('public.confirm') }}</button>
</form>
@endsection
