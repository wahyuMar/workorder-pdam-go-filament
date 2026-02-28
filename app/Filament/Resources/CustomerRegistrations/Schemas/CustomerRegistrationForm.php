<?php

namespace App\Filament\Resources\CustomerRegistrations\Schemas;

use App\Models\District;
use App\Models\Program;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Dotswan\MapPicker\Fields\Map;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
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
                        Select::make('province_id_ktp')
                            ->label('Provinsi KTP')
                            ->options(fn () => Province::where('is_selectable', true)->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('regency_id_ktp', null);
                                $set('district_id_ktp', null);
                                $set('village_id_ktp', null);
                            }),
                        Select::make('regency_id_ktp')
                            ->label('Kab/Kota KTP')
                            ->options(fn (Get $get) => Regency::where('province_id', $get('province_id_ktp'))
                                ->where('is_selectable', true)
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('district_id_ktp', null);
                                $set('village_id_ktp', null);
                            })
                            ->disabled(fn (Get $get) => !$get('province_id_ktp')),
                        Select::make('district_id_ktp')
                            ->label('Kecamatan KTP')
                            ->options(fn (Get $get) => District::where('regency_id', $get('regency_id_ktp'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('village_id_ktp', null);
                            })
                            ->disabled(fn (Get $get) => !$get('regency_id_ktp')),
                        Select::make('village_id_ktp')
                            ->label('Kel/Desa KTP')
                            ->options(fn (Get $get) => Village::where('district_id', $get('district_id_ktp'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->disabled(fn (Get $get) => !$get('district_id_ktp')),
                    ])
                    ->columns(3),

                Section::make('Alamat Pasang')
                    ->schema([
                        Toggle::make('alamat_sesuai_ktp')
                            ->label('Alamat Sesuai KTP')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($state) {
                                    $set('alamat_pasang', $get('alamat_ktp'));
                                    $set('dusun_kampung_pasang', $get('dusun_kampung_ktp'));
                                    $set('rt_pasang', $get('rt_ktp'));
                                    $set('rw_pasang', $get('rw_ktp'));
                                    $set('province_id_pasang', $get('province_id_ktp'));
                                    $set('regency_id_pasang', $get('regency_id_ktp'));
                                    $set('district_id_pasang', $get('district_id_ktp'));
                                    $set('village_id_pasang', $get('village_id_ktp'));
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
                        Select::make('province_id_pasang')
                            ->label('Provinsi Pasang')
                            ->options(fn () => Province::where('is_selectable', true)->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('regency_id_pasang', null);
                                $set('district_id_pasang', null);
                                $set('village_id_pasang', null);
                            }),
                        Select::make('regency_id_pasang')
                            ->label('Kab/Kota Pasang')
                            ->options(fn (Get $get) => Regency::where('province_id', $get('province_id_pasang'))
                                ->where('is_selectable', true)
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('district_id_pasang', null);
                                $set('village_id_pasang', null);
                            })
                            ->disabled(fn (Get $get) => !$get('province_id_pasang')),
                        Select::make('district_id_pasang')
                            ->label('Kecamatan Pasang')
                            ->options(fn (Get $get) => District::where('regency_id', $get('regency_id_pasang'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(function (Set $set) {
                                $set('village_id_pasang', null);
                            })
                            ->disabled(fn (Get $get) => !$get('regency_id_pasang')),
                        Select::make('village_id_pasang')
                            ->label('Kel/Desa Pasang')
                            ->options(fn (Get $get) => Village::where('district_id', $get('district_id_pasang'))
                                ->pluck('name', 'id'))
                            ->searchable()
                            ->disabled(fn (Get $get) => !$get('district_id_pasang')),
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
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Koordinat Lokasi')
                    ->schema([
                        Map::make('location')
                            ->label('Location')
                            ->columnSpanFull()
                            ->live()
                            ->defaultLocation(
                                latitude: (float) env('DEFAULT_LATITUDE'),
                                longitude: (float) env('DEFAULT_LONGITUDE')
                            )
                            ->afterStateUpdated(function (Set $set, ?array $state): void {
                                $set('latitude', $state['lat']);
                                $set('longitude', $state['lng']);
                            })
                            ->extraControl([
                                'zoomControl' => true,
                                'detectRetina' => true,
                            ])
                            ->extraStyles([
                                'min-height: 50vh'
                            ]),
                        TextInput::make('latitude')
                            ->label('Latitude')
                            ->live()
                            ->readonly()
                            ->maxLength(255),
                        TextInput::make('longitude')
                            ->live()
                            ->label('Longitude')
                            ->readonly()
                            ->maxLength(255),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
