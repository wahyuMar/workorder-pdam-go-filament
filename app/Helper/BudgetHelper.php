<?php

namespace App\Helper;

use App\Models\Budget;

class BudgetHelper
{
    public static function generateBudgetingNumber(): string
    {
        $lastId = Budget::max('id') ?? 0;
        return 'BGT-' . date('Ymd') . '-'
            . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);
    }
}
