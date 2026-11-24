<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InspectDatabase extends Command
{
    protected $signature = 'ecosync:database-inspect';

    protected $description = 'Read-only provera izabrane MySQL baze i tabela';

    public function handle(): int
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            $this->error('Izaberite mysql vezu.');

            return self::FAILURE;
        }
        try {
            $selected = DB::selectOne('select database() as selected_database')->selected_database;
            if ($selected !== config('database.connections.mysql.database')) {
                $this->error('Izabrana baza ne odgovara konfiguraciji.');

                return self::FAILURE;
            }
            $this->info('Izabrana baza: '.$selected);
            foreach (DB::select('show tables') as $row) {
                $table = array_values((array) $row)[0];
                $this->line($table.' — '.DB::table($table)->count().' redova');
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Veza nije uspela. Proverite DB_* podešavanja i mrežni pristup.');

            return self::FAILURE;
        }
    }
}
