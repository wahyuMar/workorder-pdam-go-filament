<?php

namespace App\Filament\Resources\Surveys\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SurveyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('no_survey'),
                TextInput::make('lokasi_pipa_distribusi_lat')
                    ->numeric(),
                TextInput::make('lokasi_pipa_distribusi_long')
                    ->numeric(),
                TextInput::make('panjang_pipa_sr')
                    ->numeric(),
                TextInput::make('ukuran_clamp_sadel'),
                TextInput::make('lokasi_sr_lat')
                    ->numeric(),
                TextInput::make('lokasi_sr_long')
                    ->numeric(),
                TextInput::make('foto_rumah'),
                TextInput::make('foto_penghuni'),
                TextInput::make('foto_lokasi_wm'),
                TextInput::make('lokasi_rabatan_lat')
                    ->numeric(),
                TextInput::make('lokasi_rabatan_long')
                    ->numeric(),
                TextInput::make('panjang_rabatan')
                    ->numeric(),
                TextInput::make('lokasi_crossing_lat')
                    ->numeric(),
                TextInput::make('lokasi_crossing_long')
                    ->numeric(),
                TextInput::make('panjang_crossing')
                    ->numeric(),
                TextInput::make('jenis_crossing'),
                TextInput::make('klasifikasi_sr'),
                DateTimePicker::make('tanggal_survey'),
                TextInput::make('customer_registration_id')
                    ->required()
                    ->numeric(),
            ]);
    }
}
