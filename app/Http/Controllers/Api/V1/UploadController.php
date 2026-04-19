<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Upload\StoreRequest;
use App\Http\Resources\Api\V1\UploadResource;
use App\Models\Upload;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function store(StoreRequest $request): JsonResponse
    {
        $file = $request->file('file');

        $storedPath = $file->store('uploads', 'public');

        if ($storedPath === false) {
            return response()->json([
                'message' => 'Gagal menyimpan file.',
            ], 500);
        }

        try {
            $originalName = preg_replace('/[\x00-\x1F\x7F]/', '', $file->getClientOriginalName());
            $originalName = mb_substr($originalName, 0, 255);

            $upload = Upload::create([
                'user_id' => $request->user()->id,
                'original_name' => $originalName,
                'stored_path' => $storedPath,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        } catch (\Throwable $e) {
            Storage::disk('public')->delete($storedPath);

            throw $e;
        }

        return (new UploadResource($upload))
            ->response()
            ->setStatusCode(201);
    }
}
