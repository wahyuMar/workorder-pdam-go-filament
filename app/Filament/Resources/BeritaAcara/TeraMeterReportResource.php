<?php

namespace App\Filament\Resources\BeritaAcara;

use App\Filament\Resources\BeritaAcara\Pages\ListTeraMeterReports;
use App\Filament\Resources\BeritaAcara\Tables\TeraMeterReportTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class TeraMeterReportResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentMagnifyingGlass;

    protected static ?string $navigationLabel = 'Tera Meter';

    protected static string|UnitEnum|null $navigationGroup = 'Trandist Mobile - Berita Acara';

    protected static ?int $navigationSort = 7;

    protected static ?string $slug = 'berita-acara/tera-meter';

    protected static ?string $modelLabel = 'Tera Meter';

    protected static ?string $pluralModelLabel = 'Tera Meter';

    protected static ?string $breadcrumb = 'Tera Meter';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(TeraMeterReportTable::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return TeraMeterReportTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('meterTera');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTeraMeterReports::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }
}
