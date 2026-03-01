<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'lokasi_pipa_distribusi_lat' => 'decimal:8',
        'lokasi_pipa_distribusi_long' => 'decimal:8',
        'panjang_pipa_sr' => 'integer',
        'lokasi_sr_lat' => 'decimal:8',
        'lokasi_sr_long' => 'decimal:8',
        'lokasi_rabatan_lat' => 'decimal:8',
        'lokasi_rabatan_long' => 'decimal:8',
        'panjang_rabatan' => 'integer',
        'lokasi_crossing_lat' => 'decimal:8',
        'lokasi_crossing_long' => 'decimal:8',
        'panjang_crossing' => 'integer',
        'tanggal_survey' => 'datetime',
    ];

    public function customerRegistration()
    {
        return $this->belongsTo(CustomerRegistration::class);
    }

    public function clampSaddle()
    {
        return $this->belongsTo(ClampSaddle::class);
    }

    public function klasifikasiSr()
    {
        return $this->belongsTo(KlasifikasiSr::class);
    }

    public function crossing()
    {
        return $this->belongsTo(Crossing::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function budgeting()
    {
        return $this->hasOne(Budget::class);
    }
}
