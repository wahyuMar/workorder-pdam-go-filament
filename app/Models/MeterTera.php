<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class MeterTera extends Model
{
    /** @use HasFactory<\Database\Factories\MeterTeraFactory> */
    use HasFactory;

    protected $fillable = [
        'no_spp',
        'complaint_id',
        'pegawai_id',
        'nama_pegawai',
        'no_sambungan',
        'nama',
        'alamat',
        'latitude',
        'longitude',
        'keluhan',
        'hasil_tera_meter',
        'tanggal',
    ];

    protected $casts = [
        'tanggal' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (MeterTera $meterTera): void {
            if (empty($meterTera->no_spp)) {
                $meterTera->no_spp = static::generateNoSPP();
            }
        });
    }

    public static function generateNoSPP(): string
    {
        return DB::transaction(function (): string {
            $today = now()->toDateString();

            $row = DB::table('meter_tera_numberings')
                ->lockForUpdate()
                ->first();

            if (! $row) {
                DB::table('meter_tera_numberings')->insert([
                    'prefix' => 'SPP',
                    'last_number' => 0,
                    'last_date' => $today,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DB::table('meter_tera_numberings')
                    ->lockForUpdate()
                    ->first();
            }

            $lastNumber = $row->last_number;

            if ($row->last_date !== $today) {
                $lastNumber = 0;
            }

            $newNumber = $lastNumber + 1;

            DB::table('meter_tera_numberings')
                ->where('id', $row->id)
                ->update([
                    'last_number' => $newNumber,
                    'last_date' => $today,
                    'updated_at' => now(),
                ]);

            $formattedNumber = str_pad($newNumber, 4, '0', STR_PAD_LEFT);

            return $row->prefix.'-'.now()->format('Ymd').'-'.$formattedNumber;
        });
    }

    public static function peekNextNoSPP(): string
    {
        $today = now()->toDateString();

        $row = DB::table('meter_tera_numberings')->first();

        if (! $row) {
            DB::table('meter_tera_numberings')->insert([
                'prefix' => 'SPP',
                'last_number' => 0,
                'last_date' => $today,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('meter_tera_numberings')->first();
        }

        $nextNumber = ($row->last_date === $today) ? $row->last_number + 1 : 1;

        return $row->prefix.'-'.now()->format('Ymd').'-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
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
