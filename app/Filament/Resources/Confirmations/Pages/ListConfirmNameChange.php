<?php

namespace App\Filament\Resources\Confirmations\Pages;

use App\Filament\Resources\Confirmations\ConfirmNameChangeResource;
use Filament\Resources\Pages\ListRecords;

class ListConfirmNameChange extends ListRecords
{
    protected static string $resource = ConfirmNameChangeResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
