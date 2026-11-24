<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuardianAccessGrant extends Model
{
    protected $fillable = ['public_id', 'tree_adoption_id', 'token_hash', 'expires_at', 'consumed_at', 'access_version', 'purpose', 'new_email'];

    protected $hidden = ['token_hash', 'new_email'];

    protected function casts(): array
    {
        return ['new_email' => 'encrypted', 'expires_at' => 'datetime', 'consumed_at' => 'datetime'];
    }

    public function adoption()
    {
        return $this->belongsTo(TreeAdoption::class, 'tree_adoption_id');
    }
}
