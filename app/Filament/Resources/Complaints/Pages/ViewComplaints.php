<?php

namespace App\Filament\Resources\Complaints\Pages;

use App\Filament\Resources\Complaints\ComplaintsResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewComplaints extends ViewRecord
{
    protected static string $resource = ComplaintsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
