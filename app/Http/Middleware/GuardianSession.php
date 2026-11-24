<?php

namespace App\Http\Middleware;

use App\Models\TreeAdoption;
use Closure;

class GuardianSession
{
    public function handle($request, Closure $next)
    {
        $id = $request->session()->get('guardian.id');
        $expires = $request->session()->get('guardian.expires_at', 0);
        $adoption = $id && $expires > now()->timestamp ? TreeAdoption::find($id) : null;
        if (! $adoption || $adoption->status !== 'active' || $adoption->access_version !== $request->session()->get('guardian.version')) {
            $request->session()->forget('guardian');

            return redirect()->route('guardian.request')->with('notice', __('public.your_access_has_expired_request_a_new_secure_email_link'));
        }
        $request->attributes->set('guardian_adoption', $adoption);

        return $next($request)->header('Cache-Control', 'no-store, private')->header('Referrer-Policy', 'no-referrer');
    }
}
