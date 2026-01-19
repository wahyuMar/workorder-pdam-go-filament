<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_pengaduan',
        'complaint_type_id',
        'no_sambungan',
        'nama',
        'alamat',
        'email',
        'no_hp',
        'no_ktp',
        'sumber',
        'judul_pengaduan',
        'isi_pengaduan',
        'foto',
        'tanggal',
        'status',
        'priority',
    ];

    protected $casts = [
        'foto' => 'array',
        'tanggal' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($complaint) {
            if (empty($complaint->no_pengaduan)) {
                $complaint->no_pengaduan = static::generateNoPengaduan();
            }
        });
    }

    public static function generateNoPengaduan()
    {
        return DB::transaction(function () {
            $today = now()->toDateString();

            $row = DB::table('complaint_numberings')
                ->lockForUpdate()
                ->first();

            if (! $row) {
                DB::table('complaint_numberings')->insert([
                    'prefix' => 'PGD',
                    'last_number' => 0,
                    'last_date' => $today,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $row = DB::table('complaint_numberings')
                    ->lockForUpdate()
                    ->first();
            }

            $lastNumber = $row->last_number;

            if ($row->last_date !== $today) {
                $lastNumber = 0;
            }

            $newNumber = $lastNumber + 1;

            DB::table('complaint_numberings')
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

    public static function peekNextNoPengaduan(): string
    {
        $today = now()->toDateString();

        $row = DB::table('complaint_numberings')->first();

        if (! $row) {
            DB::table('complaint_numberings')->insert([
                'prefix' => 'PGD',
                'last_number' => 0,
                'last_date' => $today,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $row = DB::table('complaint_numberings')->first();
        }

        $nextNumber = ($row->last_date === $today) ? $row->last_number + 1 : 1;

        return $row->prefix . '-' . now()->format('Ymd') . '-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    public function complaintType()
    {
        return $this->belongsTo(ComplaintType::class);
    }

    public function followUps()
    {
        return $this->hasMany(ComplaintFollowUp::class);
    }
}
