<?php

namespace App\Filament\Resources\KlasifikasiSrs\Pages;

use App\Filament\Resources\KlasifikasiSrs\KlasifikasiSrResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditKlasifikasiSr extends EditRecord
{
    protected static string $resource = KlasifikasiSrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
