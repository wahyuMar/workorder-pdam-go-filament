<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_surat',
        'nama_lengkap',
        'program_id',
        'no_ktp',
        'no_kk',
        'alamat_ktp',
        'dusun_kampung_ktp',
        'rt_ktp',
        'rw_ktp',
        'kel_desa_ktp',
        'kecamatan_ktp',
        'kab_kota_ktp',
        'pekerjaan',
        'email',
        'no_telp',
        'no_hp',
        'alamat_pasang',
        'dusun_kampung_pasang',
        'rt_pasang',
        'rw_pasang',
        'kel_desa_pasang',
        'kecamatan_pasang',
        'kab_kota_pasang',
        'jumlah_penghuni_tetap',
        'jumlah_penghuni_tidak_tetap',
        'jumlah_kran_air_minum',
        'jenis_rumah',
        'jumlah_kran',
        'daya_listrik',
        'upload_ktp',
        'upload_kk',
        'upload_tagihan_listrik',
        'upload_foto_rumah',
        'lang',
        'lat',
        'tanggal',
    ];

    protected $casts = [
        'rt_ktp' => 'integer',
        'rw_ktp' => 'integer',
        'rt_pasang' => 'integer',
        'rw_pasang' => 'integer',
        'jumlah_penghuni_tetap' => 'integer',
        'jumlah_penghuni_tidak_tetap' => 'integer',
        'jumlah_kran_air_minum' => 'integer',
        'jumlah_kran' => 'integer',
        'daya_listrik' => 'integer',
        'tanggal' => 'datetime',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
