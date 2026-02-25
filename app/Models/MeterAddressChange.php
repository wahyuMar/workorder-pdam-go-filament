<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MeterAddressChange extends Model
{
    protected $fillable = [
        'no_spua',
        'complaint_id',
        'pegawai_id',
        'nama_pegawai',
        'no_sambungan',
        'nama',
        'id_unit_lama',
        'nama_unit_lama',
        'id_desa_lama',
        'nama_desa_lama',
        'id_wilayah_lama',
        'nama_wilayah_lama',
        'id_jalan_lama',
        'nama_jalan_lama',
        'id_rt_rw_lama',
        'nama_rt_rw_lama',
        'id_kolektor_lama',
        'nama_kolektor_lama',
        'id_unit_baru',
        'nama_unit_baru',
        'id_desa_baru',
        'nama_desa_baru',
        'id_wilayah_baru',
        'nama_wilayah_baru',
        'id_jalan_baru',
        'nama_jalan_baru',
        'id_rt_rw_baru',
        'nama_rt_rw_baru',
        'id_kolektor_baru',
        'nama_kolektor_baru',
        'latitude',
        'longitude',
        'biaya_ubah_alamat',
        'alasan_ubah_alamat',
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

        static::creating(function ($meterAddressChange) {
            if (empty($meterAddressChange->no_spua)) {
                $meterAddressChange->no_spua = static::generateNoSPUA();
            }
        });
    }

    public static function generateNoSPUA()
    {
        return DB::transaction(function () {
            $today = now()->toDateString();

            $row = DB::table('meter_address_change_numberings')
                ->lockForUpdate()
                ->first();

            if (! $row) {
                DB::table('meter_address_change_numberings')->insert([
                    'prefix' => 'SPUA',
                    'last_number' => 0,
                    'last_date' => $today,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DB::table('meter_address_change_numberings')
                    ->lockForUpdate()
                    ->first();
            }

            $lastNumber = $row->last_number;

            if ($row->last_date !== $today) {
                $lastNumber = 0;
            }

            $newNumber = $lastNumber + 1;

            DB::table('meter_address_change_numberings')
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

    public static function peekNextNoSPUA(): string
    {
        $today = now()->toDateString();

        $row = DB::table('meter_address_change_numberings')->first();

        if (! $row) {
            DB::table('meter_address_change_numberings')->insert([
                'prefix' => 'SPUA',
                'last_number' => 0,
                'last_date' => $today,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('meter_address_change_numberings')->first();
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
