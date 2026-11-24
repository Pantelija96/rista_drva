<?php

namespace App\Filament\Resources;

use App\Filament\AdminFields;
use App\Models\School;
use App\Support\DomainRules;
use Filament\Actions\Action;
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

class SchoolResource extends Resource
{
    protected static ?string $model = School::class;

    protected static ?int $navigationSort = 2;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Škola';

    protected static ?string $pluralModelLabel = 'Škole';

    protected static string|\UnitEnum|null $navigationGroup = 'EDUKACIJA';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \App\Filament\TranslationFields::english(['description']),
            TextInput::make('name')->label('Naziv')->maxLength(255)->required(),
            TextInput::make('slug')->label('URL oznaka')->helperText('Ostavite prazno pri kreiranju za automatsku oznaku. Za sopstvenu oznaku koristite slova, brojeve i crtice.')->maxLength(200)->unique(ignoreRecord: true)->required(fn (string $operation) => $operation === 'edit'),
            Select::make('institution_type')->label('Tip ustanove')->options(DomainRules::INSTITUTIONS)->default('secondary'),
            TextInput::make('city')->label('Grad')->maxLength(255)->required(),
            TextInput::make('municipality')->label('Opština')->maxLength(255),
            TextInput::make('address')->label('Adresa')->maxLength(255),
            ...AdminFields::location(),
            TextInput::make('contact_name')->label('Kontakt osoba (privatno)')->maxLength(255),
            TextInput::make('contact_email')->label('Email (privatno)')->maxLength(255)->email(),
            TextInput::make('contact_phone')->label('Telefon (privatno)')->maxLength(255),
            Textarea::make('description')->label('Opis')->maxLength(20000)->columnSpanFull(),
            AdminFields::image('logo_path', 'Logo škole'),
            AdminFields::image('featured_image_path', 'Naslovna fotografija'),
            Select::make('status')->label('Status')->options(DomainRules::STATUS)->default('draft'),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Naziv')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->formatStateUsing(fn ($state) => DomainRules::STATUS[$state])->badge(),
            TextColumn::make('created_at')->label('Datum unosa')->dateTime('d.m.Y. H:i')->sortable(),
        ])->filters([SelectFilter::make('status')->label('Status')->options(DomainRules::STATUS)])->recordActions([Action::make('redactContacts')->label('Ukloni privatne kontakte')->requiresConfirmation()->action(fn ($record) => $record->update(['contact_name' => null, 'contact_email' => null, 'contact_phone' => null])), EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchool::route('/'),
            'create' => Pages\CreateSchool::route('/create'),
            'edit' => Pages\EditSchool::route('/{record}/edit'),
        ];
    }
}
