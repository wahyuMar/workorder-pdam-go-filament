<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SubscriptionReopening extends Model
{
    use HasFactory;

    protected $table = 'subscription_reopenings';

    protected $fillable = [
        'no_bast_bk',
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
            if (! $model->no_bast_bk) {
                $model->no_bast_bk = self::generateNoBASTBK();
            }
        });

        static::created(function (SubscriptionReopening $model): void {
            $model->complaint?->meterReopening?->update([
                'is_confirmed' => true,
            ]);
        });
    }

    public static function generateNoBASTBK()
    {
        return DB::transaction(function () {
            $counter = DB::table('subscription_reopenings')->lockForUpdate()->count();
            $date = now()->format('Ymd');

            return sprintf('BAST-BK-%s-%04d', $date, $counter + 1);
        });
    }

    public static function peekNextNoBASTBK()
    {
        $counter = SubscriptionReopening::query()->count('*');
        $date = now()->format('Ymd');

        return sprintf('BAST-BK-%s-%04d', $date, $counter + 1);
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}
