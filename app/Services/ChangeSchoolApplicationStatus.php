<?php

namespace App\Services;

use App\Models\SchoolApplication;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ChangeSchoolApplicationStatus
{
    public function handle(SchoolApplication $application, string $status): void
    {
        DB::transaction(function () use ($application, $status) {
            $current = SchoolApplication::whereKey($application->id)->lockForUpdate()->firstOrFail();
            if ($current->status === $status) {
                return;
            }
            if (! in_array($current->status, ['pending', 'reviewing'], true) || ! in_array($status, ['reviewing', 'rejected'], true)) {
                throw ValidationException::withMessages(['status' => 'Zahtev je već obrađen. Osvežite pregled.']);
            }
            $current->update(['status' => $status]);
            if ($status === 'rejected') {
                app(Outbox::class)->add('school.rejected', $current->id, 'school.rejected:'.$current->id);
            }
        });
    }
}
