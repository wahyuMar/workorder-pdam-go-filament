<?php

namespace App\Filament\Resources\BeritaAcara;

use App\Filament\Resources\BeritaAcara\Pages\ListSubscriptionCancellations;
use App\Filament\Resources\BeritaAcara\Tables\SubscriptionCancellationTable;
use App\Models\Complaint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SubscriptionCancellationResource extends Resource
{
    protected static ?string $model = Complaint::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentMagnifyingGlass;

    protected static ?string $navigationLabel = 'Cabut Langganan';
    protected static string|UnitEnum|null $navigationGroup = 'Trandist Mobile - Berita Acara';
    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'berita-acara/subscription-cancellation';
    protected static ?string $modelLabel = 'Cabut Langganan';
    protected static ?string $pluralModelLabel = 'Cabut Langganan';
    protected static ?string $breadcrumb = 'Cabut Langganan';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(SubscriptionCancellationTable::getFormSchema());
    }

    public static function table(Table $table): Table
    {
        return SubscriptionCancellationTable::configure($table);
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('meterDisconnection'); // Show only complaints that have SPDC
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionCancellations::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }
}
