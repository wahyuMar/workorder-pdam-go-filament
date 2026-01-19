<?php

namespace App\Filament\Resources\ClampSaddles;

use App\Filament\Resources\ClampSaddles\Pages\CreateClampSaddle;
use App\Filament\Resources\ClampSaddles\Pages\EditClampSaddle;
use App\Filament\Resources\ClampSaddles\Pages\ListClampSaddles;
use App\Filament\Resources\ClampSaddles\Schemas\ClampSaddleForm;
use App\Filament\Resources\ClampSaddles\Tables\ClampSaddlesTable;
use App\Models\ClampSaddle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ClampSaddleResource extends Resource
{
    protected static ?string $model = ClampSaddle::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return ClampSaddleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ClampSaddlesTable::configure($table);
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
            'index' => ListClampSaddles::route('/'),
            'create' => CreateClampSaddle::route('/create'),
            'edit' => EditClampSaddle::route('/{record}/edit'),
        ];
    }
}
