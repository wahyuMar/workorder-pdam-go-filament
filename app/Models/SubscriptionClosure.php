<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SubscriptionClosure extends Model
{
    protected $table = 'subscription_closures';

    protected $fillable = [
        'no_batl',
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
            if (!$model->no_batl) {
                $model->no_batl = self::generateNoBATL();
            }
        });
    }

    public static function generateNoBATL()
    {
        return DB::transaction(function () {
            $counter = DB::table('subscription_closures')->lockForUpdate()->count();
            $date = now()->format('Ymd');
            return sprintf('BATL-%s-%04d', $date, $counter + 1);
        });
    }

    public static function peekNextNoBATL()
    {
        $counter = SubscriptionClosure::count();
        $date = now()->format('Ymd');
        return sprintf('BATL-%s-%04d', $date, $counter + 1);
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}
