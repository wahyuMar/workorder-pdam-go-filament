<?php

namespace App\Filament\Resources\Confirmations\Tables;

use App\Models\MeterRateChange;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class ConfirmRateChangeTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_sput')
                    ->label('No. SPUT')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('no_sambungan')
                    ->label('No. Sambungan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable(),
                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable(),
                TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable(),
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('statusChange.no_baus')
                    ->label('No. BAUS')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('is_confirmed')
                    ->label('Status')
                    ->badge()
                    ->color(fn (bool $state): string => $state ? 'success' : 'warning')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Sudah Konfirmasi' : 'Belum Konfirmasi')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_confirmed')
                    ->label('Status Konfirmasi')
                    ->options([
                        true => 'Sudah Konfirmasi',
                        false => 'Belum Konfirmasi',
                    ])
                    ->placeholder('Semua Status'),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => route('filament.admin.resources.complaints.view', ['record' => $record->complaint_id])),
                Action::make('confirm')
                    ->label('Konfirmasi')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn ($record) => !$record->is_confirmed)
                    ->form([
                        Toggle::make('is_confirmed')
                            ->label('Setujui Perubahan Tarif')
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update(['is_confirmed' => true]);

                        Notification::make()
                            ->title('Konfirmasi Berhasil')
                            ->body('Perubahan tarif telah dikonfirmasi.')
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Konfirmasi Perubahan Tarif')
                    ->modalWidth('md'),
            ])
            ->defaultSort('tanggal', 'desc');
    }
}
