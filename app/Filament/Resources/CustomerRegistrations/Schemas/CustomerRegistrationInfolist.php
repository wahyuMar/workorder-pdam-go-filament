<?php

namespace App\Filament\Resources\CustomerRegistrations\Schemas;

use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class CustomerRegistrationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Surat')
                    ->schema([
                        TextEntry::make('no_surat')
                            ->label('No Surat'),
                        TextEntry::make('tanggal')
                            ->label('Tanggal')
                            ->dateTime('d M Y H:i'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Data Pribadi')
                    ->schema([
                        TextEntry::make('nama_lengkap')
                            ->label('Nama Lengkap'),
                        TextEntry::make('program.name')
                            ->label('Program')
                            ->badge(),
                        TextEntry::make('no_ktp')
                            ->label('No KTP'),
                        TextEntry::make('no_kk')
                            ->label('No KK'),
                        TextEntry::make('pekerjaan')
                            ->label('Pekerjaan')
                            ->columnSpanFull(),
                        TextEntry::make('email')
                            ->label('Email')
                            ->copyable(),
                        TextEntry::make('no_telp')
                            ->label('No Telp')
                            ->copyable(),
                        TextEntry::make('no_hp')
                            ->label('No HP')
                            ->copyable(),
                    ])
                    ->columns(2),

                Section::make('Alamat KTP')
                    ->schema([
                        TextEntry::make('alamat_ktp')
                            ->label('Alamat KTP')
                            ->columnSpanFull(),
                        TextEntry::make('dusun_kampung_ktp')
                            ->label('Dusun / Kampung KTP'),
                        TextEntry::make('rt_ktp')
                            ->label('RT KTP'),
                        TextEntry::make('rw_ktp')
                            ->label('RW KTP'),
                        TextEntry::make('villageKtp.name')
                            ->label('Kel/Desa KTP'),
                        TextEntry::make('districtKtp.name')
                            ->label('Kecamatan KTP'),
                        TextEntry::make('regencyKtp.name')
                            ->label('Kab/Kota KTP'),
                    ])
                    ->columns(3),

                Section::make('Alamat Pasang')
                    ->schema([
                        TextEntry::make('alamat_pasang')
                            ->label('Alamat Pasang')
                            ->columnSpanFull(),
                        TextEntry::make('dusun_kampung_pasang')
                            ->label('Dusun / Kampung Pasang'),
                        TextEntry::make('rt_pasang')
                            ->label('RT Pasang'),
                        TextEntry::make('rw_pasang')
                            ->label('RW Pasang'),
                        TextEntry::make('villagePasang.name')
                            ->label('Kel/Desa Pasang'),
                        TextEntry::make('districtPasang.name')
                            ->label('Kecamatan Pasang'),
                        TextEntry::make('regencyPasang.name')
                            ->label('Kab/Kota Pasang'),
                    ])
                    ->columns(3),

                Section::make('Data Rumah & Utilitas')
                    ->schema([
                        TextEntry::make('jumlah_penghuni_tetap')
                            ->label('Jumlah Penghuni Tetap')
                            ->numeric(),
                        TextEntry::make('jumlah_penghuni_tidak_tetap')
                            ->label('Jumlah Penghuni Tidak Tetap')
                            ->numeric(),
                        TextEntry::make('jumlah_kran_air_minum')
                            ->label('Jumlah Kran Air Minum')
                            ->numeric(),
                        TextEntry::make('jenis_rumah')
                            ->label('Jenis Rumah')
                            ->badge(),
                        TextEntry::make('jumlah_kran')
                            ->label('Jumlah Kran')
                            ->numeric(),
                        TextEntry::make('daya_listrik')
                            ->label('Daya Listrik (Watt)')
                            ->numeric()
                            ->suffix(' Watt'),
                    ])
                    ->columns(3),

                Section::make('Upload Dokumen')
                    ->schema([
                        ImageEntry::make('upload_ktp')
                            ->label('Upload KTP')
                            ->disk('public'),
                        ImageEntry::make('upload_kk')
                            ->label('Upload KK')
                            ->disk('public'),
                        ImageEntry::make('upload_tagihan_listrik')
                            ->label('Upload Tagihan Listrik')
                            ->disk('public'),
                        ImageEntry::make('upload_foto_rumah')
                            ->label('Upload Foto Rumah')
                            ->disk('public'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                Section::make('Koordinat Lokasi')
                    ->schema([
                        Placeholder::make('location')
                            ->label('Location')
                            ->content(
                                fn ($record) => new HtmlString("
                                <embed
                                    src='https://maps.google.com/maps?q={$record->latitude},{$record->longitude}&hl=en&z=16&output=embed'
                                    type='application/pdf'
                                    width='100%'
                                    height='500px'
                                />
                            "))
                            ->columnSpanFull(),
                        TextEntry::make('latitude')
                            ->label('Latitude')
                            ->copyable(),
                        TextEntry::make('longitude')
                            ->label('Longitude')
                            ->copyable(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }
}
