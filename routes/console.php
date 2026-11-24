<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command(config('ecosync.mail_use_queue') ? 'ecosync:mail-send --queue' : 'ecosync:mail-send --limit=25')->everyMinute()->withoutOverlapping(5);
Schedule::command('ecosync:expire-reservations')->everyMinute()->withoutOverlapping(5);
