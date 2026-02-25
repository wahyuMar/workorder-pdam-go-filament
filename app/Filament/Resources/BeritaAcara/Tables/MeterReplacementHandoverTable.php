<?php

namespace App\Filament\Resources\BeritaAcara\Tables;

use App\Models\MeterReplacementHandover;
use App\Models\Complaint;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class MeterReplacementHandoverTable
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
                    ->label('Tanggal Pengaduan')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('meterReplacement.no_spgm')
                    ->label('No. SPGM')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Action::make('create_bast_gm')
                    ->label('Create BAST GM')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! MeterReplacementHandover::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        $spgm = $record->meterReplacement;

                        return [
                            TextInput::make('no_bast_gm')
                                ->label('No. BAST GM')
                                ->disabled()
                                ->dehydrated(false)
                                ->default(MeterReplacementHandover::peekNextNoBASTGM()),
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default($record->no_sambungan)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($record->nama)
                                ->disabled()
                                ->dehydrated(),
                            Textarea::make('alamat')
                                ->label('Alamat')
                                ->default($record->alamat)
                                ->disabled()
                                ->dehydrated()
                                ->rows(2),
                            TextInput::make('lokasi')
                                ->label('Lokasi')
                                ->required(),
                            FileUpload::make('foto_sebelum')
                                ->label('Foto Sebelum')
                                ->image()
                                ->directory('berita-acara/replacement-handover'),
                            FileUpload::make('foto_sesudah')
                                ->label('Foto Sesudah')
                                ->image()
                                ->directory('berita-acara/replacement-handover'),
                            TextInput::make('merk_wm_lama')
                                ->label('Merk WM Lama')
                                ->required(),
                            TextInput::make('no_wm_lama')
                                ->label('No WM Lama')
                                ->required(),
                            TextInput::make('merk_wm_baru')
                                ->label('Merk WM Baru')
                                ->required(),
                            TextInput::make('no_wm_baru')
                                ->label('No WM Baru')
                                ->required(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->required()
                                ->default(now()),
                        ];
                    })
                    ->action(function ($record, array $data) {
                        MeterReplacementHandover::create([
                            'complaint_id' => $record->id,
                            'no_sambungan' => $data['no_sambungan'],
                            'nama' => $data['nama'],
                            'alamat' => $data['alamat'],
                            'lokasi' => $data['lokasi'],
                            'foto_sebelum' => $data['foto_sebelum'] ?? null,
                            'foto_sesudah' => $data['foto_sesudah'] ?? null,
                            'merk_wm_lama' => $data['merk_wm_lama'],
                            'no_wm_lama' => $data['no_wm_lama'],
                            'merk_wm_baru' => $data['merk_wm_baru'],
                            'no_wm_baru' => $data['no_wm_baru'],
                            'tanggal' => $data['tanggal'],
                        ]);

                        Notification::make()
                            ->title('BAST GM Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->after(function () {
                        // Refresh table to update button visibility
                    })
                    ->modalHeading('Buat Berita Acara Serah Terima Ganti Meter')
                    ->modalWidth('2xl'),
            ])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('no_bast_gm')
                ->label('No. BAST GM')
                ->disabled()
                ->dehydrated(false)
                ->default(function () {
                    return MeterReplacementHandover::peekNextNoBASTGM();
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
                ->directory('berita-acara/replacement-handover'),
            FileUpload::make('foto_sesudah')
                ->label('Foto Sesudah')
                ->image()
                ->directory('berita-acara/replacement-handover'),
            TextInput::make('merk_wm_lama')
                ->label('Merk WM Lama')
                ->required(),
            TextInput::make('no_wm_lama')
                ->label('No WM Lama')
                ->required(),
            TextInput::make('merk_wm_baru')
                ->label('Merk WM Baru')
                ->required(),
            TextInput::make('no_wm_baru')
                ->label('No WM Baru')
                ->required(),
            DateTimePicker::make('tanggal')
                ->label('Tanggal')
                ->required()
                ->default(now()),
        ];
    }
}
