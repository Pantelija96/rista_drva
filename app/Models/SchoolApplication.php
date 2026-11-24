<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolApplication extends ContentModel
{
    protected $fillable = ['preferred_locale', 'school_name', 'city', 'municipality', 'contact_person', 'contact_role', 'email', 'phone', 'desired_planting_model', 'estimated_graduate_count', 'message', 'privacy_consent', 'consent_timestamp', 'submission_key', 'active_fingerprint', 'status', 'internal_notes', 'school_id', 'notification_sent_at'];

    protected $hidden = ['contact_person', 'contact_role', 'email', 'phone', 'message', 'internal_notes', 'submission_key', 'active_fingerprint'];

    protected function casts(): array
    {
        return ['privacy_consent' => 'boolean', 'consent_timestamp' => 'datetime', 'notification_sent_at' => 'datetime'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
