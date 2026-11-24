<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends AdminPolicy
{
    public function delete(User $user, Model $record): bool
    {
        return $user->is_admin && $user->id !== $record->id;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
