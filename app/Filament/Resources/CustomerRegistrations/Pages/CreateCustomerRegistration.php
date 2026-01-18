<?php

namespace App\Filament\Resources\CustomerRegistrations\Pages;

use App\Filament\Resources\CustomerRegistrations\CustomerRegistrationResource;
use App\Helper\CustomerRegistrationHelper;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomerRegistration extends CreateRecord
{
    protected static string $resource = CustomerRegistrationResource::class;

    public function mutateFormDataBeforeCreate(array $data): array
    {
        $data['no_surat'] = CustomerRegistrationHelper::generateNoSurat();
        $data['tanggal'] = now()->toDateString();
        return $data;
    }
}
