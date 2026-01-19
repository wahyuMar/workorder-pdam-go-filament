<?php

namespace App\Filament\Resources\ClampSaddles\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ClampSaddleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('brand'),
                TextInput::make('price')
                    ->required()
                    ->numeric()
                    ->prefix('IDR '),
            ]);
    }
}
