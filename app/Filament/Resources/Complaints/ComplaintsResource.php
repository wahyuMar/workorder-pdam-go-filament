<?php

namespace App\Filament\Resources\Complaints;

use App\Filament\Resources\Complaints\Pages\CreateComplaints;
use App\Filament\Resources\Complaints\Pages\EditComplaints;
use App\Filament\Resources\Complaints\Pages\ListComplaints;
use App\Filament\Resources\Complaints\Pages\ViewComplaints;
use App\Filament\Resources\Complaints\Schemas\ComplaintsForm;
use App\Filament\Resources\Complaints\Schemas\ComplaintsInfolist;
use App\Filament\Resources\Complaints\Tables\ComplaintsTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ComplaintsResource extends Resource
{
    protected static ?string $model = Complaint::class;

    // protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlineChatBubbleBottomCenterText;

    protected static ?string $recordTitleAttribute = 'title';
    protected static ?string $navigationLabel = 'Complaints';
    protected static string|UnitEnum|null $navigationGroup = 'Customer Service';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return ComplaintsForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ComplaintsInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComplaintsTable::configure($table);
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
            'index' => ListComplaints::route('/'),
            'create' => CreateComplaints::route('/create'),
            'view' => ViewComplaints::route('/{record}'),
            'edit' => EditComplaints::route('/{record}/edit'),
        ];
    }
}
