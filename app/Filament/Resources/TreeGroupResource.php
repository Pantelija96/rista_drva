<?php

namespace App\Filament\Resources;

use App\Filament\AdminFields;
use App\Models\TreeGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class TreeGroupResource extends Resource
{
    protected static ?string $model = TreeGroup::class;

    protected static ?int $navigationSort = 2;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Grupa stabala';

    protected static ?string $pluralModelLabel = 'Grupe stabala';

    protected static string|\UnitEnum|null $navigationGroup = 'STABLA I MAPA';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \App\Filament\TranslationFields::english(['name', 'description']),
            TextInput::make('name')->label('Naziv')->maxLength(255)->required(),
            TextInput::make('slug')->label('URL oznaka')->helperText('Ostavite prazno pri kreiranju za automatsku oznaku. Za sopstvenu oznaku koristite slova, brojeve i crtice.')->maxLength(200)->unique(ignoreRecord: true)->required(fn (string $operation) => $operation === 'edit'),
            AdminFields::school(),
            AdminFields::generation(),
            Textarea::make('description')->label('Opis')->maxLength(20000)->columnSpanFull(),
            ...AdminFields::location(),
            DatePicker::make('planted_at')->label('Datum sadnje'),
            TextInput::make('declared_tree_count')->label('Prijavljen ukupan broj stabala')->numeric()->minValue(0)->helperText('Ukupan broj grupe uključuje pojedinačno registrovana stabla. Brojevi se ne sabiraju.'),
            AdminFields::image('featured_image_path', 'Naslovna fotografija'),
            Toggle::make('published')->label('Objavljeno')->default(false),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Naziv')->searchable()->sortable(),
            IconColumn::make('published')->label('Objavljeno')->boolean(),
            TextColumn::make('declared_tree_count')->label('Ukupno prijavljeno'),
            TextColumn::make('trees_count')->counts('trees')->label('Pojedinačno registrovano'),
            TextColumn::make('created_at')->label('Datum unosa')->dateTime('d.m.Y. H:i')->sortable(),
        ])->filters([TernaryFilter::make('published')->label('Objavljeno')])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTreeGroup::route('/'),
            'create' => Pages\CreateTreeGroup::route('/create'),
            'edit' => Pages\EditTreeGroup::route('/{record}/edit'),
        ];
    }
}
