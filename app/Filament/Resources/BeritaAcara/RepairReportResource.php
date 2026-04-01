<?php

namespace App\Filament\Resources\BeritaAcara;

use App\Filament\Resources\BeritaAcara\Pages\ListRepairReports;
use App\Filament\Resources\BeritaAcara\Tables\RepairReportTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RepairReportResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentMagnifyingGlass;

    protected static ?string $navigationLabel = 'Perbaikan';

    protected static string|UnitEnum|null $navigationGroup = 'Trandist Mobile - Berita Acara';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'berita-acara/perbaikan';

    protected static ?string $modelLabel = 'Perbaikan';

    protected static ?string $pluralModelLabel = 'Perbaikan';

    protected static ?string $breadcrumb = 'Perbaikan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(RepairReportTable::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return RepairReportTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('meterRepair');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRepairReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }
}
