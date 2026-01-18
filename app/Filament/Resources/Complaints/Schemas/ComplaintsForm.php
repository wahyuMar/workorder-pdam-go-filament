<?php

namespace App\Filament\Resources\Complaints\Schemas;

use App\Models\ComplaintType;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ComplaintsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Pengaduan')
                    ->schema([
                        TextInput::make('no_pengaduan')
                            ->label('No. Pengaduan')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto Generated'),
                        TextInput::make('no_sambungan')
                            ->label('No Sambungan')
                            ->maxLength(255),
                        DateTimePicker::make('tanggal')
                            ->label('Tanggal')
                            ->default(now())
                            ->required(),
                    ])
                    ->columns(3),
                Section::make('Data Pelanggan')
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('alamat')
                            ->label('Alamat')
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('no_hp')
                            ->label('No. HP')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('no_ktp')
                            ->label('No KTP')
                            ->maxLength(255),
                        Select::make('sumber')
                            ->label('Sumber')
                            ->options([
                                'website' => 'Website',
                                'kantor' => 'Kantor',
                                'sosial_media' => 'Sosial Media',
                                'telepon' => 'Telepon',
                                'mobile_apps' => 'Mobile Apps',
                            ])
                            ->searchable(),
                    ])
                    ->columns(3),
                Section::make('Detail Pengaduan')
                    ->schema([
                        Select::make('complaint_type_id')
                            ->label('Jenis Pengaduan')
                            ->options(ComplaintType::where('is_active', true)->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                        TextInput::make('judul_pengaduan')
                            ->label('Judul Pengaduan')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Textarea::make('isi_pengaduan')
                            ->label('Isi Pengaduan')
                            ->required()
                            ->rows(4)
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        FileUpload::make('foto')
                            ->label('Multi Foto')
                            ->image()
                            ->multiple()
                            ->maxFiles(5)
                            ->directory('complaints')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Status')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->default('pending')
                            ->required(),
                        Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default('medium')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
