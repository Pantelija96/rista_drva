<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreeAdoptionEvent extends Model
{
    protected $fillable = ['tree_adoption_id', 'event', 'from_status', 'to_status', 'actor_type', 'actor_id', 'metadata'];

    protected $hidden = [];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function adoption()
    {
        return $this->belongsTo(TreeAdoption::class, 'tree_adoption_id');
    }
}
