<?php

namespace App\Filament\Resources\MaterialAndServices\Schemas;

use App\Enums\MaterialAndServiceCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MaterialAndServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('category')
                    ->options(MaterialAndServiceCategory::class)
                    ->required(),
                TextInput::make('unit'),
                Toggle::make('is_deletable')
                    ->required(),
                Toggle::make('is_service')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->default(0.0)
                    ->prefix('$'),
            ]);
    }
}
