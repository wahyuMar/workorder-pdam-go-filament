<?php

namespace App\Filament\Resources\Regencies\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RegenciesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('province_id')
                            ->label('Province')
                            ->relationship('province', 'name')
                            ->required(),
                        TextInput::make('name')
                            ->label('Regency Name')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_selectable')
                            ->label('Is Selectable')
                            ->default(true),
                    ])
                    ->columns(1)
            ]);
    }
}
