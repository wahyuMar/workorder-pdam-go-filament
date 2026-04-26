<?php

namespace App\Filament\Resources\Beritas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BeritasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('Thumbnail')
                    ->disk('public')
                    ->circular()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('judul')
                    ->label('Title')
                    ->searchable()
                    ->sortable()
                    ->limit(60),
                TextColumn::make('kategori')
                    ->label('Category')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_publish')
                    ->label('Published')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('author.name')
                    ->label('Created By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('kategori')
                    ->label('Category')
                    ->options(\App\Enums\BeritaKategori::options()),
                TernaryFilter::make('is_publish')
                    ->label('Published'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
