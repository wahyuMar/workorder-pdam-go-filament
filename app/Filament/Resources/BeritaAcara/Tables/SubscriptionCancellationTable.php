<?php

namespace App\Filament\Resources\BeritaAcara\Tables;

use App\Models\SubscriptionCancellation;
use App\Models\Complaint;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class SubscriptionCancellationTable
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
                TextColumn::make('meterDisconnection.no_spcl')
                    ->label('No. SPCL')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Action::make('create_bacl')
                    ->label('Create BACL')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn ($record) => ! SubscriptionCancellation::where('complaint_id', $record->id)->exists())
                    ->form(function ($record) {
                        $spdc = $record->meterDisconnection;

                        return [
                            TextInput::make('no_bacl')
                                ->label('No. BACL')
                                ->disabled()
                                ->dehydrated(false)
                                ->default(SubscriptionCancellation::peekNextNoBACL()),
                            TextInput::make('no_sambungan')
                                ->label('No. Sambungan')
                                ->default($spdc?->no_sambungan)
                                ->disabled()
                                ->dehydrated(),
                            TextInput::make('nama')
                                ->label('Nama')
                                ->default($spdc?->nama)
                                ->disabled()
                                ->dehydrated(),
                            Textarea::make('alamat')
                                ->label('Alamat')
                                ->default($spdc?->alamat)
                                ->disabled()
                                ->dehydrated()
                                ->rows(2),
                            TextInput::make('lokasi')
                                ->label('Lokasi')
                                ->required(),
                            FileUpload::make('foto_sebelum')
                                ->label('Foto Sebelum')
                                ->image()
                                ->directory('berita-acara/subscription-cancellation'),
                            FileUpload::make('foto_sesudah')
                                ->label('Foto Sesudah')
                                ->image()
                                ->directory('berita-acara/subscription-cancellation'),
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
                        SubscriptionCancellation::create([
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
                            ->title('BACL Berhasil Dibuat')
                            ->success()
                            ->send();
                    })
                    ->after(function () {
                        // Refresh table to update button visibility
                    })
                    ->modalHeading('Buat Berita Acara Cabut Langganan')
                    ->modalWidth('2xl'),
            ])
            ->defaultSort('tanggal', 'desc');
    }

    public static function getFormSchema(): array
    {
        return [
            TextInput::make('no_bacl')
                ->label('No. BACL')
                ->disabled()
                ->dehydrated(false)
                ->default(function () {
                    return SubscriptionCancellation::peekNextNoBACL();
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
                ->directory('berita-acara/subscription-cancellation'),
            FileUpload::make('foto_sesudah')
                ->label('Foto Sesudah')
                ->image()
                ->directory('berita-acara/subscription-cancellation'),
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
