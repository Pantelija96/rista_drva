<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'ecosync:create-admin';

    protected $description = 'Bezbedno napravi novog EcoSync administratora';

    public function handle(): int
    {
        $data = ['name' => $this->ask('Ime'), 'email' => strtolower(trim($this->ask('Email') ?? '')), 'password' => $this->secret('Lozinka (najmanje 12 znakova)')];
        $validator = Validator::make($data, ['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users,email', 'password' => ['required', Password::min(12)->letters()->numbers()]]);
        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }
        if ($data['password'] !== $this->secret('Ponovi lozinku')) {
            $this->error('Lozinke se ne podudaraju.');

            return self::FAILURE;
        }
        $user = new User($data);
        $user->is_admin = true;
        $user->save();
        $this->info('Administrator je kreiran. Prijava: /admin');

        return self::SUCCESS;
    }
}
