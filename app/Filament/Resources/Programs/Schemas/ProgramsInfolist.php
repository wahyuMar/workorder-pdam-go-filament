<?php

namespace App\Filament\Resources\Programs\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProgramsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Program Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Program Name'),
                        IconEntry::make('is_active')
                            ->label('Is Active')
                            ->boolean(),
                    ])
                    ->columns(1),
            ]);
    }
}
