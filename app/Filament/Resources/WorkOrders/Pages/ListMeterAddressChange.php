<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\MeterAddressChangeResource;
use Filament\Resources\Pages\ListRecords;

class ListMeterAddressChange extends ListRecords
{
    protected static string $resource = MeterAddressChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
