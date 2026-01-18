<?php

namespace App\Filament\Resources\ComplaintTypes;

use App\Filament\Resources\ComplaintTypes\Pages\CreateComplaintTypes;
use App\Filament\Resources\ComplaintTypes\Pages\EditComplaintTypes;
use App\Filament\Resources\ComplaintTypes\Pages\ListComplaintTypes;
use App\Filament\Resources\ComplaintTypes\Pages\ViewComplaintTypes;
use App\Filament\Resources\ComplaintTypes\Schemas\ComplaintTypesForm;
use App\Filament\Resources\ComplaintTypes\Schemas\ComplaintTypesInfolist;
use App\Filament\Resources\ComplaintTypes\Tables\ComplaintTypesTable;
use App\Models\ComplaintType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ComplaintTypesResource extends Resource
{
    protected static ?string $model = ComplaintType::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Complaint Types';
    protected static string|UnitEnum|null $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return ComplaintTypesForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ComplaintTypesInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComplaintTypesTable::configure($table);
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
            'index' => ListComplaintTypes::route('/'),
            'create' => CreateComplaintTypes::route('/create'),
            'view' => ViewComplaintTypes::route('/{record}'),
            'edit' => EditComplaintTypes::route('/{record}/edit'),
        ];
    }
}
