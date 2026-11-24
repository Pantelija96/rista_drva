<?php

namespace App\Filament\Resources;

use App\Filament\AdminFields;
use App\Models\Tree;
use App\Support\DomainRules;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TreeResource extends Resource
{
    protected static ?string $model = Tree::class;

    protected static ?int $navigationSort = 1;

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $modelLabel = 'Stablo';

    protected static ?string $pluralModelLabel = 'Stabla';

    protected static string|\UnitEnum|null $navigationGroup = 'STABLA I MAPA';

    protected static ?string $recordTitleAttribute = 'public_identifier';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            \Filament\Schemas\Components\View::make('filament.tree-documents')->viewData(fn ($record) => ['tree'=>$record])->columnSpanFull(),
            \App\Filament\TranslationFields::english(['title', 'description']),
            Select::make('tree_species_id')->label('Vrsta drveta')->relationship('species', 'common_name')->searchable()->required()->required(),
            AdminFields::school(),
            AdminFields::generation(),
            AdminFields::group(),
            TextInput::make('title')->label('Naslov')->maxLength(255),
            Textarea::make('description')->label('Opis')->maxLength(20000)->columnSpanFull(),
            ...AdminFields::location(),
            DatePicker::make('planted_at')->label('Datum sadnje'),
            Select::make('physical_condition')->label('Fizičko stanje')->options(DomainRules::CONDITION)->default('planned'),
            Select::make('adoption_status')->label('Status usvajanja')->options(DomainRules::ADOPTION)->default('unavailable'),
            Toggle::make('public_visibility')->label('Javno vidljivo')->default(false),
            AdminFields::image('featured_image_path', 'Naslovna fotografija'),
            AdminFields::image('photo_paths', 'Dodatne fotografije')->multiple()->reorderable()->maxFiles(30),
            Textarea::make('notes')->label('Interne beleške')->maxLength(20000)->columnSpanFull(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('public_identifier')->label('Identifikator')->searchable()->sortable(),
            TextColumn::make('species.common_name')->label('Vrsta'),
            TextColumn::make('adoption_status')->label('Usvajanje')->formatStateUsing(fn ($state) => DomainRules::ADOPTION[$state]),
            TextColumn::make('created_at')->label('Datum unosa')->dateTime('d.m.Y. H:i')->sortable(),
        ])->filters([])->recordActions([
            \Filament\Actions\Action::make('qr')->label('QR pregled')->visible(fn ($record) => Tree::published()->whereKey($record->id)->exists())->modalContent(fn ($record) => view('filament.tree-documents', ['tree'=>$record]))->modalSubmitAction(false),
            \Filament\Actions\Action::make('qrDownload')->label('Preuzmi QR')->visible(fn ($record) => Tree::published()->whereKey($record->id)->exists())->url(fn ($record) => route('trees.qr', ['locale'=>'sr','identifier'=>$record->public_identifier,'download'=>1])),
            \Filament\Actions\Action::make('label')->label('Oznaka za štampu')->visible(fn ($record) => Tree::published()->whereKey($record->id)->exists())->url(fn ($record) => route('trees.label', ['locale'=>'sr','identifier'=>$record->public_identifier])),
            EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTree::route('/'),
            'create' => Pages\CreateTree::route('/create'),
            'edit' => Pages\EditTree::route('/{record}/edit'),
        ];
    }
}
