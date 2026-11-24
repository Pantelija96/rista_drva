<?php

namespace App\Filament\Resources;

use App\Filament\AdminFields;
use App\Models\SchoolGeneration;
use App\Support\DomainRules;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SchoolGenerationResource extends Resource
{
    protected static ?string $model = SchoolGeneration::class;

    protected static ?int $navigationSort = 3;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Generacija';

    protected static ?string $pluralModelLabel = 'Generacije';

    protected static string|\UnitEnum|null $navigationGroup = 'EDUKACIJA';

    protected static ?string $recordTitleAttribute = 'graduation_year';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \App\Filament\TranslationFields::english(['name']),
            AdminFields::school(true),
            TextInput::make('graduation_year')->label('Godina završetka školovanja')->numeric()->minValue(0)->required(),
            TextInput::make('program')->label('Program / smer')->maxLength(255)->required()->default('general'),
            TextInput::make('name')->label('Naziv')->maxLength(255),
            Select::make('planting_model')->label('Model sadnje')->options(DomainRules::PLANTING)->required(),
            TextInput::make('expected_graduates')->label('Očekivani broj maturanata')->numeric()->minValue(0),
            Textarea::make('notes')->label('Interne beleške')->maxLength(20000)->columnSpanFull(),
            Select::make('status')->label('Status')->options(DomainRules::STATUS)->default('draft'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('graduation_year')->label('Godina završetka školovanja')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->formatStateUsing(fn ($state) => DomainRules::STATUS[$state])->badge(),
            TextColumn::make('created_at')->label('Datum unosa')->dateTime('d.m.Y. H:i')->sortable(),
        ])->filters([SelectFilter::make('status')->label('Status')->options(DomainRules::STATUS)])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolGeneration::route('/'),
            'create' => Pages\CreateSchoolGeneration::route('/create'),
            'edit' => Pages\EditSchoolGeneration::route('/{record}/edit'),
        ];
    }
}
