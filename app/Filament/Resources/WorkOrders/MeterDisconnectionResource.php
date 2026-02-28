<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\WorkOrders\Pages\ListMeterDisconnection;
use App\Filament\Resources\WorkOrders\Tables\MeterDisconnectionTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MeterDisconnectionResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::XCircle;

    protected static ?string $navigationLabel = 'Meter Disconnection';
    protected static string|UnitEnum|null $navigationGroup = 'Work Order';
    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'work-orders/meter-disconnection';
    protected static ?string $modelLabel = 'Meter Disconnection';
    protected static ?string $pluralModelLabel = 'Meter Disconnection';
    protected static ?string $breadcrumb = 'Meter Disconnection';

    public static function table(Table $table): Table
    {
        return MeterDisconnectionTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('followUps', function ($query) {
                $query->where('work_order', 'Cabut');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeterDisconnection::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
