<?php

namespace App\Filament\Resources\Complaints\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ComplaintsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('no_pengaduan')
                    ->label('No. Pengaduan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('complaintType.name')
                    ->label('Jenis Pengaduan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('judul_pengaduan')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('no_sambungan')
                    ->label('No. Sambungan')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sumber')
                    ->label('Sumber')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'gray',
                        'in_progress' => 'warning',
                        'resolved' => 'success',
                        'closed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('follow_up_status')
                    ->label('Tindak Lanjut')
                    ->badge()
                    ->state(function ($record) {
                        return $record->followUps()->exists() ? 'Sudah' : 'Belum';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Sudah' => 'success',
                        'Belum' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(query: function ($query, string $direction) {
                        return $query->withCount('followUps')
                            ->orderBy('follow_ups_count', $direction);
                    }),
                TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('no_hp')
                    ->label('No. HP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diupdate')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('complaint_type_id')
                    ->label('Jenis Pengaduan')
                    ->relationship('complaintType', 'name'),
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
                SelectFilter::make('has_follow_up')
                    ->label('Status Tindak Lanjut')
                    ->options([
                        'yes' => 'Sudah Ditindak Lanjuti',
                        'no' => 'Belum Ditindak Lanjuti',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value'] === 'yes') {
                            return $query->has('followUps');
                        } elseif ($state['value'] === 'no') {
                            return $query->doesntHave('followUps');
                        }
                        return $query;
                    }),
                SelectFilter::make('sumber')
                    ->options([
                        'website' => 'Website',
                        'kantor' => 'Kantor',
                        'sosial_media' => 'Sosial Media',
                        'telepon' => 'Telepon',
                        'mobile_apps' => 'Mobile Apps',
                    ]),
            ])
            ->actions([
                ViewAction::make()
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                // EditAction::make(),
                // DeleteAction::make(),
            ])
            // ->bulkActions([
            //     BulkActionGroup::make([
            //         DeleteBulkAction::make(),
            //     ]),
            // ])
            ->defaultSort('tanggal', 'desc');
    }
}
