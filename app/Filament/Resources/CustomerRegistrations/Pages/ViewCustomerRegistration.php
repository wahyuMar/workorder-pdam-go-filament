<?php

namespace App\Filament\Resources\CustomerRegistrations\Pages;

use App\Filament\Resources\CustomerRegistrations\Components\Buttons\CreateSurveyAction;
use App\Filament\Resources\CustomerRegistrations\CustomerRegistrationResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomerRegistration extends ViewRecord
{
    protected static string $resource = CustomerRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        $surveyExists = $this->record->survey !== null;
        return [
            EditAction::make()->visible(!$surveyExists),
            CreateSurveyAction::make()->visible(!$surveyExists),
        ];
    }
}
