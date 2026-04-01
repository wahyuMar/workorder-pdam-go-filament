<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\WorkOrders\Pages\ListRepairs;
use App\Filament\Resources\WorkOrders\Tables\RepairTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RepairResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::WrenchScrewdriver;

    protected static ?string $navigationLabel = 'Perbaikan';

    protected static string|UnitEnum|null $navigationGroup = 'Work Order';

    protected static ?int $navigationSort = 8;

    protected static ?string $slug = 'work-orders/perbaikan';

    protected static ?string $modelLabel = 'Perbaikan';

    protected static ?string $pluralModelLabel = 'Perbaikan';

    protected static ?string $breadcrumb = 'Perbaikan';

    public static function table(Table $table): Table
    {
        return RepairTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('followUps', function ($query) {
                $query->where('work_order', 'Perbaikan');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRepairs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
