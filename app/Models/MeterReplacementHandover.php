<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MeterReplacementHandover extends Model
{
    protected $table = 'meter_replacement_handovers';

    protected $fillable = [
        'no_bast_gm',
        'complaint_id',
        'no_sambungan',
        'nama',
        'alamat',
        'lokasi',
        'foto_sebelum',
        'foto_sesudah',
        'merk_wm_lama',
        'no_wm_lama',
        'merk_wm_baru',
        'no_wm_baru',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->no_bast_gm) {
                $model->no_bast_gm = self::generateNoBASTGM();
            }
        });
    }

    public static function generateNoBASTGM()
    {
        return DB::transaction(function () {
            $counter = DB::table('meter_replacement_handovers')->lockForUpdate()->count();
            $date = now()->format('Ymd');
            return sprintf('BAST-GM-%s-%04d', $date, $counter + 1);
        });
    }

    public static function peekNextNoBASTGM()
    {
        $counter = MeterReplacementHandover::count();
        $date = now()->format('Ymd');
        return sprintf('BAST-GM-%s-%04d', $date, $counter + 1);
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}
