<?php

namespace App\Filament\Resources\Regencies\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RegenciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('province.name')->label('Province')->searchable()->sortable(),
                TextColumn::make('name')->label('Regency Name')->searchable()->sortable(),
                IconColumn::make('is_selectable')
                    ->label('Is Selectable')
                    ->boolean()
                    ->sortable()
            ])
            ->filters([
                SelectFilter::make('province')->relationship('province', 'name')
                    ->label('Province'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                ]),
            ]);
    }
}
