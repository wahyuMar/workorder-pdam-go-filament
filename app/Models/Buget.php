<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buget extends Model
{
    protected $guarded = [];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class);
    }
}
