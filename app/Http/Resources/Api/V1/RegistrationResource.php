<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_surat' => $this->no_surat,
            'nama_lengkap' => $this->nama_lengkap,
            'program_id' => $this->program_id,
            'no_ktp' => $this->maskNik($this->no_ktp),
            'no_kk' => $this->no_kk,

            // KTP Address
            'alamat_ktp' => $this->alamat_ktp,
            'dusun_kampung_ktp' => $this->dusun_kampung_ktp,
            'rt_ktp' => $this->rt_ktp,
            'rw_ktp' => $this->rw_ktp,
            'province_id_ktp' => $this->province_id_ktp,
            'regency_id_ktp' => $this->regency_id_ktp,
            'district_id_ktp' => $this->district_id_ktp,
            'village_id_ktp' => $this->village_id_ktp,

            // Personal
            'pekerjaan' => $this->pekerjaan,
            'email' => $this->email,
            'no_telp' => $this->no_telp,
            'no_hp' => $this->no_hp,

            // Installation Address
            'alamat_pasang' => $this->alamat_pasang,
            'dusun_kampung_pasang' => $this->dusun_kampung_pasang,
            'rt_pasang' => $this->rt_pasang,
            'rw_pasang' => $this->rw_pasang,
            'province_id_pasang' => $this->province_id_pasang,
            'regency_id_pasang' => $this->regency_id_pasang,
            'district_id_pasang' => $this->district_id_pasang,
            'village_id_pasang' => $this->village_id_pasang,

            // House / Utility
            'jumlah_penghuni_tetap' => $this->jumlah_penghuni_tetap,
            'jumlah_penghuni_tidak_tetap' => $this->jumlah_penghuni_tidak_tetap,
            'jumlah_kran_air_minum' => $this->jumlah_kran_air_minum,
            'jenis_rumah' => $this->jenis_rumah,
            'jumlah_kran' => $this->jumlah_kran,
            'daya_listrik' => $this->daya_listrik,

            // Uploads
            'upload_ktp' => $this->upload_ktp,
            'upload_kk' => $this->upload_kk,
            'upload_tagihan_listrik' => $this->upload_tagihan_listrik,
            'upload_foto_rumah' => $this->upload_foto_rumah,

            // Coordinates
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,

            // Meta
            'source' => $this->source,
            'tanggal' => $this->tanggal?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),

            // Status
            'has_survey' => (bool) $this->survey_exists,
            'survey' => $this->whenLoaded('survey', fn () => [
                'no_survey' => $this->survey->no_survey,
                'tanggal_survey' => $this->survey->tanggal_survey?->toIso8601String(),
            ]),
        ];
    }

    private function maskNik(?string $nik): ?string
    {
        if ($nik === null) {
            return null;
        }

        if (strlen($nik) <= 8) {
            return '****';
        }

        return substr($nik, 0, 4).'****'.substr($nik, -4);
    }
}
