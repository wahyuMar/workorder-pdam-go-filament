<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class StatusChange extends Model
{
    protected $table = 'status_changes';

    protected $fillable = [
        'no_baus',
        'complaint_id',
        'no_sambungan',
        'nama',
        'alamat',
        'lokasi',
        'jenis_rumah',
        'jumlah_kran',
        'daya_listrik',
        'verifikasi_ktp',
        'verifikasi_kk',
        'verifikasi_tagihan_listrik',
        'verifikasi_foto_rumah',
        'klasifikasi_sr',
        'catatan',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'jumlah_kran' => 'integer',
        'daya_listrik' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->no_baus) {
                $model->no_baus = self::generateNoBAUS();
            }
        });
    }

    public static function generateNoBAUS()
    {
        return DB::transaction(function () {
            $counter = DB::table('status_changes')->lockForUpdate()->count();
            $date = now()->format('Ymd');
            return sprintf('BAUS-%s-%04d', $date, $counter + 1);
        });
    }

    public static function peekNextNoBAUS()
    {
        $counter = StatusChange::count();
        $date = now()->format('Ymd');
        return sprintf('BAUS-%s-%04d', $date, $counter + 1);
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}
