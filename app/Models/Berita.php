<?php

namespace App\Models;

use App\Enums\BeritaKategori;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Berita extends Model
{
    use HasFactory;

    protected $fillable = [
        'judul',
        'kategori',
        'content',
        'image',
        'file_lampiran',
        'is_publish',
        'created_by',
    ];

    public function casts(): array
    {
        return [
            'kategori' => BeritaKategori::class,
            'is_publish' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
