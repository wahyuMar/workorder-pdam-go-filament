<?php

namespace App\Models;

use App\Enums\MaterialAndServiceCategory;
use Illuminate\Database\Eloquent\Model;

class MaterialAndService extends Model
{
    protected $guarded = [];

    protected $casts = [
        'category'     => MaterialAndServiceCategory::class,
        'price'        => 'decimal:2',
        'is_deletable' => 'boolean',
        'is_service'   => 'boolean',
    ];
}
