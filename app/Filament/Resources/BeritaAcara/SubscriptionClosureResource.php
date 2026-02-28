<?php

namespace App\Filament\Resources\BeritaAcara;

use App\Filament\Resources\BeritaAcara\Pages\ListSubscriptionClosures;
use App\Filament\Resources\BeritaAcara\Tables\SubscriptionClosureTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SubscriptionClosureResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentMagnifyingGlass;

    protected static ?string $navigationLabel = 'Tutup Langganan';
    protected static string|UnitEnum|null $navigationGroup = 'Trandist Mobile - Berita Acara';
    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'berita-acara/subscription-closure';
    protected static ?string $modelLabel = 'Tutup Langganan';
    protected static ?string $pluralModelLabel = 'Tutup Langganan';
    protected static ?string $breadcrumb = 'Tutup Langganan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(SubscriptionClosureTable::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return SubscriptionClosureTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('meterClosed'); // Show only complaints that have SPTL
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionClosures::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }
}
