<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\MeterDisconnectionResource;
use Filament\Resources\Pages\ListRecords;

class ListMeterDisconnection extends ListRecords
{
    protected static string $resource = MeterDisconnectionResource::class;

    protected static ?string $title = 'Meter Disconnection';

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Work Order',
            '#' => 'Meter Disconnection',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
