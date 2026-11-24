<?php

namespace Database\Seeders;

use App\Models\SiteStatistic;
use Illuminate\Database\Seeder;

class SiteStatisticSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['planted_trees', 'Posađena stabla', 1230, null], ['partner_schools', 'Partnerske škole', 14, null],
            ['volunteers', 'Volonteri', 200, '+'], ['municipalities', 'Gradovi/opštine', 4, null],
            ['participating_students', 'Učenika učestvovalo u akcijama sadnje', 150, '+'],
        ] as $order => [$key,$label,$value,$suffix]) {
            SiteStatistic::firstOrCreate(['key' => $key], compact('label', 'value', 'suffix') + ['sort_order' => $order, 'visible' => true]);
        }
    }
}
