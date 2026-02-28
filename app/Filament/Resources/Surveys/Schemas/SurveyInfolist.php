<?php

namespace App\Filament\Resources\Surveys\Schemas;

use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class SurveyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Info Umum
                Section::make('Info Umum')
                    ->schema([
                        TextEntry::make('no_survey')
                            ->label('No. Survey')
                            ->placeholder('-'),
                        TextEntry::make('customerRegistration.no_surat')
                            ->label('No. Registrasi')
                            ->suffixAction(Action::make('edit')
                                ->icon(Heroicon::ArrowUpRight)
                                ->color('primary')
                                ->url(fn($record) => route('filament.admin.resources.customer-registrations.view', $record->customerRegistration)))
                            ->placeholder('-'),
                        TextEntry::make('tanggal_survey')
                            ->label('Tanggal Survey')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('klasifikasiSr.name')
                            ->label('Klasifikasi SR')
                            ->placeholder('-'),
                    ])
                    ->columns(4)
                    ->columnSpanFull(),

                // Pipa Distribusi
                Section::make('Pipa Distribusi')
                    ->schema([
                        Placeholder::make('map_pipa_distribusi')
                            ->label('Lokasi Pipa Distribusi')
                            ->content(
                                fn($record) => new HtmlString("
                                <embed
                                    src='https://maps.google.com/maps?q={$record->lokasi_pipa_distribusi_lat},{$record->lokasi_pipa_distribusi_long}&hl=en&z=16&output=embed'
                                    type='application/pdf'
                                    width='100%'
                                    height='300px'
                                />
                            ")
                            )
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(1),

                // Pipa SR dan Clamp Saddle
                Section::make('Pipa SR dan Clamp Saddle')
                    ->schema([
                        TextEntry::make('panjang_pipa_sr')
                            ->label('Panjang Pipa (SR)')
                            ->numeric()
                            ->suffix(' meter')
                            ->placeholder('-'),
                        TextEntry::make('ukuran_clamp_sadel')
                            ->label('Clamp Saddle')
                            ->placeholder('-'),
                        Placeholder::make('map_lokasi_sr')
                            ->label('Lokasi SR')
                            ->content(
                                fn($record) => new HtmlString("
                                <embed
                                    src='https://maps.google.com/maps?q={$record->lokasi_sr_lat},{$record->lokasi_sr_long}&hl=en&z=16&output=embed'
                                    type='application/pdf'
                                    width='100%'
                                    height='250'
                                />
                            ")
                            )
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpan(1),

                // Rabatan
                Section::make('Rabatan')
                    ->schema([
                        Placeholder::make('map_rabatan')
                            ->label('Lokasi Rabatan')
                            ->content(
                                fn($record) => new HtmlString("
                                <embed
                                    src='https://maps.google.com/maps?q={$record->lokasi_rabatan_lat},{$record->lokasi_rabatan_long}&hl=en&z=16&output=embed'
                                    type='application/pdf'
                                    width='100%'
                                    height='250px'
                                />
                            ")
                            )
                            ->columnSpanFull(),
                        TextEntry::make('panjang_rabatan')
                            ->label('Panjang Rabatan')
                            ->numeric()
                            ->suffix(' meter')
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->columnSpan(1),

                // Crossing
                Section::make('Crossing')
                    ->schema([
                        Placeholder::make('map_crossing')
                            ->label('Lokasi Crossing')
                            ->content(
                                fn($record) => new HtmlString("
                                <embed
                                    src='https://maps.google.com/maps?q={$record->lokasi_crossing_lat},{$record->lokasi_crossing_long}&hl=en&z=16&output=embed'
                                    type='application/pdf'
                                    width='100%'
                                    height='250px'
                                />
                            ")
                            )
                            ->columnSpanFull(),
                        TextEntry::make('panjang_crossing')
                            ->label('Panjang Crossing')
                            ->numeric()
                            ->suffix(' meter')
                            ->placeholder('-'),
                        TextEntry::make('crossing.name')
                            ->label('Jenis Crossing')
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->columnSpan(1),

                // Foto
                Section::make('Foto')
                    ->schema([
                        ImageEntry::make('foto_rumah')
                            ->label('Foto Rumah')
                            ->disk('public')
                            ->placeholder('-'),
                        ImageEntry::make('foto_penghuni')
                            ->label('Foto Penghuni')
                            ->disk('public')
                            ->placeholder('-'),
                        ImageEntry::make('foto_lokasi_wm')
                            ->label('Foto Lokasi Water Meter')
                            ->disk('public')
                            ->placeholder('-'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                // Metadata
                Section::make('Metadata')
                    ->schema([
                        TextEntry::make('createdBy.name')
                            ->label('Dibuat Oleh')
                            ->numeric(),
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui Pada')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ])->columns(2);
    }
}
