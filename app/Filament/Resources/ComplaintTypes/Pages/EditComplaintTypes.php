<?php

namespace App\Filament\Resources\ComplaintTypes\Pages;

use App\Filament\Resources\ComplaintTypes\ComplaintTypesResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditComplaintTypes extends EditRecord
{
    protected static string $resource = ComplaintTypesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
