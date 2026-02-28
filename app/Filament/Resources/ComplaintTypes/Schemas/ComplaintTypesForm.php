<?php

namespace App\Filament\Resources\ComplaintTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ComplaintTypesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Complaint Type Details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Complaint Type Name')
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Is Active')
                    ])
                    ->columns(2),
            ]);
    }
}
