<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MeterDisconnection extends Model
{
    protected $fillable = [
        'no_spcl',
        'complaint_id',
        'pegawai_id',
        'nama_pegawai',
        'no_sambungan',
        'nama',
        'alamat',
        'latitude',
        'longitude',
        'alasan_cabut',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($meterDisconnection) {
            if (empty($meterDisconnection->no_spcl)) {
                $meterDisconnection->no_spcl = static::generateNoSPCL();
            }
        });
    }

    public static function generateNoSPCL()
    {
        return DB::transaction(function () {
            $today = now()->toDateString();

            $row = DB::table('meter_disconnection_numberings')
                ->lockForUpdate()
                ->first();

            if (! $row) {
                DB::table('meter_disconnection_numberings')->insert([
                    'prefix' => 'SPCL',
                    'last_number' => 0,
                    'last_date' => $today,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DB::table('meter_disconnection_numberings')
                    ->lockForUpdate()
                    ->first();
            }

            $lastNumber = $row->last_number;

            if ($row->last_date !== $today) {
                $lastNumber = 0;
            }

            $newNumber = $lastNumber + 1;

            DB::table('meter_disconnection_numberings')
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

    public static function peekNextNoSPCL(): string
    {
        $today = now()->toDateString();

        $row = DB::table('meter_disconnection_numberings')->first();

        if (! $row) {
            DB::table('meter_disconnection_numberings')->insert([
                'prefix' => 'SPCL',
                'last_number' => 0,
                'last_date' => $today,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('meter_disconnection_numberings')->first();
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
