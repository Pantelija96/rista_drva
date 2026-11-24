<?php

namespace App\Filament\Resources;

use App\Filament\AdminFields;
use App\Models\GalleryImage;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class GalleryImageResource extends Resource
{
    protected static ?string $model = GalleryImage::class;

    protected static ?int $navigationSort = 2;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Fotografija';

    protected static ?string $pluralModelLabel = 'Fotografije';

    protected static string|\UnitEnum|null $navigationGroup = 'SADRŽAJ';

    protected static ?string $recordTitleAttribute = 'alt_text';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \App\Filament\TranslationFields::english(['alt_text', 'caption']),
            Select::make('gallery_album_id')->label('Album')->relationship('album', 'title')->searchable()->required()->required(),
            AdminFields::image('image_path', 'Fotografija')->required(),
            TextInput::make('alt_text')->label('Opis slike za pristupačnost')->maxLength(255)->required(),
            Textarea::make('caption')->label('Natpis')->maxLength(20000)->columnSpanFull(),
            TextInput::make('sort_order')->label('Redosled')->numeric()->minValue(0)->default(0),
            Toggle::make('published')->label('Objavljeno')->default(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('alt_text')->label('Opis slike za pristupačnost')->searchable()->sortable(),
            IconColumn::make('published')->label('Objavljeno')->boolean(),
            TextColumn::make('created_at')->label('Datum unosa')->dateTime('d.m.Y. H:i')->sortable(),
        ])->reorderable('sort_order')->filters([TernaryFilter::make('published')->label('Objavljeno')])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleryImage::route('/'),
            'create' => Pages\CreateGalleryImage::route('/create'),
            'edit' => Pages\EditGalleryImage::route('/{record}/edit'),
        ];
    }
}
