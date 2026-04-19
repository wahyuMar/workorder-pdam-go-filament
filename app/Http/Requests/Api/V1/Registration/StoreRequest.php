<?php

namespace App\Http\Requests\Api\V1\Registration;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Personal
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'program_id' => ['nullable', 'integer', 'exists:programs,id'],
            'no_ktp' => ['nullable', 'string', 'max:255'],
            'no_kk' => ['nullable', 'string', 'max:255'],
            'pekerjaan' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'max:255'],
            'no_telp' => ['nullable', 'string', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:255'],

            // KTP Address
            'alamat_ktp' => ['nullable', 'string'],
            'dusun_kampung_ktp' => ['nullable', 'string', 'max:255'],
            'rt_ktp' => ['nullable', 'integer', 'min:0'],
            'rw_ktp' => ['nullable', 'integer', 'min:0'],
            'province_id_ktp' => ['nullable', 'integer', 'exists:provinces,id'],
            'regency_id_ktp' => ['nullable', 'integer', 'exists:regencies,id'],
            'district_id_ktp' => ['nullable', 'integer', 'exists:districts,id'],
            'village_id_ktp' => ['nullable', 'integer', 'exists:villages,id'],

            // Installation Address
            'alamat_pasang' => ['nullable', 'string'],
            'dusun_kampung_pasang' => ['nullable', 'string', 'max:255'],
            'rt_pasang' => ['nullable', 'integer', 'min:0'],
            'rw_pasang' => ['nullable', 'integer', 'min:0'],
            'province_id_pasang' => ['nullable', 'integer', 'exists:provinces,id'],
            'regency_id_pasang' => ['nullable', 'integer', 'exists:regencies,id'],
            'district_id_pasang' => ['nullable', 'integer', 'exists:districts,id'],
            'village_id_pasang' => ['nullable', 'integer', 'exists:villages,id'],

            // House / Utility
            'jumlah_penghuni_tetap' => ['nullable', 'integer', 'min:0'],
            'jumlah_penghuni_tidak_tetap' => ['nullable', 'integer', 'min:0'],
            'jumlah_kran_air_minum' => ['nullable', 'integer', 'min:0'],
            'jenis_rumah' => ['nullable', Rule::in(['Permanen', 'Semi Permanen', 'Non Permanen'])],
            'jumlah_kran' => ['nullable', 'integer', 'min:0'],
            'daya_listrik' => ['nullable', 'integer', 'min:0'],

            // Upload references (filenames from upload endpoint)
            'upload_ktp' => ['nullable', 'string', 'max:255', 'not_regex:/\.\.|\\\\|\x00/'],
            'upload_kk' => ['nullable', 'string', 'max:255', 'not_regex:/\.\.|\\\\|\x00/'],
            'upload_tagihan_listrik' => ['nullable', 'string', 'max:255', 'not_regex:/\.\.|\\\\|\x00/'],
            'upload_foto_rumah' => ['nullable', 'string', 'max:255', 'not_regex:/\.\.|\\\\|\x00/'],

            // Coordinates
            'latitude' => ['nullable', 'string', 'max:255'],
            'longitude' => ['nullable', 'string', 'max:255'],
        ];
    }
}
