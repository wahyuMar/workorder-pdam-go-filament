<?php

namespace App\Filament\Resources\BeritaAcara\Tables;

use App\Models\SubscriptionClosure;
use App\Models\Complaint;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SubscriptionClosureTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_pengaduan')
                    ->label('No. Pengaduan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('no_sambungan')
                    ->label('No. Sambungan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable(),
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('meterClosed.no_sptl')
                    ->label('No. SPTL')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Action::make('create_batl')
                    ->label('Create BATL')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! SubscriptionClosure::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        $sptl = $record->meterClosed;

                        return [
                            TextInput::make('no_batl')
                                ->label('No. BATL')
                                ->disabled()
                                ->dehydrated(false)
                                ->default(SubscriptionClosure::peekNextNoBATL()),
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default($sptl?->no_sambungan)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($sptl?->nama)
                                ->disabled()
                                ->dehydrated(),
                            Textarea::make('alamat')
                                ->label('Alamat')
                                ->default($sptl?->alamat)
                                ->disabled()
                                ->dehydrated()
                                ->rows(2),
                            TextInput::make('lokasi')
                                ->label('Lokasi')
                                ->required(),
                            FileUpload::make('foto_sebelum')
                                ->label('Foto Sebelum')
                                ->image()
                                ->directory('berita-acara/subscription-closure'),
                            FileUpload::make('foto_sesudah')
                                ->label('Foto Sesudah')
                                ->image()
                                ->directory('berita-acara/subscription-closure'),
                            Textarea::make('catatan')
                                ->label('Catatan')
                                ->rows(3),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->required()
                                ->default(now()),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        SubscriptionClosure::create([
                            'complaint_id' => $record->id,
                            'no_sambungan' => $data['no_sambungan'],
                            'nama' => $data['nama'],
                            'alamat' => $data['alamat'],
                            'lokasi' => $data['lokasi'],
                            'foto_sebelum' => $data['foto_sebelum'] ?? null,
                            'foto_sesudah' => $data['foto_sesudah'] ?? null,
                            'catatan' => $data['catatan'] ?? null,
                            'tanggal' => $data['tanggal'],
                        ]);

                        Notification::make()
                            ->title('BATL Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->after(function () {
                        // Refresh table to update button visibility
                    })
                    ->modalHeading('Buat Berita Acara Tutup Langganan')
                    ->modalWidth('2xl'),
            ])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('no_batl')
                ->label('No. BATL')
                ->disabled()
                ->dehydrated(false)
                ->default(function () {
                    return SubscriptionClosure::peekNextNoBATL();
                }),
            TextInput::make('no_sambungan')
                ->label('No. Sambungan')
                ->required()
                ->searchable(),
            TextInput::make('nama')
                ->label('Nama')
                ->required(),
            Textarea::make('alamat')
                ->label('Alamat')
                ->required()
                ->rows(2),
            TextInput::make('lokasi')
                ->label('Lokasi')
                ->required(),
            FileUpload::make('foto_sebelum')
                ->label('Foto Sebelum')
                ->image()
                ->directory('berita-acara/subscription-closure'),
            FileUpload::make('foto_sesudah')
                ->label('Foto Sesudah')
                ->image()
                ->directory('berita-acara/subscription-closure'),
            Textarea::make('catatan')
                ->label('Catatan')
                ->rows(3),
            DateTimePicker::make('tanggal')
                ->label('Tanggal')
                ->required()
                ->default(now()),
        ];
    }
}
