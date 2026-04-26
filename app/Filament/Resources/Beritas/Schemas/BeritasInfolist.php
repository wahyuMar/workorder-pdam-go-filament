<?php

namespace App\Filament\Resources\Beritas\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BeritasInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('News Details')
                    ->schema([
                        TextEntry::make('judul')
                            ->label('Title')
                            ->columnSpanFull(),
                        TextEntry::make('kategori')
                            ->label('Category')
                            ->badge(),
                        IconEntry::make('is_publish')
                            ->label('Published')
                            ->boolean(),
                        TextEntry::make('author.name')
                            ->label('Created By'),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                    ])
                    ->columns(2),
                Section::make('Content')
                    ->schema([
                        TextEntry::make('content')
                            ->label('Content')
                            ->html()
                            ->columnSpanFull(),
                    ]),
                Section::make('Media')
                    ->schema([
                        ImageEntry::make('image')
                            ->label('Thumbnail')
                            ->disk('public'),
                        TextEntry::make('file_lampiran')
                            ->label('Attachment'),
                    ])
                    ->columns(2),
            ]);
    }
}
