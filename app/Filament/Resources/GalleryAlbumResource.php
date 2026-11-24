<?php

namespace App\Filament\Resources;

use App\Filament\AdminFields;
use App\Models\GalleryAlbum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
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

class GalleryAlbumResource extends Resource
{
    protected static ?string $model = GalleryAlbum::class;

    protected static ?int $navigationSort = 1;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Galerija';

    protected static ?string $pluralModelLabel = 'Galerije';

    protected static string|\UnitEnum|null $navigationGroup = 'SADRŽAJ';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \App\Filament\TranslationFields::english(['title', 'description', 'location_label']),
            TextInput::make('title')->label('Naslov')->maxLength(255)->required(),
            TextInput::make('slug')->label('URL oznaka')->helperText('Ostavite prazno pri kreiranju za automatsku oznaku. Za sopstvenu oznaku koristite slova, brojeve i crtice.')->maxLength(200)->unique(ignoreRecord: true)->required(fn (string $operation) => $operation === 'edit'),
            Textarea::make('description')->label('Opis')->maxLength(20000)->columnSpanFull(),
            AdminFields::image('cover_image_path', 'Naslovna fotografija albuma'),
            AdminFields::school(),
            AdminFields::generation(),
            DatePicker::make('event_date')->label('Datum aktivnosti'),
            TextInput::make('location_label')->label('Lokacija')->maxLength(255),
            Select::make('category')->label('Kategorija')->options(['škole' => 'Škole', 'sadnje' => 'Sadnje', 'volonteri' => 'Volonteri', 'radionice' => 'Radionice'])->default('sadnje'),
            Toggle::make('published')->label('Objavljeno')->default(false),
            TextInput::make('sort_order')->label('Redosled')->numeric()->minValue(0)->default(0),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->label('Naslov')->searchable()->sortable(),
            IconColumn::make('published')->label('Objavljeno')->boolean(),
            TextColumn::make('created_at')->label('Datum unosa')->dateTime('d.m.Y. H:i')->sortable(),
        ])->filters([TernaryFilter::make('published')->label('Objavljeno')])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGalleryAlbum::route('/'),
            'create' => Pages\CreateGalleryAlbum::route('/create'),
            'edit' => Pages\EditGalleryAlbum::route('/{record}/edit'),
        ];
    }
}
