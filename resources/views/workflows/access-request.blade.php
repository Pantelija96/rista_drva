@extends('workflows.shell', ['heading'=>__('public.guardian_access')])
@section('workflow')
<p>{{ __('public.enter_your_tree_identifier_and_the_email_you_verified') }}</p>
<form method="POST" action="{{ route('guardian.request.store') }}">@csrf
<label>{{ __('public.tree_identifier') }}<input class="form-control" name="identifier" required maxlength="50"></label>
<label>{{ __('public.email_2') }}<input class="form-control" name="email" type="email" required maxlength="160"></label>
<div class="ngo-honeypot" aria-hidden="true"><input name="website" tabindex="-1" autocomplete="off"></div>
<button type="submit" class="btn btn-primary ngo-btn">{{ __('public.request_a_secure_link') }}</button></form>
@endsection
