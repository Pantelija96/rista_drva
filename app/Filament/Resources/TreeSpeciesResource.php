<?php

namespace App\Filament\Resources;

use App\Filament\AdminFields;
use App\Models\TreeSpecies;
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

class TreeSpeciesResource extends Resource
{
    protected static ?string $model = TreeSpecies::class;

    protected static ?int $navigationSort = 3;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Vrsta drveta';

    protected static ?string $pluralModelLabel = 'Vrste drveća';

    protected static string|\UnitEnum|null $navigationGroup = 'STABLA I MAPA';

    protected static ?string $recordTitleAttribute = 'common_name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \App\Filament\TranslationFields::english(['common_name', 'short_description', 'description', 'preferred_habitat', 'sunlight_requirements', 'water_requirements', 'planting_recommendations', 'maintenance_guidelines', 'ecological_benefits', 'interesting_facts']),
            TextInput::make('common_name')->label('Narodni naziv')->maxLength(255)->required(),
            TextInput::make('scientific_name')->label('Naučni naziv')->maxLength(255),
            TextInput::make('slug')->label('URL oznaka')->helperText('Ostavite prazno pri kreiranju za automatsku oznaku. Za sopstvenu oznaku koristite slova, brojeve i crtice.')->maxLength(200)->unique(ignoreRecord: true)->required(fn (string $operation) => $operation === 'edit'),
            Textarea::make('short_description')->label('Kratak opis')->maxLength(20000)->columnSpanFull(),
            Textarea::make('description')->label('Opis')->maxLength(20000)->columnSpanFull(),
            AdminFields::image('main_image_path', 'Glavna fotografija'),
            AdminFields::image('gallery_paths', 'Dodatne fotografije')->multiple()->reorderable()->maxFiles(30),
            Select::make('native_to_serbia')->label('Autohtona vrsta u Srbiji')->options([1 => 'Da', 0 => 'Ne'])->placeholder('Nepoznato'),
            Select::make('deciduous_or_evergreen')->label('Listopadno / zimzeleno')->options(['deciduous' => 'Listopadno', 'evergreen' => 'Zimzeleno']),
            TextInput::make('typical_height_m')->label('Tipična visina (m)')->numeric()->minValue(0),
            TextInput::make('expected_lifespan_years')->label('Očekivani životni vek (godine)')->numeric()->minValue(0),
            Textarea::make('preferred_habitat')->label('Stanište')->maxLength(20000)->columnSpanFull(),
            Textarea::make('sunlight_requirements')->label('Potrebe za svetlošću')->maxLength(20000)->columnSpanFull(),
            Textarea::make('water_requirements')->label('Potrebe za vodom')->maxLength(20000)->columnSpanFull(),
            Textarea::make('planting_recommendations')->label('Preporuke za sadnju')->maxLength(20000)->columnSpanFull(),
            Textarea::make('maintenance_guidelines')->label('Smernice za negu')->maxLength(20000)->columnSpanFull(),
            Textarea::make('ecological_benefits')->label('Ekološke koristi')->maxLength(20000)->columnSpanFull(),
            Textarea::make('interesting_facts')->label('Zanimljivosti')->maxLength(20000)->columnSpanFull(),
            Toggle::make('published')->label('Objavljeno')->default(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('common_name')->label('Narodni naziv')->searchable()->sortable(),
            IconColumn::make('published')->label('Objavljeno')->boolean(),
            TextColumn::make('created_at')->label('Datum unosa')->dateTime('d.m.Y. H:i')->sortable(),
        ])->filters([TernaryFilter::make('published')->label('Objavljeno')])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTreeSpecies::route('/'),
            'create' => Pages\CreateTreeSpecies::route('/create'),
            'edit' => Pages\EditTreeSpecies::route('/{record}/edit'),
        ];
    }
}
