<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailOutbox extends Model
{
    protected $fillable = ['locale', 'event', 'reference', 'dedup_key', 'payload', 'status', 'attempts', 'available_at', 'claim', 'locked_until', 'processed_at', 'transport', 'last_error'];

    protected $hidden = ['payload', 'claim'];

    protected function casts(): array
    {
        return ['payload' => 'encrypted:array', 'available_at' => 'datetime', 'locked_until' => 'datetime', 'processed_at' => 'datetime'];
    }

    protected $table = 'mail_outbox';
}
