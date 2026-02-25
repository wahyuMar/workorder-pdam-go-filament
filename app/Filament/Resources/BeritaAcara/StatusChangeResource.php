<?php

namespace App\Filament\Resources\BeritaAcara;

use App\Filament\Resources\BeritaAcara\Pages\ListStatusChanges;
use App\Filament\Resources\BeritaAcara\Tables\StatusChangeTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class StatusChangeResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentMagnifyingGlass;

    protected static ?string $navigationLabel = 'Ubah Status';
    protected static string|UnitEnum|null $navigationGroup = 'Trandist Mobile - Berita Acara';
    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'berita-acara/status-change';
    protected static ?string $modelLabel = 'Ubah Status';
    protected static ?string $pluralModelLabel = 'Ubah Status';
    protected static ?string $breadcrumb = 'Ubah Status';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(StatusChangeTable::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return StatusChangeTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('meterRateChange'); // Show only complaints that have SPUT
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStatusChanges::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }
}
