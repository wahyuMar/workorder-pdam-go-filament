<?php

namespace App\Filament\Resources\Provinces\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProvincesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('name')
                            ->label('Province Name')
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
