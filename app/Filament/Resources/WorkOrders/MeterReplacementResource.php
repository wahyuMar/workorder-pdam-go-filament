<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\WorkOrders\Pages\ListMeterReplacement;
use App\Filament\Resources\WorkOrders\Tables\MeterReplacementTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MeterReplacementResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::ArrowsRightLeft;

    protected static ?string $navigationLabel = 'Meter Replacement';
    protected static string|UnitEnum|null $navigationGroup = 'Work Order';
    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'work-orders/meter-replacement';
    protected static ?string $modelLabel = 'Meter Replacement';
    protected static ?string $pluralModelLabel = 'Meter Replacement';
    protected static ?string $breadcrumb = 'Meter Replacement';

    public static function table(Table $table): Table
    {
        return MeterReplacementTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('followUps', function ($query) {
                $query->where('work_order', 'Ganti Meter');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeterReplacement::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
