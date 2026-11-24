<?php

namespace App\Jobs;

use App\Services\Outbox;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class DrainMailOutbox implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $timeout = 120;

    public function handle(Outbox $outbox): void
    {
        $outbox->drain();
    }
}
