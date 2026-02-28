<?php

namespace App\Filament\Resources\Confirmations;

use App\Filament\Resources\Confirmations\Pages\ListConfirmAddressChange;
use App\Filament\Resources\Confirmations\Tables\ConfirmAddressChangeTable;
use App\Models\MeterAddressChange;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ConfirmAddressChangeResource extends Resource
{
    protected static ?string $model = MeterAddressChange::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CheckCircle;

    protected static ?string $navigationLabel = 'Ubah Alamat';
    protected static string|UnitEnum|null $navigationGroup = 'Confirmation';
    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'confirmations/address-change';
    protected static ?string $modelLabel = 'Ubah Alamat';
    protected static ?string $pluralModelLabel = 'Ubah Alamat';
    protected static ?string $breadcrumb = 'Ubah Alamat';

    public static function table(Table $table): Table
    {
        return ConfirmAddressChangeTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConfirmAddressChange::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
