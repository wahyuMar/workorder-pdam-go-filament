<?php

namespace App\Models;

use App\Enums\WorkOrderEnum;
use Illuminate\Database\Eloquent\Model;

class ComplaintFollowUp extends Model
{
    protected $fillable = [
        'complaint_id',
        'complaint_number',
        'carbon_copies',
        'work_order',
        'notes',
        'photos',
        'follow_up_at',
    ];

    protected $casts = [
        'carbon_copies' => 'array',
        'photos' => 'array',
        'work_order' => WorkOrderEnum::class,
        'follow_up_at' => 'datetime',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}
