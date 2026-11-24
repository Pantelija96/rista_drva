<?php

namespace App\Filament\Resources\Pages;

use App\Filament\AdminFields;
use App\Filament\Concerns\HandlesDomainValidation;
use App\Filament\Resources\GalleryAlbumResource;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditGalleryAlbum extends EditRecord
{
    use HandlesDomainValidation;

    protected static string $resource = GalleryAlbumResource::class;

    protected function getHeaderActions(): array
    {
        return [Action::make('photos')->label('Dodaj fotografije')->schema([
            AdminFields::image('images', 'Fotografije')->multiple()->maxFiles(30)->required(),
            TextInput::make('alt_text')->label('Opis fotografija (naknadno izmenite svaki opis)')->required()->maxLength(255),
        ])->action(function (array $data) {
            DB::transaction(function () use ($data) {
                $order = $this->record->images()->max('sort_order') ?? 0;
                foreach ($data['images'] as $path) {
                    $this->record->images()->create(['image_path' => $path, 'alt_text' => $data['alt_text'], 'sort_order' => ++$order, 'published' => false]);
                }
            });
            Notification::make()->title('Fotografije su dodate kao nacrt.')->success()->send();
        })];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        foreach ($this->record->getFillable() as $field) {
            $data[$field] = $this->record->getAttribute($field);
        }

        return $data;
    }
}
