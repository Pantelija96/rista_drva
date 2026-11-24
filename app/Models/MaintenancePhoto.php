<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenancePhoto extends Model
{
    protected $fillable = ['public_id', 'tree_maintenance_record_id', 'path', 'review_status', 'alt_text'];

    protected $hidden = ['path'];

    protected function casts(): array
    {
        return [];
    }

    public function record()
    {
        return $this->belongsTo(TreeMaintenanceRecord::class, 'tree_maintenance_record_id');
    }
}
