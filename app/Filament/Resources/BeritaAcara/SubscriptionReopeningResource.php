<?php

namespace App\Filament\Resources\BeritaAcara;

use App\Filament\Resources\BeritaAcara\Pages\ListSubscriptionReopenings;
use App\Filament\Resources\BeritaAcara\Tables\SubscriptionReopeningTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SubscriptionReopeningResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentMagnifyingGlass;

    protected static ?string $navigationLabel = 'Serah Terima Buka Kembali';
    protected static string|UnitEnum|null $navigationGroup = 'Trandist Mobile - Berita Acara';
    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'berita-acara/subscription-reopening';
    protected static ?string $modelLabel = 'Serah Terima Buka Kembali';
    protected static ?string $pluralModelLabel = 'Serah Terima Buka Kembali';
    protected static ?string $breadcrumb = 'Serah Terima Buka Kembali';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(SubscriptionReopeningTable::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return SubscriptionReopeningTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('meterReopening'); // Show only complaints that have SPBK
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionReopenings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }
}
