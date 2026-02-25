<?php

namespace App\Filament\Resources\Confirmations;

use App\Filament\Resources\Confirmations\Pages\ListConfirmRateChange;
use App\Filament\Resources\Confirmations\Tables\ConfirmRateChangeTable;
use App\Models\MeterRateChange;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ConfirmRateChangeResource extends Resource
{
    protected static ?string $model = MeterRateChange::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CheckCircle;

    protected static ?string $navigationLabel = 'Ganti Tarif';
    protected static string|UnitEnum|null $navigationGroup = 'Confirmation';
    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'confirmations/rate-change';
    protected static ?string $modelLabel = 'Ganti Tarif';
    protected static ?string $pluralModelLabel = 'Ganti Tarif';
    protected static ?string $breadcrumb = 'Ganti Tarif';

    public static function table(Table $table): Table
    {
        return ConfirmRateChangeTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->where('is_confirmed', false);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConfirmRateChange::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
