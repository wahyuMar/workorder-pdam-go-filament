<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\MeterReopeningResource;
use Filament\Resources\Pages\ListRecords;

class ListMeterReopening extends ListRecords
{
    protected static string $resource = MeterReopeningResource::class;

    protected static ?string $title = 'Meter Reopening';

    public function getBreadcrumbs(): array
    {
        return [
            url()->previous() => 'Work Order',
            '#' => 'Meter Reopening',
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
