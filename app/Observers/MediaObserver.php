<?php

namespace App\Observers;

use App\Services\MediaReferences;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaObserver
{
    public const FIELDS = ['logo_path', 'featured_image_path', 'main_image_path', 'cover_image_path', 'image_path', 'photo_paths', 'gallery_paths'];

    public function updated(Model $model): void
    {
        foreach (self::FIELDS as $field) {
            if ($model->wasChanged($field)) {
                $old = $model->getRawOriginal($field);
                if (in_array($field, ['photo_paths', 'gallery_paths'], true)) {
                    $old = json_decode($old ?? '[]', true);
                }
                foreach (array_diff((array) $old, (array) $model->$field) as $path) {
                    $this->deleteUnreferenced($path);
                }
            }
        }
    }

    public function deleted(Model $model): void
    {
        foreach (self::FIELDS as $field) {
            foreach ((array) $model->$field as $path) {
                $this->deleteUnreferenced($path);
            }
        }
    }

    private function deleteUnreferenced(?string $path): void
    {
        if (! $path || ! str_starts_with($path, 'ecosync/') || str_contains($path, '..')) {
            return;
        }
        DB::afterCommit(function () use ($path) {
            if (app(MediaReferences::class)->exists($path)) {
                return;
            }
            if (! Storage::disk('public')->delete($path)) {
                Log::warning('Media file cleanup failed.');
            }
        });
    }
}
