<?php

namespace App\Services;

use App\Models\SchoolApplication;
use Illuminate\Support\Str;

class SchoolApplicationNotifier
{
    public function send(SchoolApplication $application, bool $retry = false): bool
    {
        app(Outbox::class)->add('school.admin', $application->id, 'school.admin:'.$application->id.($retry ? ':retry-'.Str::uuid() : ''));
        if (! $retry) {
            app(Outbox::class)->add('school.ack', $application->id, 'school.ack:'.$application->id);
        }

        return true;
    }
}
