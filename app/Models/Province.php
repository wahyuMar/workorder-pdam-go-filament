<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_selectable',
    ];

    protected $casts = [
        'is_selectable' => 'boolean',
    ];

    public function regencies(): HasMany
    {
        return $this->hasMany(Regency::class);
    }
}
