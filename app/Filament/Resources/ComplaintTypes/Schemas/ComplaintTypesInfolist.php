<?php

namespace App\Filament\Resources\ComplaintTypes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ComplaintTypesInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Complaint Type Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Complaint Type Name'),
                        IconEntry::make('is_active')
                            ->label('Is Active')
                            ->boolean(),
                    ])
                    ->columns(1),
            ]);
    }
}
