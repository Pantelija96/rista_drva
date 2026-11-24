<?php

namespace App\Http\Controllers;

use App\Models\GuardianAccessGrant;
use App\Models\TreeAdoption;
use App\Services\Adoptions;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GuardianController
{
    public function request(Request $request, Adoptions $service)
    {
        $data = $request->validate(['identifier' => 'required|string|max:50', 'email' => 'required|email|max:160', 'website' => 'prohibited']);
        $service->requestAccess($data['identifier'], $data['email']);

        return back()->with('notice', __('public.if_the_details_match_an_active_adoption_a_secure_link'));
    }

    public function accessForm(GuardianAccessGrant $grant)
    {
        return response()->view('workflows.token', ['heading' => __('public.guardian_access'), 'action' => route('guardian.access', $grant->public_id), 'message' => __('public.confirm_single_use_access_to_your_adoption')])->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function access(Request $request, GuardianAccessGrant $grant, Adoptions $service)
    {
        $data = $request->validate(['token' => 'required|string|size:64']);
        $adoption = $service->consumeAccess($grant, $data['token']);
        $request->session()->regenerate();
        $request->session()->put('guardian', ['id' => $adoption->id, 'version' => $adoption->access_version, 'expires_at' => now()->addMinutes(config('adoptions.access_minutes'))->timestamp]);

        return redirect()->route('guardian.dashboard');
    }

    public function dashboard(Request $request)
    {
        $adoption = $request->attributes->get('guardian_adoption')->load('tree.species');

        return view('workflows.guardian', ['adoption' => $adoption, 'records' => $adoption->maintenanceRecords()->with('photos')->latest()->get()]);
    }

    public function preferences(Request $request, Adoptions $service)
    {
        $data = $request->validate(['guardian_name' => 'required|string|max:160', 'guardian_phone' => 'nullable|string|max:50', 'public_display_preference' => ['required', Rule::in(array_keys(TreeAdoption::PREFERENCES))], 'adoption_type' => ['required', Rule::in(array_keys(TreeAdoption::TYPES))]]);
        $data['guardian_name'] = trim(strip_tags($data['guardian_name']));
        $service->updatePreferences($request->attributes->get('guardian_adoption'), $data);

        return back()->with('notice', __('public.your_settings_have_been_saved'));
    }

    public function emailRequest(Request $request, Adoptions $service)
    {
        $data = $request->validate(['email' => 'required|email|max:160']);
        $service->requestEmailChange($request->attributes->get('guardian_adoption'), $data['email']);

        return back()->with('notice', __('public.a_verification_message_has_been_queued_for_your_new_email'));
    }

    public function emailForm(GuardianAccessGrant $grant)
    {
        return response()->view('workflows.token', ['heading' => __('public.new_email_address_verification'), 'action' => route('guardian.email.confirm', $grant->public_id), 'message' => __('public.confirm_your_email_change_after_verification_request_a_new_access')])->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function emailConfirm(Request $request, GuardianAccessGrant $grant, Adoptions $service)
    {
        $data = $request->validate(['token' => 'required|string|size:64']);
        $service->confirmEmailChange($grant, $data['token']);
        $request->session()->forget('guardian');
        $request->session()->regenerate();

        return redirect()->route('guardian.request')->with('notice', __('public.your_new_email_address_is_verified_please_request_a_new'));
    }

    public function cancel(Request $request, Adoptions $service)
    {
        $request->validate(['confirm' => 'accepted']);
        $service->cancel($request->attributes->get('guardian_adoption'));
        $request->session()->forget('guardian');
        $request->session()->regenerate();

        return redirect()->route('guardian.request')->with('notice', __('public.your_adoption_has_been_cancelled_approved_maintenance_history_is_retained'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('guardian');
        $request->session()->regenerate();

        return redirect()->route('guardian.request');
    }
}
