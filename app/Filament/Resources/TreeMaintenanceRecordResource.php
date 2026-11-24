<?php

namespace App\Filament\Resources;

use App\Models\Tree;
use App\Models\TreeMaintenanceRecord;
use App\Services\Maintenance;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;

class TreeMaintenanceRecordResource extends Resource
{
    protected static ?string $model = TreeMaintenanceRecord::class;

    protected static ?string $modelLabel = 'Izveštaj o nezi';

    protected static ?string $pluralModelLabel = 'Nega stabala';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static string|\UnitEnum|null $navigationGroup = 'STABLA I MAPA';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \App\Filament\TranslationFields::english(['public_description']),Select::make('tree_id')->label('Stablo')->options(fn () => Tree::pluck('public_identifier', 'id'))->searchable()->required()->disabled(fn (string $operation) => $operation === 'edit')->dehydrated(fn (string $operation) => $operation === 'create'), Select::make('activity_type')->label('Aktivnost')->options(TreeMaintenanceRecord::ACTIVITIES)->required()->disabled(fn (string $operation) => $operation === 'edit')->dehydrated(fn (string $operation) => $operation === 'create'), Textarea::make('description')->label('Izvorni privatni izveštaj')->required()->maxLength(3000)->disabled(fn (string $operation) => $operation === 'edit')->dehydrated(fn (string $operation) => $operation === 'create'), DatePicker::make('performed_at')->label('Datum aktivnosti')->required()->maxDate(now())->disabled(fn (string $operation) => $operation === 'edit')->dehydrated(fn (string $operation) => $operation === 'create'), FileUpload::make('photos')->label('Fotografije')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->multiple()->maxFiles(5)->maxSize(5120)->storeFiles(false)->visible(fn (string $operation) => $operation === 'create'), Textarea::make('internal_notes')->label('Interne beleške'), View::make('filament.maintenance-photos')->viewData(fn ($record) => ['maintenance' => $record])->columnSpanFull()])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('tree.public_identifier')->label('Zapis')->searchable()->sortable(), TextColumn::make('review_status')->label('Status')->formatStateUsing(fn ($state) => (TreeMaintenanceRecord::REVIEWS)[$state] ?? $state)->badge(), TextColumn::make('created_at')->label('Primljeno')->dateTime('d.m.Y. H:i')->sortable()])->filters([SelectFilter::make('review_status')->label('Status')->options(TreeMaintenanceRecord::REVIEWS)])->recordActions([EditAction::make(), Action::make('approve')->label('Odobri za javnost')->requiresConfirmation()->schema([
            Textarea::make('public_description')->label('Javni tekst bez privatnih podataka')->required()->maxLength(3000),
            CheckboxList::make('photos')->label('Fotografije odobrene za javnost')->options(fn ($record) => $record->photos->filter(fn ($photo) => Storage::disk('local')->exists($photo->path))->mapWithKeys(fn ($photo) => [$photo->id => $photo->alt_text])),
            Checkbox::make('public_safe')->label('Proverio/la sam da tekst i izabrane fotografije mogu biti javno objavljeni.')->accepted(),
        ])->fillForm(fn ($record) => ['public_description' => $record->public_description ?: $record->description])->action(fn ($record, array $data) => app(Maintenance::class)->review($record, 'approved', auth()->user(), $data['public_description'], $data['photos'] ?? [])), Action::make('reject')->label('Odbij / povuci objavu')->requiresConfirmation()->action(fn ($record) => app(Maintenance::class)->review($record, 'rejected', auth()->user()))]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('tree');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListTreeMaintenanceRecord::route('/'), 'edit' => Pages\EditTreeMaintenanceRecord::route('/{record}/edit'), 'create' => Pages\CreateTreeMaintenanceRecord::route('/create')];
    }
}
