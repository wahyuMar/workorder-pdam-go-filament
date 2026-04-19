<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UploadResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'filename' => basename($this->stored_path),
            'original_name' => $this->original_name,
            'size' => $this->size,
            'mime_type' => $this->mime_type,
        ];
    }
}
