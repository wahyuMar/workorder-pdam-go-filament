<?php

namespace App\Filament\Resources\Surveys\Tables;

use App\Helper\MapHelper;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
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
                TextColumn::make('customerRegistration.no_surat')
                    ->label('No Registrasi')
                    ->searchable(),
                IconColumn::make('lokasi_pipa_distribusi_lat')
                    ->label('Lokasi Pipa Dist.')
                    ->icon(Heroicon::MapPin)
                    ->color(Color::Red)
                    ->url(fn($record) => MapHelper::generateGoogleMapLink($record->lokasi_pipa_distribusi_lat, $record->lokasi_pipa_distribusi_long))
                    ->openUrlInNewTab(),
                TextColumn::make('panjang_pipa_sr')
                    ->label('Panjang Pipa SR')
                    ->numeric()
                    ->suffix(' m')
                    ->sortable(),
                IconColumn::make('lokasi_sr_lat')
                    ->label('Lokasi SR')
                    ->icon(Heroicon::MapPin)
                    ->color(Color::Red)
                    ->url(fn($record) => MapHelper::generateGoogleMapLink($record->lokasi_sr_lat, $record->lokasi_sr_long))
                    ->openUrlInNewTab(),
                TextColumn::make('panjang_rabatan')
                    ->label('Panjang Rabatan')
                    ->numeric()
                    ->suffix(' m')
                    ->sortable(),
                IconColumn::make('lokasi_rabatan_lat')
                    ->label('Lokasi Rabatan')
                    ->icon(Heroicon::MapPin)
                    ->color(Color::Red)
                    ->url(fn($record) => MapHelper::generateGoogleMapLink($record->lokasi_rabatan_lat, $record->lokasi_rabatan_long))
                    ->openUrlInNewTab(),
                TextColumn::make('panjang_crossing')
                    ->label('Panjang Crossing')
                    ->numeric()
                    ->suffix(' m')
                    ->sortable(),
                IconColumn::make('lokasi_crossing_lat')
                    ->label('Lokasi Crossing')
                    ->icon(Heroicon::MapPin)
                    ->color(Color::Red)
                    ->url(fn($record) => MapHelper::generateGoogleMapLink($record->lokasi_crossing_lat, $record->lokasi_crossing_long))
                    ->openUrlInNewTab(),
                TextColumn::make('crossing.name')
                    ->label('Jenis Crossing')
                    ->searchable(),
                TextColumn::make('tanggal_survey')
                    ->dateTime('Y-m-d')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('createdBy.name')
                    ->label('Disubmit Oleh'),
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
