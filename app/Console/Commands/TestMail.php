<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMail extends Command
{
    protected $signature = 'ecosync:test-mail';

    protected $description = 'Pošalji probnu poruku konfigurisanom primaocu obaveštenja';

    public function handle(): int
    {
        $email = config('ecosync.notification_email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Podesite ECOSYNC_NOTIFICATION_EMAIL.');

            return self::FAILURE;
        }
        try {
            Mail::raw('Probna EcoSync poruka. Potvrdite prijem u inbox-u.', fn ($message) => $message->to($email)->subject('EcoSync — provera pošte'));
        } catch (\Throwable) {
            $this->error('Slanje nije uspelo. Proverite SMTP podešavanja.');

            return self::FAILURE;
        }
        $this->info(config('mail.default') === 'log' ? 'Poruka je zapisana u log. Nije isporučena emailom.' : 'Transport je prihvatio poruku. Proverite prijem u inbox-u.');

        return self::SUCCESS;
    }
}
