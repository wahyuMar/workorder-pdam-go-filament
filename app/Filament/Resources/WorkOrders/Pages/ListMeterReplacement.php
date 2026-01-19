<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\MeterReplacementResource;
use Filament\Resources\Pages\ListRecords;

class ListMeterReplacement extends ListRecords
{
    protected static string $resource = MeterReplacementResource::class;

    protected static ?string $title = 'Meter Replacement';

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Work Order',
            '#' => 'Meter Replacement',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
