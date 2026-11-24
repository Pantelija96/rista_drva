<?php

namespace App\Services;

use App\Models\GuardianAccessGrant;
use App\Models\Inquiry;
use App\Models\MailOutbox;
use App\Models\SchoolApplication;
use App\Models\Tree;
use App\Models\TreeAdoption;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PrivacyRedaction
{
    public function redact($record, User $admin): void
    {
        abort_unless($admin->is_admin, 403);
        DB::transaction(function () use ($record, $admin) {
            if ($record instanceof TreeAdoption) {
                Tree::whereKey($record->tree_id)->lockForUpdate()->firstOrFail();
                $record = TreeAdoption::whereKey($record->id)->lockForUpdate()->firstOrFail();
                if (in_array($record->status, ['active', 'pending_verification', 'pending_approval'], true)) {
                    throw ValidationException::withMessages(['status' => 'Najpre završite ili odbijte usvajanje.']);
                }
                $record->update(['guardian_name' => 'Podaci uklonjeni', 'guardian_email' => null, 'guardian_email_hash' => hash('sha256', random_bytes(32)), 'guardian_phone' => null, 'public_display_preference' => 'anonymous', 'verification_token_hash' => null, 'internal_notes' => null, 'rejection_reason' => null, 'access_version' => $record->access_version + 1]);
                GuardianAccessGrant::where('tree_adoption_id', $record->id)->update(['token_hash' => null]);
                $record->maintenanceRecords()->update(['description' => 'Privatni tekst je uklonjen.', 'internal_notes' => null]);
                app(Adoptions::class)->event($record, 'personal_data_removed', $record->status, 'admin', $admin->id);
                MailOutbox::where('reference', (string) $record->id)->where('event', 'like', 'adoption.%')->whereIn('status', ['pending', 'failed'])->update(['status' => 'cancelled', 'payload' => null]);
            } elseif ($record instanceof Inquiry) {
                $record = Inquiry::whereKey($record->id)->lockForUpdate()->firstOrFail();
                $record->update(['name' => 'Podaci uklonjeni', 'email' => null, 'phone' => null, 'organization' => null, 'message' => 'Lični podaci su uklonjeni.', 'internal_notes' => null, 'status' => 'closed']);
                MailOutbox::where('reference', (string) $record->id)->where('event', 'inquiry.admin')->whereIn('status', ['pending', 'failed'])->update(['status' => 'cancelled', 'payload' => null]);
            } elseif ($record instanceof SchoolApplication) {
                $record = SchoolApplication::whereKey($record->id)->lockForUpdate()->firstOrFail();
                $record->update(['contact_person' => 'Podaci uklonjeni', 'email' => 'removed-'.$record->id.'@example.invalid', 'phone' => null, 'message' => null, 'contact_role' => null, 'internal_notes' => null]);
                MailOutbox::where('reference', (string) $record->id)->where('event', 'like', 'school.%')->whereIn('status', ['pending', 'failed'])->update(['status' => 'cancelled', 'payload' => null]);
            }
        });
    }
}
