<?php

namespace App\Console\Commands;

use App\Services\MediaReferences;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanMedia extends Command
{
    protected $signature = 'ecosync:clean-media {--delete : Obriši nepotrebne fajlove starije od sedam dana}';

    protected $description = 'Pregledaj fajlove koji su ostali posle prekinutih unosa';

    public function handle(MediaReferences $references): int
    {
        $disk = Storage::disk('public');
        foreach ($disk->allFiles('ecosync') as $path) {
            if ($disk->lastModified($path) > now()->subDays(7)->timestamp || $references->exists($path)) {
                continue;
            }
            $this->line($path);
            if ($this->option('delete')) {
                $disk->delete($path);
            }
        }

        return self::SUCCESS;
    }
}
