<?php

namespace App\Filament\Resources\Districts\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DistrictForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        Select::make('regency_id')
                            ->label('Regency')
                            ->relationship('regency', 'name')
                            ->required(),
                        TextInput::make('name')
                            ->label('District Name')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(1)
            ]);
    }
}
