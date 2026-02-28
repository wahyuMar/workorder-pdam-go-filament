<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\WorkOrders\Pages\ListMeterNameChange;
use App\Filament\Resources\WorkOrders\Tables\MeterNameChangeTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MeterNameChangeResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Pencil;

    protected static ?string $navigationLabel = 'Meter Name Change';
    protected static string|UnitEnum|null $navigationGroup = 'Work Order';
    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'work-orders/meter-name-change';
    protected static ?string $modelLabel = 'Meter Name Change';
    protected static ?string $pluralModelLabel = 'Meter Name Change';
    protected static ?string $breadcrumb = 'Meter Name Change';

    public static function table(Table $table): Table
    {
        return MeterNameChangeTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('followUps', function ($query) {
                $query->where('work_order', 'Ubah Nama');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeterNameChange::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
