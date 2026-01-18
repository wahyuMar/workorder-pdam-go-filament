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
        'province_id_ktp',
        'regency_id_ktp',
        'district_id_ktp',
        'village_id_ktp',
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
        'province_id_pasang',
        'regency_id_pasang',
        'district_id_pasang',
        'village_id_pasang',
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

    // Relationships untuk alamat KTP
    public function provinceKtp()
    {
        return $this->belongsTo(Province::class, 'province_id_ktp');
    }

    public function regencyKtp()
    {
        return $this->belongsTo(Regency::class, 'regency_id_ktp');
    }

    public function districtKtp()
    {
        return $this->belongsTo(District::class, 'district_id_ktp');
    }

    public function villageKtp()
    {
        return $this->belongsTo(Village::class, 'village_id_ktp');
    }

    // Relationships untuk alamat pasang
    public function provincePasang()
    {
        return $this->belongsTo(Province::class, 'province_id_pasang');
    }

    public function regencyPasang()
    {
        return $this->belongsTo(Regency::class, 'regency_id_pasang');
    }

    public function districtPasang()
    {
        return $this->belongsTo(District::class, 'district_id_pasang');
    }

    public function villagePasang()
    {
        return $this->belongsTo(Village::class, 'village_id_pasang');
    }
}
