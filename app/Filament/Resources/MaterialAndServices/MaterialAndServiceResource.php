<?php

namespace App\Filament\Resources\MaterialAndServices;

use App\Filament\Resources\MaterialAndServices\Pages\CreateMaterialAndService;
use App\Filament\Resources\MaterialAndServices\Pages\EditMaterialAndService;
use App\Filament\Resources\MaterialAndServices\Pages\ListMaterialAndServices;
use App\Filament\Resources\MaterialAndServices\Schemas\MaterialAndServiceForm;
use App\Filament\Resources\MaterialAndServices\Tables\MaterialAndServicesTable;
use App\Models\MaterialAndService;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MaterialAndServiceResource extends Resource
{
    protected static ?string $model = MaterialAndService::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return MaterialAndServiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MaterialAndServicesTable::configure($table);
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
            'index' => ListMaterialAndServices::route('/'),
            'create' => CreateMaterialAndService::route('/create'),
            'edit' => EditMaterialAndService::route('/{record}/edit'),
        ];
    }
}
