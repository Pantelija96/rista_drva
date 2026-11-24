<?php

namespace App\Filament;

use App\Models\School;
use App\Models\SchoolGeneration;
use App\Models\TreeGroup;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Components\View;

final class AdminFields
{
    public static function school(bool $required = false): Select
    {
        return Select::make('school_id')->label('Škola')->options(fn () => School::orderBy('name')->get(['id', 'name', 'city'])->mapWithKeys(fn ($school) => [$school->id => $school->name.' — '.$school->city]))
            ->searchable()->required($required)->live()->afterStateUpdated(function (Set $set) {
                $set('school_generation_id', null);
                $set('tree_group_id', null);
            });
    }

    public static function generation(): Select
    {
        return Select::make('school_generation_id')->label('Generacija')->options(fn (Get $get) => SchoolGeneration::where('school_id', $get('school_id'))->get()->mapWithKeys(fn ($g) => [$g->id => $g->graduation_year.' — '.($g->name ?: $g->program)]))
            ->searchable()->live()->afterStateUpdated(fn (Set $set) => $set('tree_group_id', null));
    }

    public static function group(): Select
    {
        return Select::make('tree_group_id')->label('Grupa stabala')->options(fn (Get $get) => TreeGroup::where('school_id', $get('school_id'))->where('school_generation_id', $get('school_generation_id'))->pluck('name', 'id'))->searchable();
    }

    public static function location(): array
    {
        return [
            TextInput::make('latitude')->label('Geografska širina')->numeric()->minValue(-90)->maxValue(90)->requiredWith('longitude')->live(onBlur: true),
            TextInput::make('longitude')->label('Geografska dužina')->numeric()->minValue(-180)->maxValue(180)->requiredWith('latitude')->live(onBlur: true),
            View::make('filament.location-picker')->columnSpanFull(),
        ];
    }

    public static function image(string $field, string $label): FileUpload
    {
        return FileUpload::make($field)->label($label)->disk('public')->directory('ecosync')->visibility('public')->image()
            ->placeholder('Prevucite fotografiju ili <span class="filepond--label-action">izaberite datoteku</span>')
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->maxSize(5120)
            ->rules(['dimensions:min_width=100,min_height=100,max_width=10000,max_height=10000'])
            ->preventFilePathTampering()->helperText('JPG, PNG ili WebP, do 5 MB. Samo fotografije odobrene za javno objavljivanje.');
    }
}
