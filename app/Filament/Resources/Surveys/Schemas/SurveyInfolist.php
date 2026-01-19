<?php

namespace App\Filament\Resources\Surveys\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class SurveyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('no_survey')
                    ->placeholder('-'),
                TextEntry::make('lokasi_pipa_distribusi_lat')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('lokasi_pipa_distribusi_long')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('panjang_pipa_sr')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('ukuran_clamp_sadel')
                    ->placeholder('-'),
                TextEntry::make('lokasi_sr_lat')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('lokasi_sr_long')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('foto_rumah')
                    ->placeholder('-'),
                TextEntry::make('foto_penghuni')
                    ->placeholder('-'),
                TextEntry::make('foto_lokasi_wm')
                    ->placeholder('-'),
                TextEntry::make('lokasi_rabatan_lat')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('lokasi_rabatan_long')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('panjang_rabatan')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('lokasi_crossing_lat')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('lokasi_crossing_long')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('panjang_crossing')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('jenis_crossing')
                    ->placeholder('-'),
                TextEntry::make('klasifikasi_sr')
                    ->placeholder('-'),
                TextEntry::make('tanggal_survey')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('customer_registration_id')
                    ->numeric(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
