<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\WorkOrders\Pages\ListMeterRateChange;
use App\Filament\Resources\WorkOrders\Tables\MeterRateChangeTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MeterRateChangeResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CurrencyDollar;

    protected static ?string $navigationLabel = 'Meter Rate Change';
    protected static string|UnitEnum|null $navigationGroup = 'Work Order';
    protected static ?int $navigationSort = 7;

    protected static ?string $slug = 'work-orders/meter-rate-change';
    protected static ?string $modelLabel = 'Meter Rate Change';
    protected static ?string $pluralModelLabel = 'Meter Rate Change';
    protected static ?string $breadcrumb = 'Meter Rate Change';

    public static function table(Table $table): Table
    {
        return MeterRateChangeTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('followUps', function ($query) {
                $query->where('work_order', 'Ganti Tarif');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeterRateChange::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
