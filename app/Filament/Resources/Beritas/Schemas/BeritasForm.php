<?php

namespace App\Filament\Resources\Beritas\Schemas;

use App\Enums\BeritaKategori;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BeritasForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('News Details')
                    ->schema([
                        TextInput::make('judul')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Select::make('kategori')
                            ->label('Category')
                            ->options(BeritaKategori::options())
                            ->required(),
                        Toggle::make('is_publish')
                            ->label('Published'),
                    ])
                    ->columns(2),
                Section::make('Content')
                    ->schema([
                        RichEditor::make('content')
                            ->label('Content')
                            ->required()
                            ->columnSpanFull(),
                    ]),
                Section::make('Media')
                    ->schema([
                        FileUpload::make('image')
                            ->label('Thumbnail / Image')
                            ->image()
                            ->disk('public')
                            ->directory('beritas/images')
                            ->visibility('public')
                            ->nullable(),
                        FileUpload::make('file_lampiran')
                            ->label('Attachment')
                            ->disk('public')
                            ->directory('beritas/attachments')
                            ->visibility('public')
                            ->nullable(),
                    ])
                    ->columns(2),
            ]);
    }
}
