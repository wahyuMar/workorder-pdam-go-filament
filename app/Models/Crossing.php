<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Crossing extends Model
{
    protected $guarded = [];

    public function surveys()
    {
        return $this->hasMany(Survey::class);
    }
}
