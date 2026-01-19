<?php

namespace App\Filament\Resources\KlasifikasiSrs;

use App\Filament\Resources\KlasifikasiSrs\Pages\CreateKlasifikasiSr;
use App\Filament\Resources\KlasifikasiSrs\Pages\EditKlasifikasiSr;
use App\Filament\Resources\KlasifikasiSrs\Pages\ListKlasifikasiSrs;
use App\Filament\Resources\KlasifikasiSrs\Schemas\KlasifikasiSrForm;
use App\Filament\Resources\KlasifikasiSrs\Tables\KlasifikasiSrsTable;
use App\Models\KlasifikasiSr;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class KlasifikasiSrResource extends Resource
{
    protected static ?string $model = KlasifikasiSr::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 16;

    public static function form(Schema $schema): Schema
    {
        return KlasifikasiSrForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KlasifikasiSrsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKlasifikasiSrs::route('/'),
            'create' => CreateKlasifikasiSr::route('/create'),
            'edit' => EditKlasifikasiSr::route('/{record}/edit'),
        ];
    }
}
