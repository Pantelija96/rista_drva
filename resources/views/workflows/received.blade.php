@extends('workflows.shell', ['heading'=>__('public.adoption_request')])
@section('workflow')
<x-workflow-progress />
<p>{{ __('public.adoption_requires_email_verification_and_administrator_approval_use_the') }}</p>
<h2>{{ __('public.resend_verification') }}</h2>
<form method="POST" action="{{ route('adoptions.resend') }}">@csrf
<label>{{ __('public.tree_identifier') }}<input class="form-control" name="identifier" required maxlength="50"></label>
<label>{{ __('public.email_2') }}<input class="form-control" name="email" type="email" required maxlength="160"></label>
<button type="submit" class="btn btn-primary ngo-btn">{{ __('public.request_new_verification') }}</button></form>
<p>{{ __('public.the_temporary_reservation_expires_without_verification_for_help_email_eoecosync') }}</p>
@endsection
