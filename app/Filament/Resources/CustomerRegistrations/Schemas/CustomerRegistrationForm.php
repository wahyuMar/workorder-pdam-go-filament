<?php

namespace App\Filament\Resources\CustomerRegistrations\Schemas;

use Filament\Schemas\Components\DateTimePicker;
use Filament\Schemas\Components\FileUpload;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Select;
use Filament\Schemas\Components\Textarea;
use Filament\Schemas\Components\TextInput;
use Filament\Schemas\Schema;

class CustomerRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Surat')
                    ->schema([
                        TextInput::make('no_surat')
                            ->label('No Surat')
                            ->maxLength(255),
                        DateTimePicker::make('tanggal')
                            ->label('Tanggal')
                            ->default(now()),
                    ])
                    ->columns(2),

                Section::make('Data Pribadi')
                    ->schema([
                        TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Select::make('program')
                            ->label('Program')
                            ->options([
                                'Program A' => 'Program A',
                                'Program B' => 'Program B',
                                'Program C' => 'Program C',
                            ])
                            ->searchable(),
                        TextInput::make('no_ktp')
                            ->label('No KTP')
                            ->maxLength(255),
                        TextInput::make('no_kk')
                            ->label('No KK')
                            ->maxLength(255),
                        Textarea::make('pekerjaan')
                            ->label('Pekerjaan')
                            ->rows(3),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),
                        TextInput::make('no_telp')
                            ->label('No Telp')
                            ->tel()
                            ->maxLength(255),
                        TextInput::make('no_hp')
                            ->label('No HP')
                            ->tel()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Section::make('Alamat KTP')
                    ->schema([
                        Textarea::make('alamat_ktp')
                            ->label('Alamat KTP')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('dusun_kampung_ktp')
                            ->label('Dusun / Kampung KTP')
                            ->maxLength(255),
                        TextInput::make('rt_ktp')
                            ->label('RT KTP')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('rw_ktp')
                            ->label('RW KTP')
                            ->numeric()
                            ->minValue(0),
                        Select::make('kel_desa_ktp')
                            ->label('Kel/Desa KTP')
                            ->options([
                                'Desa 1' => 'Desa 1',
                                'Desa 2' => 'Desa 2',
                                'Kelurahan 1' => 'Kelurahan 1',
                            ])
                            ->searchable(),
                        Select::make('kecamatan_ktp')
                            ->label('Kecamatan KTP')
                            ->options([
                                'Kecamatan 1' => 'Kecamatan 1',
                                'Kecamatan 2' => 'Kecamatan 2',
                            ])
                            ->searchable(),
                        TextInput::make('kab_kota_ktp')
                            ->label('Kab/Kota KTP')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Alamat Pasang')
                    ->schema([
                        Textarea::make('alamat_pasang')
                            ->label('Alamat Pasang')
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('dusun_kampung_pasang')
                            ->label('Dusun / Kampung Pasang')
                            ->maxLength(255),
                        TextInput::make('rt_pasang')
                            ->label('RT Pasang')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('rw_pasang')
                            ->label('RW Pasang')
                            ->numeric()
                            ->minValue(0),
                        Select::make('kel_desa_pasang')
                            ->label('Kel/Desa Pasang')
                            ->options([
                                'Desa 1' => 'Desa 1',
                                'Desa 2' => 'Desa 2',
                                'Kelurahan 1' => 'Kelurahan 1',
                            ])
                            ->searchable(),
                        Select::make('kecamatan_pasang')
                            ->label('Kecamatan Pasang')
                            ->options([
                                'Kecamatan 1' => 'Kecamatan 1',
                                'Kecamatan 2' => 'Kecamatan 2',
                            ])
                            ->searchable(),
                        TextInput::make('kab_kota_pasang')
                            ->label('Kab/Kota Pasang')
                            ->maxLength(255),
                    ])
                    ->columns(3),

                Section::make('Data Rumah & Utilitas')
                    ->schema([
                        TextInput::make('jumlah_penghuni_tetap')
                            ->label('Jumlah Penghuni Tetap')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('jumlah_penghuni_tidak_tetap')
                            ->label('Jumlah Penghuni Tidak Tetap')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('jumlah_kran_air_minum')
                            ->label('Jumlah Kran Air Minum')
                            ->numeric()
                            ->minValue(0),
                        Select::make('jenis_rumah')
                            ->label('Jenis Rumah')
                            ->options([
                                'Permanen' => 'Permanen',
                                'Semi Permanen' => 'Semi Permanen',
                                'Non Permanen' => 'Non Permanen',
                            ])
                            ->searchable(),
                        TextInput::make('jumlah_kran')
                            ->label('Jumlah Kran')
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('daya_listrik')
                            ->label('Daya Listrik (Watt)')
                            ->numeric()
                            ->minValue(0)
                            ->suffix('Watt'),
                    ])
                    ->columns(3),

                Section::make('Upload Dokumen')
                    ->schema([
                        FileUpload::make('upload_ktp')
                            ->label('Upload KTP')
                            ->image()
                            ->disk('public')
                            ->directory('customer-registrations/ktp')
                            ->maxSize(2048)
                            ->visibility('public'),
                        FileUpload::make('upload_kk')
                            ->label('Upload KK')
                            ->image()
                            ->disk('public')
                            ->directory('customer-registrations/kk')
                            ->maxSize(2048)
                            ->visibility('public'),
                        FileUpload::make('upload_tagihan_listrik')
                            ->label('Upload Tagihan Listrik')
                            ->image()
                            ->disk('public')
                            ->directory('customer-registrations/tagihan-listrik')
                            ->maxSize(2048)
                            ->visibility('public'),
                        FileUpload::make('upload_foto_rumah')
                            ->label('Upload Foto Rumah')
                            ->image()
                            ->disk('public')
                            ->directory('customer-registrations/foto-rumah')
                            ->maxSize(2048)
                            ->visibility('public'),
                    ])
                    ->columns(2),

                Section::make('Koordinat Lokasi')
                    ->schema([
                        TextInput::make('lat')
                            ->label('Latitude')
                            ->maxLength(255)
                            ->placeholder('-6.200000'),
                        TextInput::make('lang')
                            ->label('Longitude')
                            ->maxLength(255)
                            ->placeholder('106.816666'),
                    ])
                    ->columns(2),
            ]);
    }
}
