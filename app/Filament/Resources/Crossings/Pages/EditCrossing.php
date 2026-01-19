<?php

namespace App\Filament\Resources\Crossings\Pages;

use App\Filament\Resources\Crossings\CrossingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCrossing extends EditRecord
{
    protected static string $resource = CrossingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
