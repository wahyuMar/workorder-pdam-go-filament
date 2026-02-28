<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\WorkOrders\Pages\ListMeterReopening;
use App\Filament\Resources\WorkOrders\Tables\MeterReopeningTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MeterReopeningResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::LockOpen;

    protected static ?string $navigationLabel = 'Meter Reopening';
    protected static string|UnitEnum|null $navigationGroup = 'Work Order';
    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'work-orders/meter-reopening';
    protected static ?string $modelLabel = 'Meter Reopening';
    protected static ?string $pluralModelLabel = 'Meter Reopening';
    protected static ?string $breadcrumb = 'Meter Reopening';

    public static function table(Table $table): Table
    {
        return MeterReopeningTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('followUps', function ($query) {
                $query->where('work_order', 'Buka Kembali');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeterReopening::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
