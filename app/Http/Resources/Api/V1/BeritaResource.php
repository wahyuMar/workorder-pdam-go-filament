<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeritaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'kategori' => $this->kategori?->value,
            'kategori_label' => $this->kategori?->getLabel(),
            'content' => $this->content,
            'image' => $this->image ? asset('storage/'.$this->image) : null,
            'file_lampiran' => $this->file_lampiran ? asset('storage/'.$this->file_lampiran) : null,
            'is_publish' => $this->is_publish,
            'created_by' => $this->whenLoaded('author', fn () => $this->author ? [
                'id' => $this->author->id,
                'name' => $this->author->name,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
