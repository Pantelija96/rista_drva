<?php

namespace App\Services;

use App\Models\Tree;
use App\Models\TreeAdoption;
use App\Models\TreeMaintenanceRecord;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class Maintenance
{
    public static function photoRules(): array
    {
        return ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:min_width=100,min_height=100,max_width=6000,max_height=6000'];
    }

    public function create(Tree $tree, array $data, array $photos = [], ?TreeAdoption $guardian = null, ?User $admin = null, ?string $submissionKey = null): TreeMaintenanceRecord
    {
        Validator::make($data, ['activity_type' => ['required', Rule::in(array_keys(TreeMaintenanceRecord::ACTIVITIES))], 'description' => 'required|string|max:3000', 'performed_at' => 'required|date|before_or_equal:today'])->validate();
        Validator::make(['photos' => $photos], ['photos' => 'array|max:5', 'photos.*' => self::photoRules()])->validate();
        $paths = [];
        try {
            return DB::transaction(function () use ($tree, $data, $photos, $guardian, $admin, $submissionKey, &$paths) {
                $tree = Tree::whereKey($tree->id)->lockForUpdate()->firstOrFail();
                if ($guardian) {
                    $guardian = TreeAdoption::whereKey($guardian->id)->lockForUpdate()->firstOrFail();
                    abort_unless($guardian->tree_id === $tree->id && $guardian->status === 'active', 403);
                } else {
                    abort_unless($admin?->is_admin, 403);
                }
                if ($submissionKey && ($existing = TreeMaintenanceRecord::where('submission_key', $submissionKey)->first())) {
                    return $existing;
                }
                $record = TreeMaintenanceRecord::create(['public_id' => (string) Str::uuid(), 'tree_id' => $tree->id, 'tree_adoption_id' => $guardian?->id,
                    'activity_type' => $data['activity_type'], 'description' => trim(strip_tags($data['description'])), 'performed_at' => $data['performed_at'],
                    'submitted_by_type' => $guardian ? 'guardian' : 'admin', 'submitted_by_id' => $admin?->id, 'review_status' => 'pending', 'submission_key' => $submissionKey]);
                foreach ($photos as $photo) {
                    $size = getimagesize($photo->getRealPath());
                    if (! $size || $size[0] * $size[1] > 8000000) {
                        throw ValidationException::withMessages(['photos' => __('public.the_photo_contains_too_many_pixels')]);
                    }
                    $source = imagecreatefromstring(file_get_contents($photo->getRealPath()));
                    if (! $source) {
                        throw ValidationException::withMessages(['photos' => __('public.the_photo_is_invalid')]);
                    }
                    $clean = imagecreatetruecolor(imagesx($source), imagesy($source));
                    imagefill($clean, 0, 0, imagecolorallocate($clean, 255, 255, 255));
                    imagecopy($clean, $source, 0, 0, 0, 0, imagesx($source), imagesy($source));
                    ob_start();
                    imagejpeg($clean, null, 85);
                    $bytes = ob_get_clean();
                    $path = 'maintenance/'.Str::uuid().'.jpg';
                    if (! Storage::disk('local')->put($path, $bytes)) {
                        throw new \RuntimeException('Private upload failed');
                    }
                    $paths[] = $path;
                    $record->photos()->create(['public_id' => (string) Str::uuid(), 'path' => $path, 'review_status' => 'pending', 'alt_text' => __('public.tree_care').$tree->public_identifier]);
                }

                return $record;
            });
        } catch (\Throwable $e) {
            foreach ($paths as $path) {
                Storage::disk('local')->delete($path);
            }throw $e;
        }
    }

    public function review(TreeMaintenanceRecord $record, string $status, User $admin, ?string $publicDescription = null, array $approvedPhotoIds = []): void
    {
        abort_unless($admin->is_admin, 403);
        Validator::make(['status' => $status, 'public_description' => $publicDescription], ['status' => Rule::in(['approved', 'rejected']), 'public_description' => [$status === 'approved' ? 'required' : 'nullable', 'string', 'max:3000']])->validate();
        DB::transaction(function () use ($record, $status, $admin, $publicDescription, $approvedPhotoIds) {
            $current = TreeMaintenanceRecord::whereKey($record->id)->lockForUpdate()->firstOrFail();
            foreach ($approvedPhotoIds as $photoId) {
                $photo = $current->photos()->find($photoId);
                if (! $photo || ! Storage::disk('local')->exists($photo->path)) {
                    throw ValidationException::withMessages(['photos' => __('public.the_selected_photo_is_not_available_for_publication')]);
                }
            }
            $current->update(['review_status' => $status, 'public_description' => $status === 'approved' ? trim(strip_tags($publicDescription)) : null, 'approved_by' => $admin->id, 'approved_at' => now()]);
            foreach ($current->photos as $photo) {
                $approve = $status === 'approved' && in_array($photo->id, array_map('intval', $approvedPhotoIds), true);
                $photo->update(['review_status' => $approve ? 'approved' : 'rejected']);
                if (! $approve) {
                    DB::afterCommit(fn () => Storage::disk('local')->delete($photo->path));
                }
            }
        });
    }
}
