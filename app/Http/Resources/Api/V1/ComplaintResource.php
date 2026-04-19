<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_pengaduan' => $this->no_pengaduan,
            'complaint_type' => $this->whenLoaded('complaintType', fn () => [
                'id' => $this->complaintType->id,
                'name' => $this->complaintType->name,
            ]),
            'no_sambungan' => $this->no_sambungan,
            'nama' => $this->nama,
            'alamat' => $this->alamat,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'email' => $this->email,
            'no_hp' => $this->no_hp,
            'no_ktp' => $this->maskNik($this->no_ktp),
            'sumber' => $this->sumber,
            'judul_pengaduan' => $this->judul_pengaduan,
            'isi_pengaduan' => $this->isi_pengaduan,
            'foto' => $this->foto,
            'tanggal' => $this->tanggal?->toIso8601String(),
            'status' => $this->status,
            'priority' => $this->priority,
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
