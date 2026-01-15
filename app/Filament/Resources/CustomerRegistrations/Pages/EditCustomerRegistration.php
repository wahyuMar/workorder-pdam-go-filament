<?php

namespace App\Filament\Resources\CustomerRegistrations\Pages;

use App\Filament\Resources\CustomerRegistrations\CustomerRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCustomerRegistration extends EditRecord
{
    protected static string $resource = CustomerRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
