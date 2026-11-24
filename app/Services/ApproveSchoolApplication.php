<?php

namespace App\Services;

use App\Models\School;
use App\Models\SchoolApplication;
use App\Models\SchoolGeneration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class ApproveSchoolApplication
{
    public function handle(SchoolApplication $application, ?int $schoolId = null, ?int $year = null, ?string $model = null): School
    {
        return DB::transaction(function () use ($application, $schoolId, $year, $model) {
            $application = SchoolApplication::lockForUpdate()->findOrFail($application->id);
            if ($application->status === 'approved') {
                return $application->school;
            }
            if (! in_array($application->status, ['pending', 'reviewing'], true)) {
                throw ValidationException::withMessages(['status' => 'Zahtev je već obrađen.']);
            }
            if ($schoolId) {
                $school = School::lockForUpdate()->findOrFail($schoolId);
            } else {
                if (School::where('name', $application->school_name)->where('city', $application->city)->exists()) {
                    throw ValidationException::withMessages(['school_id' => 'Škola sa ovim nazivom i gradom postoji. Izričito izaberite postojeću školu ili proverite podatke.']);
                }
                $school = School::create([
                    'name' => $application->school_name, 'slug' => Str::slug($application->school_name).'-'.Str::lower(Str::random(8)),
                    'city' => $application->city, 'municipality' => $application->municipality, 'contact_name' => $application->contact_person,
                    'contact_email' => $application->email, 'contact_phone' => $application->phone, 'status' => 'draft',
                ]);
            }
            if ($year) {
                SchoolGeneration::firstOrCreate(['school_id' => $school->id, 'graduation_year' => $year, 'program' => 'general'], [
                    'planting_model' => $model ?? $application->desired_planting_model, 'expected_graduates' => $application->estimated_graduate_count, 'status' => 'draft',
                ]);
            }
            $application->update(['school_id' => $school->id, 'status' => 'approved']);
            app(Outbox::class)->add('school.approved', $application->id, 'school.approved:'.$application->id);

            return $school;
        });
    }
}
