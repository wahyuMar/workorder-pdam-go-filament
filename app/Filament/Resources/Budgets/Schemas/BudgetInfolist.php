<?php

namespace App\Filament\Resources\Budgets\Schemas;

use App\Enums\BudgetItemCategory;
use App\Enums\BudgetItemSubCategory;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BudgetInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Info Budgeting')
                    ->schema([
                        TextEntry::make('budgeting_number')
                            ->label('Nomor Budgeting')
                            ->placeholder('-'),
                        TextEntry::make('date')
                            ->label('Tanggal Budgeting')
                            ->date()
                            ->placeholder('-'),
                        TextEntry::make('survey.no_pelanggan')
                            ->label('No Pelanggan')
                            ->placeholder('-'),
                        TextEntry::make('createdBy.name')
                            ->label('Dibuat Oleh')
                            ->placeholder('-'),
                        TextEntry::make('total_amount')
                            ->label('Total RAB')
                            ->money('IDR')
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Dibuat Pada')
                            ->dateTime()
                            ->placeholder('-'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Denah Persil')
                    ->schema([
                        ViewEntry::make('blueprint')
                            ->label('Denah Persil')
                            ->view('filament.infolists.blueprint-entry')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make('Item RAB')
                    ->schema([
                        RepeatableEntry::make('items')
                            ->label('Item RAB')
                            ->schema([
                                TextEntry::make('category')
                                    ->label('Kategori')
                                    ->formatStateUsing(fn($state) => $state instanceof BudgetItemCategory ? $state->getLabel() : $state)
                                    ->columnSpan(2),
                                TextEntry::make('sub_category')
                                    ->label('Sub Kategori')
                                    ->formatStateUsing(fn($state) => $state instanceof BudgetItemSubCategory ? $state->getLabel() : $state)
                                    ->columnSpan(2),
                                TextEntry::make('name')
                                    ->label('Nama Item')
                                    ->columnSpan(2),
                                TextEntry::make('unit')
                                    ->label('Satuan')
                                    ->placeholder('-'),
                                TextEntry::make('quantity')
                                    ->label('Quantity')
                                    ->numeric(),
                                TextEntry::make('price')
                                    ->label('Harga Satuan')
                                    ->money('IDR')
                                    ->columnSpan(2),
                                TextEntry::make('item_amount')
                                    ->label('Total Harga')
                                    ->money('IDR')
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
