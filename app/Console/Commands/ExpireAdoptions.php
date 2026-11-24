<?php

namespace App\Console\Commands;

use App\Models\GuardianAccessGrant;
use App\Models\Tree;
use App\Models\TreeAdoption;
use App\Services\Adoptions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireAdoptions extends Command
{
    protected $signature = 'ecosync:expire-reservations';

    protected $description = 'Oslobodi istekle privremene rezervacije bez menjanja aktivnih usvajanja';

    public function handle(Adoptions $service): int
    {
        $count = 0;
        TreeAdoption::whereIn('status', ['pending_verification', 'pending_approval'])->where('reservation_expires_at', '<=', now())->select('tree_id')->distinct()->orderBy('tree_id')->get()->each(function ($record) use ($service, &$count) {
            $count += DB::transaction(function () use ($service, $record) {
                return $service->expireTree(Tree::whereKey($record->tree_id)->lockForUpdate()->firstOrFail());
            });
        });
        GuardianAccessGrant::where('expires_at', '<=', now())->update(['token_hash' => null]);
        $this->info('Isteklih rezervacija: '.$count);

        return self::SUCCESS;
    }
}
