<?php

namespace App\Filament\Resources\CustomerRegistrations;

use App\Filament\Resources\CustomerRegistrations\Pages\CreateCustomerRegistration;
use App\Filament\Resources\CustomerRegistrations\Pages\EditCustomerRegistration;
use App\Filament\Resources\CustomerRegistrations\Pages\ListCustomerRegistrations;
use App\Filament\Resources\CustomerRegistrations\Pages\ViewCustomerRegistration;
use App\Filament\Resources\CustomerRegistrations\Schemas\CustomerRegistrationForm;
use App\Filament\Resources\CustomerRegistrations\Schemas\CustomerRegistrationInfolist;
use App\Filament\Resources\CustomerRegistrations\Tables\CustomerRegistrationsTable;
use App\Models\CustomerRegistration;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CustomerRegistrationResource extends Resource
{
    protected static ?string $model = CustomerRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserPlus;

    protected static ?string $recordTitleAttribute = 'nama_lengkap';

    protected static string|UnitEnum|null $navigationGroup = 'Customer Registrations';

    protected static ?int $navigationSort = 10;

    public static function form(Schema $schema): Schema
    {
        return CustomerRegistrationForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CustomerRegistrationInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomerRegistrationsTable::configure($table);
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
            'index' => ListCustomerRegistrations::route('/'),
            'create' => CreateCustomerRegistration::route('/create'),
            'view' => ViewCustomerRegistration::route('/{record}'),
            'edit' => EditCustomerRegistration::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(
                'program', 'provinceKtp', 'regencyKtp', 'districtKtp', 'villageKtp',
                'provincePasang', 'regencyPasang', 'districtPasang', 'villagePasang'
            );
    }
}
