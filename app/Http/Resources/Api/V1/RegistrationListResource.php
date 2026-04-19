<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegistrationListResource extends JsonResource
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
            'source' => $this->source,
            'tanggal' => $this->tanggal?->toIso8601String(),
            'has_survey' => (bool) $this->survey_exists,
            'created_at' => $this->created_at?->toIso8601String(),
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
