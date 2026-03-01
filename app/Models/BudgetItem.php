<?php

namespace App\Models;

use App\Enums\BudgetItemType;
use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'type'        => BudgetItemType::class,
        'price'       => 'decimal:2',
        'item_amount' => 'decimal:2',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}
