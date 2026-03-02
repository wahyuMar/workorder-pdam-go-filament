<?php

namespace App\Filament\Resources\Surveys\Components\Buttons;

use App\Enums\BudgetItemCategory;
use App\Enums\BudgetItemSubCategory;
use App\Enums\MaterialAndServiceCategory;
use App\Helper\BudgetHelper;
use App\Models\Budget;
use App\Models\BudgetItem;
use App\Models\MaterialAndService;
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
            ->label('Create RAB')
            ->icon(Heroicon::DocumentText)
            ->fillForm(function ($record): array {
                $items = [];

                // Pre-fill clamp saddle sebagai item awal
                if ($record->clampSaddle) {
                    $price = (float) $record->clampSaddle->price;
                    $items[] = [
                        'category'     => BudgetItemCategory::PekerjaanPipaDinas->value,
                        'sub_category' => BudgetItemSubCategory::MaterialPipaDanAccDinas->value,
                        'name'         => 'Clamp Saddle : ' . $record->clampSaddle->name . ' (' . $record->clampSaddle->brand . ')',
                        'quantity'     => 1,
                        'price'        => $price,
                        'item_amount'  => $price,
                    ];
                }

                // Pre-fill crossing
                if ($record->crossing) {
                    $quantity = (int) $record->panjang_crossing;
                    $price    = (float) $record->crossing->price;
                    $items[]  = [
                        'category'     => BudgetItemCategory::PekerjaanPipaInstalasi->value,
                        'sub_category' => BudgetItemSubCategory::PekerjaanTanahInstalasi->value,
                        'name'         => 'Penggalian Crossing :' . $record->crossing->name,
                        'quantity'     => $quantity,
                        'price'        => $price,
                        'item_amount'  => $quantity * $price,
                    ];
                }

                // Pre-fill klasifikasi SR
                if ($record->klasifikasiSr) {
                    $price   = (float) $record->klasifikasiSr->price;
                    $items[] = [
                        'category'     => BudgetItemCategory::PekerjaanPipaInstalasi->value,
                        'sub_category' => BudgetItemSubCategory::LainLainInstalasi->value,
                        'name'         => 'Klasifikasi SR : ' . $record->klasifikasiSr->name,
                        'quantity'     => 1,
                        'price'        => $price,
                        'item_amount'  => $price,
                    ];
                }

                return [
                    'items' => $items,
                ];
            })
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

                Section::make('Item RAB')
                    ->hiddenLabel()
                    ->schema([
                        Repeater::make('items')
                            ->label('Item RAB')
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
                                Select::make('name')
                                    ->label('Nama Item')
                                    ->options(
                                        fn($get) => MaterialAndService::when(
                                            filled($get('category')),
                                            fn($q) => $q->where('category', MaterialAndServiceCategory::tryFrom($get('category')))
                                        )->pluck('name', 'name')
                                    )
                                    ->live()
                                    ->afterStateUpdated(function ($state, $get, $set) {
                                        $item = MaterialAndService::where('name', $state)->first();
                                        if ($item) {
                                            $qty = (float) $get('quantity') ?: 1;
                                            $set('price', $item->price);
                                            $set('item_amount', $qty * (float) $item->price);
                                        }
                                    })
                                    ->searchable()
                                    ->required(),
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
                                    }),
                                TextInput::make('item_amount')
                                    ->label('Total Harga')
                                    ->numeric()
                                    ->prefix('Rp')
                                    ->readOnly(),
                            ])
                            ->columns(6)
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Item')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),
            ])
            ->action(function (array $data, $record) {
                $totalAmount = collect($data['items'])->sum(fn($item) => (float) ($item['item_amount'] ?? 0));

                $budget = Budget::create([
                    'budgeting_number' => BudgetHelper::generateBudgetingNumber(),
                    'date'             => $data['date'],
                    'blueprint'        => $data['blueprint'],
                    'total_amount'     => $totalAmount,
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
            ->modalWidth(Width::Full);
    }
}
