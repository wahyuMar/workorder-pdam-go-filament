<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program Details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Program Name')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Is Active')
                    ])
                    ->columns(2),
            ]);
    }
}
