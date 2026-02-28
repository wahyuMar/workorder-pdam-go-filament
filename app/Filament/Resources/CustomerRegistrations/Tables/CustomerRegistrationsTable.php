<?php

namespace App\Filament\Resources\CustomerRegistrations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CustomerRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_surat')
                    ->label('No Surat')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_lengkap')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('program.name')
                    ->label('Program')
                    ->searchable()
                    ->sortable()
                    ->badge(),
                TextColumn::make('no_ktp')
                    ->label('No KTP')
                    ->searchable(),
                TextColumn::make('no_hp')
                    ->label('No HP')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kab_kota_pasang')
                    ->label('Kab/Kota Pasang')
                    ->searchable(),
                TextColumn::make('kecamatan_pasang')
                    ->label('Kecamatan Pasang')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('jenis_rumah')
                    ->label('Jenis Rumah')
                    ->badge()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('program')
                    ->options([
                        'Program A' => 'Program A',
                        'Program B' => 'Program B',
                        'Program C' => 'Program C',
                    ]),
                SelectFilter::make('jenis_rumah')
                    ->options([
                        'Permanen' => 'Permanen',
                        'Semi Permanen' => 'Semi Permanen',
                        'Non Permanen' => 'Non Permanen',
                    ]),
                SelectFilter::make('kecamatan_pasang')
                    ->options([
                        'Kecamatan 1' => 'Kecamatan 1',
                        'Kecamatan 2' => 'Kecamatan 2',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
