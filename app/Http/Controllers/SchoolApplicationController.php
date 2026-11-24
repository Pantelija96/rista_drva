<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolApplicationRequest;
use App\Models\SchoolApplication;
use App\Services\SchoolApplicationNotifier;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SchoolApplicationController
{
    public function store(SchoolApplicationRequest $request, SchoolApplicationNotifier $notifier)
    {
        $data = $request->safe()->except(['website', 'submission_token']);
        $key = hash('sha256', $request->session()->getId().'|'.$request->validated('submission_token'));
        try {
            DB::transaction(function () use ($key, $data, $notifier) {
                // A database unique key handles concurrent retries of the same browser form.
                $fingerprint = hash('sha256', mb_strtolower($data['school_name'].'|'.$data['city'].'|'.$data['email']));
                $application = SchoolApplication::where('submission_key', $key)->first();
                $application ??= SchoolApplication::firstOrCreate(['active_fingerprint' => $fingerprint], [
                    'preferred_locale' => app()->getLocale(), 'submission_key' => $key,
                    ...$data, 'privacy_consent' => true, 'consent_timestamp' => now(), 'status' => 'pending',
                ]);
                if ($application->wasRecentlyCreated) {
                    $notifier->send($application);
                }
            });
        } catch (QueryException $e) {
            Log::error('School application persistence failed.', ['code' => $e->getCode()]);

            return back()->withInput()->withErrors(['submission' => __('public.your_application_could_not_be_saved_please_try_again_or')]);
        }

        return redirect()->to(route('join').'#prijava-skole')->with('school_application_success', __('public.thank_you_your_application_has_been_received_ecosync_will_contact'));
    }
}
