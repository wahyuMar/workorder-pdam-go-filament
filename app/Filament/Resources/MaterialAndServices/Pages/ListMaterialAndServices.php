<?php

namespace App\Filament\Resources\MaterialAndServices\Pages;

use App\Filament\Resources\MaterialAndServices\MaterialAndServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMaterialAndServices extends ListRecords
{
    protected static string $resource = MaterialAndServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
