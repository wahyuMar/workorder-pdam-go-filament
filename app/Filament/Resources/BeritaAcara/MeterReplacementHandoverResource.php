<?php

namespace App\Filament\Resources\BeritaAcara;

use App\Filament\Resources\BeritaAcara\Pages\ListMeterReplacementHandovers;
use App\Filament\Resources\BeritaAcara\Tables\MeterReplacementHandoverTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MeterReplacementHandoverResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentMagnifyingGlass;

    protected static ?string $navigationLabel = 'Serah Terima Ganti Meter';
    protected static string|UnitEnum|null $navigationGroup = 'Trandist Mobile - Berita Acara';
    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'berita-acara/meter-replacement-handover';
    protected static ?string $modelLabel = 'Serah Terima Ganti Meter';
    protected static ?string $pluralModelLabel = 'Serah Terima Ganti Meter';
    protected static ?string $breadcrumb = 'Serah Terima Ganti Meter';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(MeterReplacementHandoverTable::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return MeterReplacementHandoverTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('meterReplacement'); // Show only complaints that have SPGM
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMeterReplacementHandovers::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }
}
