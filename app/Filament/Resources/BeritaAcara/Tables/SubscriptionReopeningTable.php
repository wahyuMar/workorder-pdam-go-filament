<?php

namespace App\Filament\Resources\BeritaAcara\Tables;

use App\Models\SubscriptionReopening;
use App\Models\Complaint;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SubscriptionReopeningTable
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
                TextColumn::make('meterReopening.no_spbk')
                    ->label('No. SPBK')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Action::make('create_bast_bk')
                    ->label('Create BAST BK')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! SubscriptionReopening::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        $spbk = $record->meterReopening;

                        return [
                            TextInput::make('no_bast_bk')
                                ->label('No. BAST BK')
                                ->disabled()
                                ->dehydrated(false)
                                ->default(SubscriptionReopening::peekNextNoBASTBK()),
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default($spbk?->no_sambungan)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($spbk?->nama)
                                ->disabled()
                                ->dehydrated(),
                            Textarea::make('alamat')
                                ->label('Alamat')
                                ->default($spbk?->alamat)
                                ->disabled()
                                ->dehydrated()
                                ->rows(2),
                            TextInput::make('lokasi')
                                ->label('Lokasi')
                                ->required(),
                            FileUpload::make('foto_sebelum')
                                ->label('Foto Sebelum')
                                ->image()
                                ->directory('berita-acara/subscription-reopening'),
                            FileUpload::make('foto_sesudah')
                                ->label('Foto Sesudah')
                                ->image()
                                ->directory('berita-acara/subscription-reopening'),
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
                        SubscriptionReopening::create([
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
                            ->title('BAST BK Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->after(function () {
                        // Refresh table to update button visibility
                    })
                    ->modalHeading('Buat Berita Acara Serah Terima Buka Kembali')
                    ->modalWidth('2xl'),
            ])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('no_bast_bk')
                ->label('No. BAST BK')
                ->disabled()
                ->dehydrated(false)
                ->default(function () {
                    return SubscriptionReopening::peekNextNoBASTBK();
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
                ->directory('berita-acara/subscription-reopening'),
            FileUpload::make('foto_sesudah')
                ->label('Foto Sesudah')
                ->image()
                ->directory('berita-acara/subscription-reopening'),
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
