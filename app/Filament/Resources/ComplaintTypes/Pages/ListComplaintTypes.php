<?php

namespace App\Filament\Resources\ComplaintTypes\Pages;

use App\Filament\Resources\ComplaintTypes\ComplaintTypesResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComplaintTypes extends ListRecords
{
    protected static string $resource = ComplaintTypesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
