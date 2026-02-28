<?php

namespace App\Filament\Resources\Confirmations;

use App\Filament\Resources\Confirmations\Pages\ListConfirmNameChange;
use App\Filament\Resources\Confirmations\Tables\ConfirmNameChangeTable;
use App\Models\MeterNameChange;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ConfirmNameChangeResource extends Resource
{
    protected static ?string $model = MeterNameChange::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::CheckCircle;

    protected static ?string $navigationLabel = 'Ubah Nama';
    protected static string|UnitEnum|null $navigationGroup = 'Confirmation';
    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'confirmations/name-change';
    protected static ?string $modelLabel = 'Ubah Nama';
    protected static ?string $pluralModelLabel = 'Ubah Nama';
    protected static ?string $breadcrumb = 'Ubah Nama';

    public static function table(Table $table): Table
    {
        return ConfirmNameChangeTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListConfirmNameChange::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
