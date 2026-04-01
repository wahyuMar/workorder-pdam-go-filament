<?php

namespace App\Filament\Resources\WorkOrders;

use App\Filament\Resources\WorkOrders\Pages\ListTeraMeters;
use App\Filament\Resources\WorkOrders\Tables\TeraMeterTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TeraMeterResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::WrenchScrewdriver;

    protected static ?string $navigationLabel = 'Tera Meter';

    protected static string|UnitEnum|null $navigationGroup = 'Work Order';

    protected static ?int $navigationSort = 9;

    protected static ?string $slug = 'work-orders/tera-meter';

    protected static ?string $modelLabel = 'Tera Meter';

    protected static ?string $pluralModelLabel = 'Tera Meter';

    protected static ?string $breadcrumb = 'Tera Meter';

    public static function table(Table $table): Table
    {
        return TeraMeterTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('followUps', function ($query) {
                $query->where('work_order', 'Tera Meter');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeraMeters::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
