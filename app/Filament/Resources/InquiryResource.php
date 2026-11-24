<?php

namespace App\Filament\Resources;

use App\Models\Inquiry;
use App\Services\PrivacyRedaction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class InquiryResource extends Resource
{
    protected static ?string $model = Inquiry::class;

    protected static ?string $modelLabel = 'Upit';

    protected static ?string $pluralModelLabel = 'Upiti i podrška';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|\UnitEnum|null $navigationGroup = 'EDUKACIJA';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('kind')->label('Vrsta')->disabled()->dehydrated(false), TextInput::make('name')->label('Ime')->required()->maxLength(160), TextInput::make('email')->label('Email')->email()->maxLength(160), TextInput::make('phone')->label('Telefon')->maxLength(50), TextInput::make('topic')->label('Tema')->disabled()->dehydrated(false), Textarea::make('message')->label('Poruka')->required()->maxLength(3000), Select::make('status')->label('Status')->options(['pending' => 'Na čekanju', 'reviewing' => 'U razmatranju', 'closed' => 'Zatvoreno'])->required(), Textarea::make('internal_notes')->label('Interne beleške')])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->label('Zapis')->searchable()->sortable(), TextColumn::make('status')->label('Status')->formatStateUsing(fn ($state) => (['pending' => 'Na čekanju', 'reviewing' => 'U razmatranju', 'closed' => 'Zatvoreno'])[$state] ?? $state)->badge(), TextColumn::make('created_at')->label('Primljeno')->dateTime('d.m.Y. H:i')->sortable()])->filters([SelectFilter::make('status')->label('Status')->options(['pending' => 'Na čekanju', 'reviewing' => 'U razmatranju', 'closed' => 'Zatvoreno'])])->recordActions([EditAction::make(), Action::make('redact')->label('Ukloni lične podatke')->color('danger')->requiresConfirmation()->action(fn ($record) => app(PrivacyRedaction::class)->redact($record, auth()->user()))]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListInquiry::route('/'), 'edit' => Pages\EditInquiry::route('/{record}/edit')];
    }
}
