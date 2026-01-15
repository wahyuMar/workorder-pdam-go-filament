<?php

namespace App\Filament\Resources\CustomerRegistrations\Schemas;

use App\Models\Program;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;


class CustomerRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pribadi')
                    ->schema([
                        TextInput::make('nama_lengkap')
                            ->label('Nama Lengkap')
                            ->required()
                            ->maxLength(255),
                        Select::make('program_id')
                            ->label('Program')
                            ->relationship('program', 'name')
                            ->options(function () {
                                return Program::whereIsActive(true)
                                    ->pluck('name', 'id')->toArray();
                            })
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
                        Toggle::make('alamat_sesuai_ktp')
                            ->label('Alamat Sesuai KTP')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                if ($state) {
                                    $set('alamat_pasang', $get('alamat_ktp'));
                                    $set('dusun_kampung_pasang', $get('dusun_kampung_ktp'));
                                    $set('rt_pasang', $get('rt_ktp'));
                                    $set('rw_pasang', $get('rw_ktp'));
                                    $set('kel_desa_pasang', $get('kel_desa_ktp'));
                                    $set('kecamatan_pasang', $get('kecamatan_ktp'));
                                    $set('kab_kota_pasang', $get('kab_kota_ktp'));
                                }
                            })
                            ->columnSpanFull(),
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
