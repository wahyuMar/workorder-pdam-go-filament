<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\WorkOrders\Pages\ListMeterAddressChange;
use App\Filament\Resources\WorkOrders\Tables\MeterAddressChangeTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MeterAddressChangeResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::MapPin;

    protected static ?string $navigationLabel = 'Meter Address Change';
    protected static string|UnitEnum|null $navigationGroup = 'Work Order';
    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'work-orders/meter-address-change';
    protected static ?string $modelLabel = 'Meter Address Change';
    protected static ?string $pluralModelLabel = 'Meter Address Change';
    protected static ?string $breadcrumb = 'Meter Address Change';

    public static function table(Table $table): Table
    {
        return MeterAddressChangeTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('followUps', function ($query) {
                $query->where('work_order', 'Ganti Alamat');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeterAddressChange::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
