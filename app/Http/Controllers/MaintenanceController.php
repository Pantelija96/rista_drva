<?php

namespace App\Http\Controllers;

use App\Models\MaintenancePhoto;
use App\Services\Maintenance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaintenanceController
{
    public function store(Request $request, Maintenance $service)
    {
        $data = $request->validate(['activity_type' => 'required|string', 'description' => 'required|string|max:3000', 'performed_at' => 'required|date', 'submission_token' => 'required|uuid', 'photos' => 'nullable|array|max:5', 'photos.*' => Maintenance::photoRules()]);
        $adoption = $request->attributes->get('guardian_adoption');
        $service->create($adoption->tree, $data, $request->file('photos', []), guardian: $adoption, submissionKey: hash('sha256', $adoption->id.'|'.$data['submission_token']));

        return back()->with('notice', __('public.your_report_has_been_saved_and_is_awaiting_review_photos'));
    }

    public function photo(Request $request, MaintenancePhoto $photo)
    {
        $record = $photo->record;
        $public = $record->review_status === 'approved' && $photo->review_status === 'approved' && $record->tree()->published()->exists();
        $guardian = $request->session()->get('guardian');
        $owner = $guardian && ($guardian['expires_at'] ?? 0) > now()->timestamp && $record->tree_adoption_id === ($guardian['id'] ?? null) && $record->adoption?->status === 'active' && $record->adoption?->access_version === ($guardian['version'] ?? null);
        abort_unless($public || $request->user()?->is_admin || $owner, 404);
        abort_unless(Storage::disk('local')->exists($photo->path), 404);

        return Storage::disk('local')->response($photo->path, null, ['Content-Type' => 'image/jpeg', 'X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'no-store, private']);
    }
}
