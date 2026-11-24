<?php

namespace App\Filament\Resources;

use App\Filament\AdminFields;
use App\Models\School;
use App\Models\SchoolApplication;
use App\Services\ApproveSchoolApplication;
use App\Services\ChangeSchoolApplicationStatus;
use App\Services\PrivacyRedaction;
use App\Services\SchoolApplicationNotifier;
use App\Support\DomainRules;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SchoolApplicationResource extends Resource
{
    protected static ?string $model = SchoolApplication::class;

    protected static ?int $navigationSort = 1;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Zahtev škole';

    protected static ?string $pluralModelLabel = 'Zahtevi škola';

    protected static string|\UnitEnum|null $navigationGroup = 'EDUKACIJA';

    protected static ?string $recordTitleAttribute = 'school_name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('school_name')->label('Naziv škole')->maxLength(255)->disabled()->dehydrated(false),
            TextInput::make('city')->label('Grad')->maxLength(255)->disabled()->dehydrated(false),
            TextInput::make('municipality')->label('Opština')->maxLength(255)->disabled()->dehydrated(false),
            TextInput::make('contact_person')->label('Kontakt osoba')->maxLength(255)->disabled()->dehydrated(false),
            TextInput::make('contact_role')->label('Funkcija')->maxLength(255)->disabled()->dehydrated(false),
            TextInput::make('email')->label('Email')->maxLength(255)->email()->unique(ignoreRecord: true)->disabled()->dehydrated(false),
            TextInput::make('phone')->label('Telefon')->maxLength(255)->disabled()->dehydrated(false),
            Select::make('desired_planting_model')->label('Željeni model')->options(DomainRules::PLANTING + ['undecided' => 'Dogovor sa EcoSync'])->disabled()->dehydrated(false),
            TextInput::make('estimated_graduate_count')->label('Okvirni broj maturanata')->numeric()->minValue(0)->disabled()->dehydrated(false),
            Textarea::make('message')->label('Poruka')->maxLength(20000)->columnSpanFull()->disabled()->dehydrated(false),
            AdminFields::school(),
            Textarea::make('internal_notes')->label('Interne beleške')->maxLength(20000)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('school_name')->label('Naziv škole')->searchable()->sortable(),
            TextColumn::make('status')->label('Status')->formatStateUsing(fn ($state) => DomainRules::APPLICATION[$state])->badge(),
            TextColumn::make('school.name')->label('Povezana škola'),
            TextColumn::make('created_at')->label('Datum unosa')->dateTime('d.m.Y. H:i')->sortable(),
        ])->filters([SelectFilter::make('status')->label('Status')->options(DomainRules::APPLICATION)])->recordActions([Action::make('redact')->label('Ukloni lične podatke')->requiresConfirmation()->action(fn ($record) => app(PrivacyRedaction::class)->redact($record, auth()->user())), EditAction::make(),
            Action::make('review')->label('Razmotri')->visible(fn ($record) => $record->status === 'pending')->action(fn ($record) => app(ChangeSchoolApplicationStatus::class)->handle($record, 'reviewing')),
            Action::make('approve')->label('Odobri')->color('success')->requiresConfirmation()
                ->visible(fn ($record) => in_array($record->status, ['pending', 'reviewing']))
                ->schema([
                    Select::make('school_id')->label('Postojeća škola (ostavite prazno za novu)')->options(fn () => School::get(['id', 'name', 'city'])->mapWithKeys(fn ($school) => [$school->id => $school->name.' — '.$school->city]))->searchable(),
                    TextInput::make('graduation_year')->label('Godina generacije (opciono)')->numeric()->minValue(1900)->maxValue(2200),
                    Select::make('planting_model')->label('Model nove generacije')->options(DomainRules::PLANTING)->requiredWith('graduation_year'),
                ])->action(function ($record, array $data) {
                    app(ApproveSchoolApplication::class)->handle($record, $data['school_id'] ?? null, $data['graduation_year'] ?? null, $data['planting_model'] ?? null);
                    Notification::make()->title('Zahtev je odobren; škola je povezana.')->success()->send();
                }),
            Action::make('reject')->label('Odbij')->color('danger')->requiresConfirmation()->visible(fn ($record) => in_array($record->status, ['pending', 'reviewing']))->action(fn ($record) => app(ChangeSchoolApplicationStatus::class)->handle($record, 'rejected')),
            Action::make('notify')->label('Pošalji obaveštenje ponovo')->requiresConfirmation()->action(function ($record) {
                $sent = app(SchoolApplicationNotifier::class)->send($record, true);
                Notification::make()->title($sent ? 'Obaveštenje je u trajnom redu pošte.' : 'Slanje nije uspelo. Proverite podešavanja pošte.')->status($sent ? 'success' : 'danger')->send();
            })]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSchoolApplication::route('/'),
            'create' => Pages\CreateSchoolApplication::route('/create'),
            'edit' => Pages\EditSchoolApplication::route('/{record}/edit'),
        ];
    }
}
