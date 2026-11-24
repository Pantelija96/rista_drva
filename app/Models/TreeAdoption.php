<?php

namespace App\Models;

use App\Services\Adoptions;
use Illuminate\Database\Eloquent\Model;

class TreeAdoption extends Model
{
    protected $fillable = ['preferred_locale', 'public_id', 'tree_id', 'reserved_tree_id', 'guardian_name', 'guardian_name_hash', 'guardian_email', 'guardian_email_hash', 'guardian_phone', 'adoption_type', 'public_display_preference', 'status', 'verification_token_hash', 'verification_version', 'verification_expires_at', 'reservation_expires_at', 'email_verified_at', 'approved_at', 'approved_by', 'rejected_at', 'rejection_reason', 'ended_at', 'consent_version', 'consented_at', 'access_version', 'internal_notes', 'submission_key'];

    protected $hidden = ['guardian_name', 'guardian_name_hash', 'guardian_email', 'guardian_phone', 'guardian_email_hash', 'verification_token_hash', 'submission_key', 'rejection_reason', 'internal_notes'];

    protected function casts(): array
    {
        return ['guardian_name' => 'encrypted', 'guardian_email' => 'encrypted', 'guardian_phone' => 'encrypted', 'verification_expires_at' => 'datetime', 'reservation_expires_at' => 'datetime', 'email_verified_at' => 'datetime', 'approved_at' => 'datetime', 'rejected_at' => 'datetime', 'ended_at' => 'datetime', 'consented_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $adoption) {
            if ($adoption->isDirty('guardian_name')) {
                $adoption->guardian_name_hash = hash_hmac('sha256', mb_strtolower(trim($adoption->guardian_name)), config('app.key'));
            }
            if ($adoption->isDirty('guardian_email') && $adoption->guardian_email) {
                $adoption->guardian_email_hash = Adoptions::emailHash($adoption->guardian_email);
            }
        });
    }

    public const TYPES = ['symbolic' => 'Simbolično usvajanje', 'active_guardianship' => 'Aktivna briga o stablu'];

    public const PREFERENCES = ['full_name' => 'Puno ime', 'initials' => 'Inicijali', 'anonymous' => 'Anonimno'];

    public const STATUSES = ['pending_verification' => 'Čeka email potvrdu', 'pending_approval' => 'Čeka odobrenje', 'active' => 'Aktivno', 'rejected' => 'Odbijeno', 'expired' => 'Isteklo', 'cancelled' => 'Otkazano', 'ended' => 'Završeno'];

    public function tree()
    {
        return $this->belongsTo(Tree::class);
    }

    public function events()
    {
        return $this->hasMany(TreeAdoptionEvent::class);
    }

    public function maintenanceRecords()
    {
        return $this->hasMany(TreeMaintenanceRecord::class);
    }

    public function displayName(): string
    {
        if ($this->public_display_preference === 'anonymous') {
            return __('public.anonymous_guardian');
        }
        if ($this->public_display_preference === 'full_name') {
            return $this->guardian_name;
        }

        return collect(preg_split('/\\s+/u', trim($this->guardian_name)))->filter()->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)).'.')->implode(' ');
    }

    public function isLiveReservation(): bool
    {
        return $this->status === 'active' || (in_array($this->status, ['pending_verification', 'pending_approval'], true) && $this->reservation_expires_at?->isFuture());
    }
}
