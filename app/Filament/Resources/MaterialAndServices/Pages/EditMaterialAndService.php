<?php

namespace App\Filament\Resources\MaterialAndServices\Pages;

use App\Filament\Resources\MaterialAndServices\MaterialAndServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMaterialAndService extends EditRecord
{
    protected static string $resource = MaterialAndServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
