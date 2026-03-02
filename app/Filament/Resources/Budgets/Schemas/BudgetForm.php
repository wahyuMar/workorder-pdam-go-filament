<?php

namespace App\Filament\Resources\Budgets\Schemas;

use App\Enums\BudgetItemCategory;
use App\Enums\BudgetItemSubCategory;
use App\Models\MaterialAndService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BudgetForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Info Budgeting')
                    ->hiddenLabel()
                    ->schema([
                        TextInput::make('budgeting_number')
                            ->label('Nomor Budgeting')
                            ->disabled(),
                        DatePicker::make('date')
                            ->label('Tanggal Budgeting')
                            ->required()
                            ->native(false),
                        FileUpload::make('blueprint')
                            ->label('Denah Persil')
                            ->disk('public')
                            ->directory('budgets')
                            ->visibility('public')
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp', 'image/gif', 'application/pdf'])
                            ->helperText('Format yang diterima: JPG, PNG, WEBP, GIF, PDF')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Item RAB')
                    ->hiddenLabel()
                    ->schema([
                        Repeater::make('items')
                            ->label('Item RAB')
                            ->relationship()
                            ->schema([
                                Select::make('category')
                                    ->label('Kategori')
                                    ->options(BudgetItemCategory::options())
                                    ->live()
                                    ->afterStateUpdated(fn($set) => $set('sub_category', null))
                                    ->required()
                                    ->columnSpan(2),
                                Select::make('sub_category')
                                    ->label('Sub Kategori')
                                    ->options(
                                        fn($get) => filled($get('category'))
                                            ? BudgetItemSubCategory::forCategory($get('category'))
                                            : BudgetItemSubCategory::options()
                                    )
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('name')
                                    ->label('Nama Item')
                                    ->datalist(
                                        MaterialAndService::all()->pluck('name')->toArray()
                                    )
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        $item = MaterialAndService::where('name', $state)->first();
                                        if ($item) {
                                            $qty = (float) $get('quantity') ?: 1;
                                            $set('unit', $item->unit);
                                            $set('price', $item->price);
                                            $set('item_amount', $qty * (float) $item->price);
                                        }
                                    })
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('unit')
                                    ->label('Satuan')
                                    ->readOnly()
                                    ->placeholder('-'),
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        $set('item_amount', (float) $state * (float) $get('price'));
                                    }),
                                TextInput::make('price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        $set('item_amount', (float) $get('quantity') * (float) $state);
                                    })
                                    ->columnSpan(2),
                                TextInput::make('item_amount')
                                    ->label('Total Harga')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly()
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Item')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ]);
    }
}
