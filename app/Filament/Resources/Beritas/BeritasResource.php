<?php

namespace App\Filament\Resources\Beritas;

use App\Filament\Resources\Beritas\Pages\CreateBeritas;
use App\Filament\Resources\Beritas\Pages\EditBeritas;
use App\Filament\Resources\Beritas\Pages\ListBeritas;
use App\Filament\Resources\Beritas\Pages\ViewBeritas;
use App\Filament\Resources\Beritas\Schemas\BeritasForm;
use App\Filament\Resources\Beritas\Schemas\BeritasInfolist;
use App\Filament\Resources\Beritas\Tables\BeritasTable;
use App\Models\Berita;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class BeritasResource extends Resource
{
    protected static ?string $model = Berita::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentText;

    protected static ?string $recordTitleAttribute = 'judul';

    protected static ?string $navigationLabel = 'News';

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return BeritasForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BeritasInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BeritasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBeritas::route('/'),
            'create' => CreateBeritas::route('/create'),
            'view' => ViewBeritas::route('/{record}'),
            'edit' => EditBeritas::route('/{record}/edit'),
        ];
    }
}
