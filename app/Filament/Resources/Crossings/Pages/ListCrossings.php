<?php

namespace App\Filament\Resources\Crossings\Pages;

use App\Filament\Resources\Crossings\CrossingResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCrossings extends ListRecords
{
    protected static string $resource = CrossingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
