<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MeterRateChange extends Model
{
    protected $fillable = [
        'no_sput',
        'complaint_id',
        'no_sambungan',
        'nama',
        'alamat',
        'email',
        'no_hp',
        'no_ktp',
        'alasan_ganti_tarif',
        'is_confirmed',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'is_confirmed' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($meterRateChange) {
            if (empty($meterRateChange->no_sput)) {
                $meterRateChange->no_sput = static::generateNoSPUT();
            }
        });
    }

    public static function generateNoSPUT()
    {
        return DB::transaction(function () {
            $today = now()->toDateString();

            $row = DB::table('meter_rate_change_numberings')
                ->lockForUpdate()
                ->first();

            if (! $row) {
                DB::table('meter_rate_change_numberings')->insert([
                    'prefix' => 'SPUT',
                    'last_number' => 0,
                    'last_date' => $today,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DB::table('meter_rate_change_numberings')
                    ->lockForUpdate()
                    ->first();
            }

            $lastNumber = $row->last_number;

            if ($row->last_date !== $today) {
                $lastNumber = 0;
            }

            $newNumber = $lastNumber + 1;

            DB::table('meter_rate_change_numberings')
                ->where('id', $row->id)
                ->update([
                    'last_number' => $newNumber,
                    'last_date' => $today,
                    'updated_at' => now(),
                ]);

            $formattedNumber = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            return $row->prefix . '-' . now()->format('Ymd') . '-' . $formattedNumber;
        });
    }

    public static function peekNextNoSPUT(): string
    {
        $today = now()->toDateString();

        $row = DB::table('meter_rate_change_numberings')->first();

        if (! $row) {
            DB::table('meter_rate_change_numberings')->insert([
                'prefix' => 'SPUT',
                'last_number' => 0,
                'last_date' => $today,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('meter_rate_change_numberings')->first();
        }

        $lastNumber = $row->last_number;
        $tomorrow = now()->addDay()->toDateString();

        if ($row->last_date !== $tomorrow) {
            $lastNumber = 0;
        }

        $newNumber = $lastNumber + 1;
        $formattedNumber = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

        return $row->prefix . '-' . $tomorrow . '-' . $formattedNumber;
    }
}
