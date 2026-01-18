<?php

namespace App\Filament\Resources\Complaints\Schemas;

use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ComplaintsInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Informasi Pengaduan')
                    ->schema([
                        TextEntry::make('no_pengaduan')
                            ->label('No. Pengaduan'),
                        TextEntry::make('tanggal')
                            ->label('Tanggal')
                            ->dateTime(),
                        TextEntry::make('no_sambungan')
                            ->label('No. Sambungan'),
                    ])
                    ->columns(3),
                Section::make('Data Pelanggan')
                    ->schema([
                        TextEntry::make('nama')
                            ->label('Nama'),
                        TextEntry::make('alamat')
                            ->label('Alamat'),
                        TextEntry::make('email')
                            ->label('Email'),
                        TextEntry::make('no_hp')
                            ->label('No. HP'),
                        TextEntry::make('no_ktp')
                            ->label('No KTP'),
                        TextEntry::make('sumber')
                            ->label('Sumber')
                            ->badge(),
                    ])
                    ->columns(3),
                Section::make('Detail Pengaduan')
                    ->schema([
                        TextEntry::make('complaintType.name')
                            ->label('Jenis Pengaduan'),
                        TextEntry::make('judul_pengaduan')
                            ->label('Judul Pengaduan')
                            ->columnSpanFull(),
                        TextEntry::make('isi_pengaduan')
                            ->label('Isi Pengaduan')
                            ->columnSpanFull(),
                        ImageEntry::make('foto')
                            ->label('Foto')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Status')
                    ->schema([
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'gray',
                                'in_progress' => 'warning',
                                'resolved' => 'success',
                                'closed' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('priority')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'low' => 'gray',
                                'medium' => 'info',
                                'high' => 'warning',
                                'urgent' => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->columns(2),
                Section::make('Timestamps')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Diupdate')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
