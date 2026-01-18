<?php

namespace App\Filament\Resources\ComplaintTypes\Pages;

use App\Filament\Resources\ComplaintTypes\ComplaintTypesResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewComplaintTypes extends ViewRecord
{
    protected static string $resource = ComplaintTypesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
