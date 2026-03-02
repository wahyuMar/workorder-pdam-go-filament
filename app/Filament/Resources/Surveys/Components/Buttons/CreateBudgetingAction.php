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
                        'name'         => 'Clamp Saddle ' . $record->clampSaddle->name . ' (' . $record->clampSaddle->brand . ')',
                        'quantity'     => 1,
                        'price'        => $price,
                        'item_amount'  => $price,
                        'unit'         => $record->clampSaddle->unit,
                    ];
                }

                // Pre-fill crossing
                if ($record->crossing) {
                    $quantity = (int) $record->panjang_crossing;
                    $price    = (float) $record->crossing->price;
                    $items[]  = [
                        'category'     => BudgetItemCategory::PekerjaanPipaInstalasi->value,
                        'sub_category' => BudgetItemSubCategory::PekerjaanTanahInstalasi->value,
                        'name'         => 'Crossing ' . $record->crossing->name,
                        'quantity'     => $quantity,
                        'price'        => $price,
                        'item_amount'  => $quantity * $price,
                        'unit'         => $record->crossing->unit,
                    ];
                }

                // Pre-fill klasifikasi SR
                if ($record->klasifikasiSr) {
                    $price   = (float) $record->klasifikasiSr->price;
                    $items[] = [
                        'category'     => BudgetItemCategory::PekerjaanPipaInstalasi->value,
                        'sub_category' => BudgetItemSubCategory::LainLainInstalasi->value,
                        'name'         => 'Klasifikasi SR ' . $record->klasifikasiSr->name,
                        'quantity'     => 1,
                        'price'        => $price,
                        'item_amount'  => $price,
                        'unit'         => '-',
                    ];
                }

                // Rabatan
                if ($record->panjang_rabatan > 0) {
                    $rabatan = MaterialAndService::find(5);
                    $items[] = [
                        'category'     => BudgetItemCategory::PekerjaanPipaInstalasi->value,
                        'sub_category' => BudgetItemSubCategory::PekerjaanTanahInstalasi->value,
                        'name'         => $rabatan->name,
                        'quantity'     => $record->panjang_rabatan,
                        'price'        => $rabatan->price,
                        'item_amount'  => $record->panjang_rabatan * $rabatan->price,
                        'unit'         => $rabatan->unit,
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
                        'unit'         => $item['unit'] ?? null,
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
