<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AdminPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function view(User $user, Model $record): bool
    {
        return $user->is_admin === true;
    }

    public function create(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function update(User $user, Model $record): bool
    {
        return $user->is_admin === true;
    }

    public function delete(User $user, Model $record): bool
    {
        return $user->is_admin === true;
    }

    public function deleteAny(User $user): bool
    {
        return $user->is_admin === true;
    }

    public function reorder(User $user): bool
    {
        return $user->is_admin === true;
    }
}
