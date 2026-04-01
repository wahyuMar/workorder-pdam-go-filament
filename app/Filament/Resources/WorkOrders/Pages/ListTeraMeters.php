<?php

namespace App\Filament\Resources\WorkOrders\Pages;

use App\Filament\Resources\WorkOrders\TeraMeterResource;
use Filament\Resources\Pages\ListRecords;

class ListTeraMeters extends ListRecords
{
    protected static string $resource = TeraMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
