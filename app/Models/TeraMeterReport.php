<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TeraMeterReport extends Model
{
    /** @use HasFactory<\Database\Factories\TeraMeterReportFactory> */
    use HasFactory;

    protected $fillable = [
        'no_bap',
        'complaint_id',
        'no_sambungan',
        'nama',
        'alamat',
        'lokasi',
        'foto_sebelum',
        'foto_sesudah',
        'items',
        'catatan',
        'tanggal',
    ];

    protected $casts = [
        'items' => 'array',
        'tanggal' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (TeraMeterReport $teraMeterReport): void {
            if (empty($teraMeterReport->no_bap)) {
                $teraMeterReport->no_bap = static::generateNoBAP();
            }
        });
    }

    public static function generateNoBAP(): string
    {
        return DB::transaction(function (): string {
            $counter = DB::table('tera_meter_reports')
                ->lockForUpdate()
                ->count();

            return sprintf('BATM-%s-%04d', now()->format('Ymd'), $counter + 1);
        });
    }

    public static function peekNextNoBAP(): string
    {
        $counter = static::query()->count();

        return sprintf('BATM-%s-%04d', now()->format('Ymd'), $counter + 1);
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }
}
