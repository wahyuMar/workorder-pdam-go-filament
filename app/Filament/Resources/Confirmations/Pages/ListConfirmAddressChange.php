<?php

namespace App\Filament\Resources\Confirmations\Pages;

use App\Filament\Resources\Confirmations\ConfirmAddressChangeResource;
use Filament\Resources\Pages\ListRecords;

class ListConfirmAddressChange extends ListRecords
{
    protected static string $resource = ConfirmAddressChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
