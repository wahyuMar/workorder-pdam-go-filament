<?php

namespace App\Filament\Resources\CustomerRegistrations\Pages;

use App\Filament\Resources\CustomerRegistrations\CustomerRegistrationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerRegistration extends CreateRecord
{
    protected static string $resource = CustomerRegistrationResource::class;
}
