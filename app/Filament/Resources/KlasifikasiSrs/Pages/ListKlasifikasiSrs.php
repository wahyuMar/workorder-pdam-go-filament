<?php

namespace App\Filament\Resources\KlasifikasiSrs\Pages;

use App\Filament\Resources\KlasifikasiSrs\KlasifikasiSrResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListKlasifikasiSrs extends ListRecords
{
    protected static string $resource = KlasifikasiSrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
