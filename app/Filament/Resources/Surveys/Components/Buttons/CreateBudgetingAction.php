<?php

namespace App\Filament\Resources\Surveys\Components\Buttons;

use App\Enums\BudgetItemCategory;
use App\Enums\BudgetItemSubCategory;
use App\Helper\BudgetHelper;
use App\Models\Budget;
use App\Models\BudgetItem;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;

class CreateBudgetingAction extends Action
{
    public static function make(?string $name = null): static
    {
        $name ??= 'createBudgeting';

        return parent::make($name)
            ->label('Create Budgeting')
            ->icon(Heroicon::DocumentText)
            ->schema([
                Section::make('Info Budgeting')
                    ->hiddenLabel()
                    ->schema([
                        DatePicker::make('date')
                            ->label('Tanggal Budgeting')
                            ->required()
                            ->native(false),
                        FileUpload::make('blueprint')
                            ->label('Denah Persil')
                            ->disk('public')
                            ->directory('budgets')
                            ->visibility('public')
                            ->required(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Item Pekerjaan')
                    ->hiddenLabel()
                    ->schema([
                        Repeater::make('items')
                            ->label('Item Pekerjaan')
                            ->schema([
                                Select::make('category')
                                    ->label('Kategori')
                                    ->options(BudgetItemCategory::options())
                                    ->live()
                                    ->afterStateUpdated(fn($set) => $set('sub_category', null))
                                    ->required(),
                                Select::make('sub_category')
                                    ->label('Sub Kategori')
                                    ->options(
                                        fn($get) => filled($get('category'))
                                            ? BudgetItemSubCategory::forCategory($get('category'))
                                            : BudgetItemSubCategory::options()
                                    )
                                    ->required(),
                                TextInput::make('name')
                                    ->label('Nama Pekerjaan')
                                    ->required()
                                    ->columnSpanFull(),
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required(),
                                TextInput::make('price')
                                    ->label('Harga Satuan')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required(),
                                TextInput::make('item_amount')
                                    ->label('Total Harga')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->required(),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, $record) {
                $budget = Budget::create([
                    'budgeting_number' => BudgetHelper::generateBudgetingNumber(),
                    'date'             => $data['date'],
                    'blueprint'        => $data['blueprint'],
                    'survey_id'        => $record->id,
                    'created_by'       => auth()->id(),
                ]);

                foreach ($data['items'] as $item) {
                    BudgetItem::create([
                        'budget_id'    => $budget->id,
                        'category'     => $item['category'],
                        'sub_category' => $item['sub_category'],
                        'name'         => $item['name'],
                        'quantity'     => $item['quantity'],
                        'price'        => $item['price'],
                        'item_amount'  => $item['item_amount'],
                    ]);
                }

                Notification::make()
                    ->title('Budgeting berhasil dibuat')
                    ->success()
                    ->send();
            })
            ->modalWidth(Width::SevenExtraLarge);
    }
}
