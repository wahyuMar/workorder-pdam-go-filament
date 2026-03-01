<?php

namespace App\Models;

use App\Enums\BudgetItemCategory;
use App\Enums\BudgetItemSubCategory;
use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    protected $guarded = [];

    protected $casts = [
        'category'     => BudgetItemCategory::class,
        'sub_category' => BudgetItemSubCategory::class,
        'price'        => 'decimal:2',
        'item_amount'  => 'decimal:2',
    ];

    public function budget()
    {
        return $this->belongsTo(Budget::class);
    }
}
