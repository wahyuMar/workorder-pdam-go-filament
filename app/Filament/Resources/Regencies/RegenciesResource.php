<?php

namespace App\Filament\Resources\Regencies;

use App\Filament\Resources\Regencies\Pages\CreateRegencies;
use App\Filament\Resources\Regencies\Pages\EditRegencies;
use App\Filament\Resources\Regencies\Pages\ListRegencies;
use App\Filament\Resources\Regencies\Schemas\RegenciesForm;
use App\Filament\Resources\Regencies\Tables\RegenciesTable;
use App\Models\Regency;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RegenciesResource extends Resource
{
    protected static ?string $model = Regency::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 12;

    public static function form(Schema $schema): Schema
    {
        return RegenciesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegenciesTable::configure($table);
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
            'index' => ListRegencies::route('/'),
            'create' => CreateRegencies::route('/create'),
            'edit' => EditRegencies::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('province')
            ->orderBy('is_selectable', 'desc')
            ->orderBy('name');
    }
}
