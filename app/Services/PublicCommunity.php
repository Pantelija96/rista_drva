<?php

namespace App\Services;

use App\Models\TreeAdoption;

final class PublicCommunity
{
    public function guardians(): array
    {
        $records = TreeAdoption::where('status', 'active')->whereHas('tree', fn ($q) => $q->published())->get(['id', 'guardian_name', 'guardian_email_hash', 'public_display_preference']);

        return $records->groupBy(fn ($a) => $a->public_display_preference === 'anonymous' ? 'anonymous-'.$a->id : $a->guardian_email_hash.'|'.$a->public_display_preference.'|'.$a->displayName())
            ->map(fn ($adoptions) => ['name' => $adoptions->first()->displayName(), 'trees' => $adoptions->count()])->values()->all();
    }
}
