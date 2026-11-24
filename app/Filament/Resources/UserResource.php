<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rules\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 1;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Administrator';

    protected static ?string $pluralModelLabel = 'Administratori';

    protected static string|\UnitEnum|null $navigationGroup = 'ADMINISTRACIJA';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Ime i prezime')->maxLength(255)->required(),
            TextInput::make('email')->label('Email')->required()->maxLength(255)->email()->unique(ignoreRecord: true),
            TextInput::make('password')->label('Lozinka')->password()->revealable()->minLength(12)->rules([Password::min(12)->letters()->numbers()])->required(fn (string $operation) => $operation === 'create')->dehydrated(fn ($state) => filled($state)),
            Toggle::make('is_admin')->label('Administratorski pristup')->default(true)->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->label('Naziv')->searchable()->sortable(),
            TextColumn::make('created_at')->label('Datum unosa')->dateTime('d.m.Y. H:i')->sortable(),
        ])->filters([])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('is_admin', true);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUser::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
