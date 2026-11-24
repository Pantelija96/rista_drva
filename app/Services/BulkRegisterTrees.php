<?php

namespace App\Services;

use App\Models\Tree;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

final class BulkRegisterTrees
{
    public function handle(array $data, int $quantity): array
    {
        Validator::make(['quantity' => $quantity], ['quantity' => 'required|integer|min:1|max:500'])->validate();

        return DB::transaction(function () use ($data, $quantity) {
            $records = [];
            for ($i = 0; $i < $quantity; $i++) {
                $records[] = Tree::create([
                    ...$data, 'latitude' => null, 'longitude' => null, 'public_visibility' => false,
                ]);
            }

            return $records;
        });
    }
}
