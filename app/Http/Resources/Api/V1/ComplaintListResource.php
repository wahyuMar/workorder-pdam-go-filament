<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintListResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'no_pengaduan' => $this->no_pengaduan,
            'complaint_type' => $this->whenLoaded('complaintType', fn () => $this->complaintType ? [
                'id' => $this->complaintType->id,
                'name' => $this->complaintType->name,
            ] : null),
            'no_sambungan' => $this->no_sambungan,
            'judul_pengaduan' => $this->judul_pengaduan,
            'status' => $this->status,
            'priority' => $this->priority,
            'tanggal' => $this->tanggal?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
