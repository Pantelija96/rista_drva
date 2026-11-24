<?php

namespace App\Filament\Resources;

use App\Models\SiteStatistic;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SiteStatisticResource extends Resource
{
    protected static ?string $model = SiteStatistic::class;

    protected static ?int $navigationSort = 3;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Statistika sajta';

    protected static ?string $pluralModelLabel = 'Statistika sajta';

    protected static string|\UnitEnum|null $navigationGroup = 'SADRŽAJ';

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \App\Filament\TranslationFields::english(['label']),
            TextInput::make('key')->label('Ključ')->maxLength(255)->unique(ignoreRecord: true)->required(),
            TextInput::make('label')->label('Naziv statistike')->maxLength(255)->required(),
            TextInput::make('value')->label('Broj')->numeric()->minValue(0)->required(),
            TextInput::make('suffix')->label('Sufiks')->maxLength(255),
            TextInput::make('sort_order')->label('Redosled')->numeric()->minValue(0)->default(0),
            Toggle::make('visible')->label('Vidljivo')->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('label')->label('Naziv statistike')->searchable()->sortable(),
            TextColumn::make('created_at')->label('Datum unosa')->dateTime('d.m.Y. H:i')->sortable(),
        ])->filters([])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSiteStatistic::route('/'),
            'create' => Pages\CreateSiteStatistic::route('/create'),
            'edit' => Pages\EditSiteStatistic::route('/{record}/edit'),
        ];
    }
}
