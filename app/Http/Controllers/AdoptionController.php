<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdoptionRequest;
use App\Models\Tree;
use App\Models\TreeAdoption;
use App\Services\Adoptions;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdoptionController
{
    public function store(AdoptionRequest $request, string $identifier, Adoptions $service)
    {
        $tree = Tree::published()->where('public_identifier', $identifier)->firstOrFail();
        $data = $request->safe()->except(['privacy_consent', 'website', 'submission_token']);
        try {
            $adoption = $service->request($tree, $data, hash('sha256', $request->session()->getId().'|'.$request->validated('submission_token')));
        } catch (QueryException $exception) {
            Log::error('Adoption persistence failed.', ['code' => $exception->getCode()]);

            return back()->withInput()->withErrors(['adoption' => __('public.your_request_could_not_be_saved_please_try_again_or')]);
        }

        return redirect()->route('adoptions.received')->with('notice', __('public.your_request_has_been_saved_email_verification_is_required_before'));
    }

    public function verifyForm(TreeAdoption $adoption)
    {
        return response()->view('workflows.token', ['heading' => __('public.email_verification'), 'action' => route('adoptions.verify', $adoption->public_id), 'message' => __('public.verify_your_email_address_an_administrator_will_then_review_the')])->header('Referrer-Policy', 'no-referrer')->header('Cache-Control', 'no-store');
    }

    public function verify(Request $request, TreeAdoption $adoption, Adoptions $service)
    {
        $data = $request->validate(['token' => 'required|string|size:64']);
        $service->verify($adoption, $data['token']);

        return redirect()->route('adoptions.received')->with('notice', __('public.your_email_is_verified_your_request_is_awaiting_administrator_approval'));
    }

    public function resend(Request $request, Adoptions $service)
    {
        $data = $request->validate(['identifier' => 'required|string|max:50', 'email' => 'required|email|max:160', 'website' => 'prohibited']);
        $tree = Tree::where('public_identifier', $data['identifier'])->first();
        if ($tree) {
            $adoption = TreeAdoption::where('tree_id', $tree->id)->where('guardian_email_hash', Adoptions::emailHash($data['email']))->where('status', 'pending_verification')->first();
            if ($adoption) {
                $service->resend($adoption, $data['email']);
            }
        }

        return back()->with('notice', __('public.if_a_matching_valid_request_exists_a_new_message_has'));
    }
}
