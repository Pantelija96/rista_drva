<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TreeMaintenanceRecord extends Model
{
    use HasPublicTranslations;

    protected $fillable = ['translations', 'public_id', 'tree_id', 'tree_adoption_id', 'activity_type', 'description', 'public_description', 'performed_at', 'submitted_by_type', 'submitted_by_id', 'review_status', 'internal_notes', 'approved_by', 'approved_at', 'submission_key'];

    protected $hidden = ['description', 'internal_notes', 'submission_key'];

    protected function casts(): array
    {
        return ['translations' => 'array', 'performed_at' => 'date', 'approved_at' => 'datetime'];
    }

    public const ACTIVITIES = ['watering' => 'Zalivanje', 'cleaning' => 'Čišćenje', 'inspection' => 'Pregled', 'pruning' => 'Orezivanje', 'fertilizing' => 'Prihrana', 'issue_report' => 'Prijava problema', 'other' => 'Drugo'];

    public const REVIEWS = ['pending' => 'Na čekanju', 'approved' => 'Odobreno', 'rejected' => 'Odbijeno'];

    public function tree()
    {
        return $this->belongsTo(Tree::class);
    }

    public function adoption()
    {
        return $this->belongsTo(TreeAdoption::class, 'tree_adoption_id');
    }

    public function photos()
    {
        return $this->hasMany(MaintenancePhoto::class);
    }
}
