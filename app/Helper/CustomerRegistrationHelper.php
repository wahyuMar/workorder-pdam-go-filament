<?php

namespace App\Helper;

use App\Models\CustomerRegistration;

class CustomerRegistrationHelper
{
   public static function generateNoSurat(): string
   {
        $lastId = CustomerRegistration::max('id') ?? 0;
        return 'SRPB-' . date('Ymd') . '-'
            . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
   }
}
