<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\MeterNameChangeResource;
use Filament\Resources\Pages\ListRecords;

class ListMeterNameChange extends ListRecords
{
    protected static string $resource = MeterNameChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }
}
