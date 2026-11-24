<?php

namespace App\Filament\Resources;

use App\Models\MailOutbox;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MailOutboxResource extends Resource
{
    protected static ?string $model = MailOutbox::class;

    protected static ?string $modelLabel = 'Email zapis';

    protected static ?string $pluralModelLabel = 'Red email poruka';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|\UnitEnum|null $navigationGroup = 'ADMINISTRACIJA';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([TextInput::make('event')->label('Vrsta poruke')->disabled()->dehydrated(false), TextInput::make('reference')->label('ID zapisa')->disabled()->dehydrated(false), TextInput::make('status')->label('Status')->disabled()->dehydrated(false), TextInput::make('attempts')->label('Pokušaji')->disabled()->dehydrated(false), TextInput::make('last_error')->label('Tip greške (bez privatnih detalja)')->disabled()->dehydrated(false)])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('event')->label('Zapis')->searchable()->sortable(), TextColumn::make('status')->label('Status')->formatStateUsing(fn ($state) => (['pending' => 'Čeka obradu', 'processing' => 'U obradi', 'sent' => 'Prihvaćeno transportom', 'logged' => 'Samo log/test', 'failed' => 'Neuspešno', 'cancelled' => 'Otkazano'])[$state] ?? $state)->badge(), TextColumn::make('created_at')->label('Primljeno')->dateTime('d.m.Y. H:i')->sortable()])->filters([SelectFilter::make('status')->label('Status')->options(['pending' => 'Čeka obradu', 'processing' => 'U obradi', 'sent' => 'Prihvaćeno transportom', 'logged' => 'Samo log/test', 'failed' => 'Neuspešno', 'cancelled' => 'Otkazano'])])->recordActions([EditAction::make(), Action::make('retry')->label('Ponovi obradu')->requiresConfirmation()->visible(fn ($record) => $record->status === 'failed')->action(fn ($record) => $record->update(['status' => 'pending', 'attempts' => 0, 'available_at' => now(), 'claim' => null, 'locked_until' => null]))]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListMailOutbox::route('/'), 'edit' => Pages\EditMailOutbox::route('/{record}/edit')];
    }
}
