<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MeterReplacement extends Model
{
    protected $fillable = [
        'no_spgm',
        'complaint_id',
        'pegawai_id',
        'nama_pegawai',
        'no_sambungan',
        'nama',
        'alamat',
        'latitude',
        'longitude',
        'alasan_penggantian',
        'biaya_ganti_meter',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
        'biaya_ganti_meter' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($meterReplacement) {
            if (empty($meterReplacement->no_spgm)) {
                $meterReplacement->no_spgm = static::generateNoSPGM();
            }
        });
    }

    public static function generateNoSPGM()
    {
        return DB::transaction(function () {
            $today = now()->toDateString();

            $row = DB::table('meter_replacement_numberings')
                ->lockForUpdate()
                ->first();

            if (! $row) {
                DB::table('meter_replacement_numberings')->insert([
                    'prefix' => 'SPGM',
                    'last_number' => 0,
                    'last_date' => $today,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DB::table('meter_replacement_numberings')
                    ->lockForUpdate()
                    ->first();
            }

            $lastNumber = $row->last_number;

            if ($row->last_date !== $today) {
                $lastNumber = 0;
            }

            $newNumber = $lastNumber + 1;

            DB::table('meter_replacement_numberings')
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

    public static function peekNextNoSPGM(): string
    {
        $today = now()->toDateString();

        $row = DB::table('meter_replacement_numberings')->first();

        if (! $row) {
            DB::table('meter_replacement_numberings')->insert([
                'prefix' => 'SPGM',
                'last_number' => 0,
                'last_date' => $today,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('meter_replacement_numberings')->first();
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
