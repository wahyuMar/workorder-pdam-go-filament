<?php

namespace App\Filament\Resources\Confirmations\Pages;

use App\Filament\Resources\Confirmations\ConfirmRateChangeResource;
use Filament\Resources\Pages\ListRecords;

class ListConfirmRateChange extends ListRecords
{
    protected static string $resource = ConfirmRateChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
