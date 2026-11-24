<?php

namespace App\Http\Controllers;

use App\Http\Requests\InquiryRequest;
use App\Models\Inquiry;
use App\Services\Outbox;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class InquiryController
{
    public function store(InquiryRequest $request, string $kind)
    {
        $data = $request->safe()->except(['privacy_consent', 'website', 'submission_token']);
        try {
            DB::transaction(function () use ($request, $kind, $data) {
                $key = hash('sha256', $request->session()->getId().'|'.$request->validated('submission_token'));
                $fingerprint = hash_hmac('sha256', json_encode([$kind, mb_strtolower($data['email']), $data['topic'], $data['message'], now()->format('Y-m-d')]), config('app.key'));
                $inquiry = Inquiry::where('submission_key', $key)->first();
                $inquiry ??= Inquiry::firstOrCreate(['fingerprint' => $fingerprint], ['preferred_locale' => app()->getLocale(), 'public_id' => (string) Str::uuid(), ...$data, 'kind' => $kind, 'submission_key' => $key, 'consent_version' => config('adoptions.consent_version'), 'consented_at' => now()]);
                if ($inquiry->wasRecentlyCreated) {
                    app(Outbox::class)->add('inquiry.admin', $inquiry->id, 'inquiry.admin:'.$inquiry->id);
                }
            });
        } catch (QueryException $e) {
            Log::error('Inquiry persistence failed.', ['code' => $e->getCode()]);

            return back()->withInput()->withErrors(['submission' => __('public.your_inquiry_could_not_be_saved_please_try_again_or')]);
        }

        return back()->with('inquiry_success', __('public.thank_you_your_inquiry_has_been_saved_ecosync_will_review'));
    }
}
