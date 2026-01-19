<?php

namespace App\Filament\Resources\Surveys\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SurveysTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_survey')
                    ->searchable(),
                TextColumn::make('lokasi_pipa_distribusi_lat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lokasi_pipa_distribusi_long')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('panjang_pipa_sr')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('ukuran_clamp_sadel')
                    ->searchable(),
                TextColumn::make('lokasi_sr_lat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lokasi_sr_long')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('foto_rumah')
                    ->searchable(),
                TextColumn::make('foto_penghuni')
                    ->searchable(),
                TextColumn::make('foto_lokasi_wm')
                    ->searchable(),
                TextColumn::make('lokasi_rabatan_lat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lokasi_rabatan_long')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('panjang_rabatan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lokasi_crossing_lat')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('lokasi_crossing_long')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('panjang_crossing')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('jenis_crossing')
                    ->searchable(),
                TextColumn::make('klasifikasi_sr')
                    ->searchable(),
                TextColumn::make('tanggal_survey')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('customer_registration_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                // ]),
            ]);
    }
}
