<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\MeterClosedResource;
use Filament\Resources\Pages\ListRecords;

class ListMeterClosed extends ListRecords
{
    protected static string $resource = MeterClosedResource::class;

    protected static ?string $title = 'Meter Closed';

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Work Order',
            '#' => 'Meter Closed',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
