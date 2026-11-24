<?php

namespace App\Filament\Resources\Pages;

use App\Filament\AdminFields;
use App\Filament\Resources\TreeResource;
use App\Models\TreeSpecies;
use App\Services\BulkRegisterTrees;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListTree extends ListRecords
{
    protected static string $resource = TreeResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make(),
            Action::make('bulk')->label('Registruj više stabala')->schema([
                AdminFields::school(), AdminFields::generation(), AdminFields::group(),
                Select::make('tree_species_id')->label('Vrsta')->options(TreeSpecies::pluck('common_name', 'id'))->searchable()->required(),
                TextInput::make('quantity')->label('Količina')->numeric()->required()->minValue(1)->maxValue(500),
            ])->action(function (array $data) {
                $quantity = (int) $data['quantity'];
                unset($data['quantity']);
                app(BulkRegisterTrees::class)->handle($data, $quantity);
                Notification::make()->title('Stabla su registrovana bez lokacije.')->success()->send();
            }),
        ];
    }
}
