<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'is_admin' => 'boolean', 'email_verified_at' => 'datetime'];
    }

    public function save(array $options = [])
    {
        return DB::transaction(function () use ($options) {
            if ($this->exists && $this->isDirty('is_admin') && ! $this->is_admin) {
                $admins = static::where('is_admin', true)->lockForUpdate()->get();
                if ($admins->count() <= 1 && $admins->contains('id', $this->id)) {
                    throw ValidationException::withMessages(['is_admin' => 'Poslednjem administratoru nije moguće ukinuti pristup.']);
                }
            }

            return parent::save($options);
        });
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_admin === true;
    }
}
