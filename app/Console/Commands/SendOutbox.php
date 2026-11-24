<?php

namespace App\Console\Commands;

use App\Jobs\DrainMailOutbox;
use App\Services\Outbox;
use Illuminate\Console\Command;

class SendOutbox extends Command
{
    protected $signature = 'ecosync:mail-send {--limit=25} {--queue : Stavi obradu u database red poslova}';

    protected $description = 'Obradi trajni red email poruka uz ponovne pokušaje';

    public function handle(Outbox $outbox): int
    {
        if ($this->option('queue')) {
            if (config('queue.default') !== 'database') {
                $this->error('Opcija --queue zahteva QUEUE_CONNECTION=database.');

                return self::FAILURE;
            }
            DrainMailOutbox::dispatch();
            $this->info('Obrada je stavljena u red poslova.');

            return self::SUCCESS;
        }
        $this->info('Obrađeno poruka: '.$outbox->drain(max(1, min(100, (int) $this->option('limit')))));
        if (in_array(config('mail.default'), ['log', 'array'], true)) {
            $this->warn('Testni transport. Ovo nije potvrđena email isporuka.');
        }

        return self::SUCCESS;
    }
}
