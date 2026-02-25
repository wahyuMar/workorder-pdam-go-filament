<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\MeterRateChangeResource;
use Filament\Resources\Pages\ListRecords;

class ListMeterRateChange extends ListRecords
{
    protected static string $resource = MeterRateChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
