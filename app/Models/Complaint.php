<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        $date = now()->format('Ymd');
        $lastComplaint = static::whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastComplaint && $lastComplaint->no_pengaduan) {
            $lastNumber = intval(substr($lastComplaint->no_pengaduan, -4));
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return 'PGD-' . $date . '-' . $newNumber;
    }

    public function complaintType()
    {
        return $this->belongsTo(ComplaintType::class);
    }
}
