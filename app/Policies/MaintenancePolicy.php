<?php

namespace App\Policies;

use App\Models\User;

class MaintenancePolicy extends WorkflowPolicy
{
    public function create(User $user): bool
    {
        return $user->is_admin === true;
    }
}
