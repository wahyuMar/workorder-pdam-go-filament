<?php

namespace App\Filament\Resources\Crossings;

use App\Filament\Resources\Crossings\Pages\CreateCrossing;
use App\Filament\Resources\Crossings\Pages\EditCrossing;
use App\Filament\Resources\Crossings\Pages\ListCrossings;
use App\Filament\Resources\Crossings\Schemas\CrossingForm;
use App\Filament\Resources\Crossings\Tables\CrossingsTable;
use App\Models\Crossing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CrossingResource extends Resource
{
    protected static ?string $model = Crossing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 17;

    public static function form(Schema $schema): Schema
    {
        return CrossingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CrossingsTable::configure($table);
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
            'index' => ListCrossings::route('/'),
            'create' => CreateCrossing::route('/create'),
            'edit' => EditCrossing::route('/{record}/edit'),
        ];
    }
}
