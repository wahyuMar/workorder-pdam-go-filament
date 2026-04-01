<?php

namespace App\Filament\Resources\BeritaAcara\Tables;

use App\Models\TeraMeterReport;
use Filament\Actions\Action;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TeraMeterReportTable
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
                    ->searchable()
                    ->limit(50),
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('meterTera.no_spp')
                    ->label('No. SPP')
                    ->searchable(),
            ])
            ->actions([
                Action::make('view_bap')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->visible(fn ($record) => TeraMeterReport::where('complaint_id', $record->id)->exists())
                    ->form(function ($record): array {
                        $bap = TeraMeterReport::where('complaint_id', $record->id)->first();

                        return [
                            TextInput::make('no_bap')
                                ->label('No. BATM')
                                ->default($bap?->no_bap)
                                ->disabled(),
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default($bap?->no_sambungan)
                                ->disabled(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($bap?->nama)
                                ->disabled(),
                            Textarea::make('alamat')
                                ->label('Alamat')
                                ->default($bap?->alamat)
                                ->disabled(),
                            TextInput::make('lokasi')
                                ->label('Lokasi')
                                ->default($bap?->lokasi)
                                ->disabled(),
                            FileUpload::make('foto_sebelum')
                                ->label('Foto Tera')
                                ->default($bap?->foto_sebelum)
                                ->disabled()
                                ->disk('public'),
                            FileUpload::make('foto_sesudah')
                                ->label('Foto Pelanggan')
                                ->default($bap?->foto_sesudah)
                                ->disabled()
                                ->disk('public'),
                            Textarea::make('catatan')
                                ->label('Catatan Tera')
                                ->default($bap?->catatan)
                                ->disabled(),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->default($bap?->tanggal)
                                ->disabled(),
                        ];
                    })
                    ->modalHeading('Detail Berita Acara Tera Meter')
                    ->modalWidth('3xl')
                    ->closeModalByClickingAway(false),
                Action::make('create_bap')
                    ->label('Buat BATM')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! TeraMeterReport::where('complaint_id', $record->id)->exists())
                    ->form(function ($record): array {
                        return [
                            TextInput::make('no_bap')
                                ->label('No. BATM')
                                ->disabled()
                                ->dehydrated(false)
                                ->default(TeraMeterReport::peekNextNoBAP()),
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
                                ->dehydrated(),
                            TextInput::make('lokasi')
                                ->label('Lokasi')
                                ->default($record->alamat)
                                ->disabled()
                                ->dehydrated(),
                            FileUpload::make('foto_sebelum')
                                ->label('Foto Tera')
                                ->disk('public')
                                ->directory('berita-acara/tera-meter'),
                            FileUpload::make('foto_sesudah')
                                ->label('Foto Pelanggan')
                                ->disk('public')
                                ->directory('berita-acara/tera-meter'),
                            Textarea::make('catatan')
                                ->label('Catatan Tera')
                                ->rows(3),
                            DateTimePicker::make('tanggal')
                                ->label('Tanggal')
                                ->required()
                                ->default(now()),
                        ];
                    })
                    ->action(function ($record, array $data): void {
                        TeraMeterReport::create([
                            'complaint_id' => $record->id,
                            'no_sambungan' => $data['no_sambungan'],
                            'nama' => $data['nama'],
                            'alamat' => $data['alamat'] ?? null,
                            'lokasi' => $data['lokasi'] ?? null,
                            'foto_sebelum' => $data['foto_sebelum'] ?? null,
                            'foto_sesudah' => $data['foto_sesudah'] ?? null,
                            'catatan' => $data['catatan'] ?? null,
                            'tanggal' => $data['tanggal'],
                        ]);

                        Notification::make()
                            ->title('Berita Acara Tera Meter Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Buat Berita Acara Tera Meter')
                    ->modalWidth('3xl'),
            ])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('no_bap')
                ->label('No. BATM')
                ->disabled()
                ->dehydrated(false)
                ->default(TeraMeterReport::peekNextNoBAP()),
            TextInput::make('no_sambungan')
                ->label('No. Sambungan')
                ->disabled()
                ->dehydrated(),
            TextInput::make('nama')
                ->label('Nama')
                ->disabled()
                ->dehydrated(),
            Textarea::make('alamat')
                ->label('Alamat')
                ->disabled()
                ->dehydrated(),
            TextInput::make('lokasi')
                ->label('Lokasi')
                ->disabled()
                ->dehydrated(),
            FileUpload::make('foto_sebelum')
                ->label('Foto Tera')
                ->disk('public')
                ->directory('berita-acara/tera-meter'),
            FileUpload::make('foto_sesudah')
                ->label('Foto Pelanggan')
                ->disk('public')
                ->directory('berita-acara/tera-meter'),
            Textarea::make('catatan')
                ->label('Catatan Tera')
                ->rows(3),
            DateTimePicker::make('tanggal')
                ->label('Tanggal')
                ->required()
                ->default(now()),
        ];
    }
}
