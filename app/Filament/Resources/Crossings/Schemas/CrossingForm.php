<?php

namespace App\Filament\Resources\Crossings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CrossingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('IDR'),
            ]);
    }
}
