<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SubscriptionCancellation extends Model
{
    protected $table = 'subscription_cancellations';

    protected $fillable = [
        'no_bacl',
        'complaint_id',
        'no_sambungan',
        'nama',
        'alamat',
        'lokasi',
        'foto_sebelum',
        'foto_sesudah',
        'catatan',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->no_bacl) {
                $model->no_bacl = self::generateNoBACL();
            }
        });
    }

    public static function generateNoBACL()
    {
        return DB::transaction(function () {
            $counter = DB::table('subscription_cancellations')->lockForUpdate()->count();
            $date = now()->format('Ymd');
            return sprintf('BACL-%s-%04d', $date, $counter + 1);
        });
    }

    public static function peekNextNoBACL()
    {
        $counter = SubscriptionCancellation::count();
        $date = now()->format('Ymd');
        return sprintf('BACL-%s-%04d', $date, $counter + 1);
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}
