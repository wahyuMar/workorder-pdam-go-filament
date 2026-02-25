<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MeterNameChange extends Model
{
    protected $fillable = [
        'no_spun',
        'complaint_id',
        'pegawai_id',
        'nama_pegawai',
        'no_sambungan',
        'nama_lama',
        'nama_baru',
        'alamat_lama',
        'alamat_baru',
        'email_lama',
        'email_baru',
        'no_hp_lama',
        'no_hp_baru',
        'no_ktp_lama',
        'no_ktp_baru',
        'latitude',
        'longitude',
        'alasan_ubah_nama',
        'upload_ktp',
        'upload_kk',
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

        static::creating(function ($meterNameChange) {
            if (empty($meterNameChange->no_spun)) {
                $meterNameChange->no_spun = static::generateNoSPUN();
            }
        });
    }

    public static function generateNoSPUN()
    {
        return DB::transaction(function () {
            $today = now()->toDateString();

            $row = DB::table('meter_name_change_numberings')
                ->lockForUpdate()
                ->first();

            if (! $row) {
                DB::table('meter_name_change_numberings')->insert([
                    'prefix' => 'SPUN',
                    'last_number' => 0,
                    'last_date' => $today,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DB::table('meter_name_change_numberings')
                    ->lockForUpdate()
                    ->first();
            }

            $lastNumber = $row->last_number;

            if ($row->last_date !== $today) {
                $lastNumber = 0;
            }

            $newNumber = $lastNumber + 1;

            DB::table('meter_name_change_numberings')
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

    public static function peekNextNoSPUN(): string
    {
        $today = now()->toDateString();

        $row = DB::table('meter_name_change_numberings')->first();

        if (! $row) {
            DB::table('meter_name_change_numberings')->insert([
                'prefix' => 'SPUN',
                'last_number' => 0,
                'last_date' => $today,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('meter_name_change_numberings')->first();
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
