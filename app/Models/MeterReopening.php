<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MeterReopening extends Model
{
    protected $fillable = [
        'no_spbk',
        'complaint_id',
        'pegawai_id',
        'nama_pegawai',
        'no_sambungan',
        'nama',
        'alamat',
        'latitude',
        'longitude',
        'alasan_buka_kembali',
        'biaya_buka_kembali',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'biaya_buka_kembali' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($meterReopening) {
            if (empty($meterReopening->no_spbk)) {
                $meterReopening->no_spbk = static::generateNoSPBK();
            }
        });
    }

    public static function generateNoSPBK()
    {
        return DB::transaction(function () {
            $today = now()->toDateString();

            $row = DB::table('meter_reopening_numberings')
                ->lockForUpdate()
                ->first();

            if (! $row) {
                DB::table('meter_reopening_numberings')->insert([
                    'prefix' => 'SPBK',
                    'last_number' => 0,
                    'last_date' => $today,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DB::table('meter_reopening_numberings')
                    ->lockForUpdate()
                    ->first();
            }

            $lastNumber = $row->last_number;

            if ($row->last_date !== $today) {
                $lastNumber = 0;
            }

            $newNumber = $lastNumber + 1;

            DB::table('meter_reopening_numberings')
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

    public static function peekNextNoSPBK(): string
    {
        $today = now()->toDateString();

        $row = DB::table('meter_reopening_numberings')->first();

        if (! $row) {
            DB::table('meter_reopening_numberings')->insert([
                'prefix' => 'SPBK',
                'last_number' => 0,
                'last_date' => $today,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('meter_reopening_numberings')->first();
        }

        $nextNumber = ($row->last_date === $today) ? $row->last_number + 1 : 1;

        return $row->prefix . '-' . now()->format('Ymd') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function pegawai()
    {
        return $this->belongsTo(User::class, 'pegawai_id');
    }
}
