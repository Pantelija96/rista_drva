<?php

namespace App\Filament\Resources;

use App\Models\Tree;
use App\Models\TreeAdoption;
use App\Services\Adoptions;
use App\Services\PrivacyRedaction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TreeAdoptionResource extends Resource
{
    protected static ?string $model = TreeAdoption::class;

    protected static ?string $modelLabel = 'Usvajanje';

    protected static ?string $pluralModelLabel = 'Usvajanja stabala';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|\UnitEnum|null $navigationGroup = 'STABLA I MAPA';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([View::make('filament.adoption-certificate')->viewData(fn ($record) => ['adoption'=>$record])->columnSpanFull(), TextInput::make('tree.public_identifier')->label('Stablo')->disabled()->dehydrated(false), TextInput::make('guardian_name')->label('Ime staratelja')->disabled()->dehydrated(false), TextInput::make('guardian_email')->label('Email')->disabled()->dehydrated(false), TextInput::make('guardian_phone')->label('Telefon')->disabled()->dehydrated(false), Select::make('adoption_type')->label('Tip')->options(TreeAdoption::TYPES)->disabled()->dehydrated(false), Select::make('public_display_preference')->label('Javni prikaz')->options(TreeAdoption::PREFERENCES)->disabled()->dehydrated(false), Select::make('status')->label('Status')->options(TreeAdoption::STATUSES)->disabled()->dehydrated(false), TextInput::make('email_verified_at')->label('Email potvrđen')->disabled()->dehydrated(false), TextInput::make('reservation_expires_at')->label('Rezervacija ističe')->disabled()->dehydrated(false), Textarea::make('internal_notes')->label('Interne beleške'), View::make('filament.adoption-history')->viewData(fn ($record) => ['adoption' => $record])->columnSpanFull()])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('tree.public_identifier')->label('Zapis')->searchable()->sortable(), TextColumn::make('status')->label('Status')->formatStateUsing(fn ($state) => (TreeAdoption::STATUSES)[$state] ?? $state)->badge(), TextColumn::make('created_at')->label('Primljeno')->dateTime('d.m.Y. H:i')->sortable(), TextColumn::make('guardian_name')->label('Staratelj')->searchable(query: fn ($query, string $search) => $query->where('guardian_name_hash', hash_hmac('sha256', mb_strtolower(trim($search)), config('app.key')))->orWhere('guardian_email_hash', Adoptions::emailHash($search))), TextColumn::make('email_verified_at')->label('Email potvrđen')->dateTime()])->filters([SelectFilter::make('status')->label('Status')->options(TreeAdoption::STATUSES)])->recordActions([
            Action::make('certificate')->label('Sertifikat')->visible(fn ($record) => app(\App\Services\AdoptionCertificates::class)->eligible($record))->modalContent(fn ($record) => view('filament.adoption-certificate', ['adoption'=>$record]))->modalSubmitAction(false),
            Action::make('emailChange')->label('Potvrđena promena emaila')->requiresConfirmation()->visible(fn ($record) => $record->status === 'active')->schema([TextInput::make('email')->label('Nova email adresa')->email()->required()->maxLength(160)])->action(fn ($record, array $data) => app(Adoptions::class)->requestEmailChange($record, $data['email'], auth()->user())), EditAction::make(), Action::make('approve')->label('Odobri')->requiresConfirmation()->visible(fn ($record) => $record->status === 'pending_approval' && $record->email_verified_at)->action(fn ($record) => app(Adoptions::class)->transition($record, 'active', auth()->user())), Action::make('reject')->label('Odbij')->requiresConfirmation()->visible(fn ($record) => in_array($record->status, ['pending_verification', 'pending_approval']))->action(fn ($record) => app(Adoptions::class)->transition($record, 'rejected', auth()->user())), Action::make('end')->label('Završi usvajanje')->requiresConfirmation()->visible(fn ($record) => $record->status === 'active')->action(fn ($record) => app(Adoptions::class)->transition($record, 'ended', auth()->user())), Action::make('correct')->label('Ispravi lične podatke')->schema([TextInput::make('guardian_name')->label('Ime')->required()->maxLength(160), TextInput::make('guardian_phone')->label('Telefon')->maxLength(50), Select::make('public_display_preference')->label('Javni prikaz')->options(TreeAdoption::PREFERENCES)->required()])->fillForm(fn ($record) => ['guardian_name' => $record->guardian_name, 'guardian_phone' => $record->guardian_phone, 'public_display_preference' => $record->public_display_preference])->action(function ($record, array $data) {
            DB::transaction(function () use ($record, $data) {
                Tree::whereKey($record->tree_id)->lockForUpdate()->firstOrFail();
                $current = TreeAdoption::whereKey($record->id)->lockForUpdate()->firstOrFail();
                $current->update($data);
                app(Adoptions::class)->event($current, 'admin_contact_correction', $current->status, 'admin', auth()->id());
            });
        }), Action::make('redact')->label('Ukloni lične podatke')->color('danger')->requiresConfirmation()->action(fn ($record) => app(PrivacyRedaction::class)->redact($record, auth()->user()))]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('tree');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListTreeAdoption::route('/'), 'edit' => Pages\EditTreeAdoption::route('/{record}/edit')];
    }
}
