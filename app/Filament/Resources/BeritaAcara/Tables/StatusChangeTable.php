<?php

namespace App\Filament\Resources\BeritaAcara\Tables;

use App\Models\StatusChange;
use App\Models\Complaint;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class StatusChangeTable
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
                TextColumn::make('meterRateChange.no_sput')
                    ->label('No. SPUT')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Action::make('view_baus')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record) => StatusChange::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        $baus = StatusChange::where('complaint_id', $record->id)->first();

                        return [
                            TextInput::make('no_baus')
                                ->label('No. BAUS')
                                ->default($baus?->no_baus)
                                ->disabled(),
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default($baus?->no_sambungan)
                                ->disabled(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($baus?->nama)
                                ->disabled(),
                            Textarea::make('alamat')
                                ->label('Alamat')
                                ->default($baus?->alamat)
                                ->disabled()
                                ->rows(2),
                            TextInput::make('lokasi')
                                ->label('Lokasi')
                                ->default($baus?->lokasi)
                                ->disabled(),
                            Textarea::make('catatan')
                                ->label('Catatan')
                                ->default($baus?->catatan)
                                ->disabled()
                                ->rows(2),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default($baus?->tanggal)
                                ->disabled(),
                        ];
                    })
                    ->modalHeading('Detail BAUS')
                    ->modalWidth('2xl')
                    ->modalDescription('Berita Acara Ubah Status (SPUT Ganti Tarif)')
                    ->closeModalByClickingAway(false),
                Action::make('create_baus')
                    ->label('Create BAUS')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! StatusChange::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        return [
                            TextInput::make('no_baus')
                                ->label('No. BAUS')
                                ->disabled()
                                ->dehydrated(false)
                                ->default(StatusChange::peekNextNoBAUS()),
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
                                ->directory('berita-acara/status-change'),
                            FileUpload::make('foto_sesudah')
                                ->label('Foto Sesudah')
                                ->directory('berita-acara/status-change'),
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
                        StatusChange::create([
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
                            ->title('BAUS Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->after(function () {
                        // Refresh table to update button visibility
                    })
                    ->modalHeading('Buat Berita Acara Ubah Status')
                    ->modalWidth('2xl'),
            ])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('no_baus')
                ->label('No. BAUS')
                ->disabled()
                ->dehydrated(false)
                ->default(function () {
                    return StatusChange::peekNextNoBAUS();
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
            Select::make('jenis_rumah')
                ->label('Jenis Rumah')
                ->options([
                    'Rumah' => 'Rumah',
                    'Ruko' => 'Ruko',
                    'Kantor' => 'Kantor',
                    'Toko' => 'Toko',
                    'Pabrik' => 'Pabrik',
                    'Lainnya' => 'Lainnya',
                ]),
            TextInput::make('jumlah_kran')
                ->label('Jumlah Kran')
                ->numeric(),
            TextInput::make('daya_listrik')
                ->label('Daya Listrik')
                ->numeric(),
            FileUpload::make('verifikasi_ktp')
                ->label('Verifikasi KTP')
                ->directory('berita-acara/status-change'),
            FileUpload::make('verifikasi_kk')
                ->label('Verifikasi KK')
                ->directory('berita-acara/status-change'),
            FileUpload::make('verifikasi_tagihan_listrik')
                ->label('Verifikasi Tagihan Listrik')
                ->directory('berita-acara/status-change'),
            FileUpload::make('verifikasi_foto_rumah')
                ->label('Verifikasi Foto Rumah')
                ->image()
                ->directory('berita-acara/status-change'),
            Select::make('klasifikasi_sr')
                ->label('Klasifikasi SR')
                ->options([
                    'SR-1' => 'SR-1',
                    'SR-2' => 'SR-2',
                    'SR-3' => 'SR-3',
                    'SR-4' => 'SR-4',
                ]),
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
