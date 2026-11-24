<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    protected $fillable = ['preferred_locale', 'public_id', 'kind', 'name', 'email', 'phone', 'organization', 'topic', 'message', 'status', 'internal_notes', 'submission_key', 'fingerprint', 'consent_version', 'consented_at'];

    protected $hidden = ['name', 'email', 'phone', 'message', 'internal_notes', 'submission_key', 'fingerprint'];

    protected function casts(): array
    {
        return ['email' => 'encrypted', 'phone' => 'encrypted', 'consented_at' => 'datetime'];
    }
}
