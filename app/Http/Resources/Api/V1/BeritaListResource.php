<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeritaListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'kategori' => $this->kategori?->value,
            'kategori_label' => $this->kategori?->getLabel(),
            'image' => $this->image ? asset('storage/'.$this->image) : null,
            'is_publish' => $this->is_publish,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
