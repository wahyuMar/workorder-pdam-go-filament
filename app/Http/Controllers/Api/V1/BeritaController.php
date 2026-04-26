<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BeritaListResource;
use App\Http\Resources\Api\V1\BeritaResource;
use App\Models\Berita;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BeritaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $beritas = Berita::query()
            ->where('is_publish', true)
            ->when($request->filled('kategori'), fn ($q) => $q->where('kategori', $request->string('kategori')))
            ->latest()
            ->paginate(max(1, min($request->integer('per_page', 15), 100)));

        return BeritaListResource::collection($beritas);
    }

    public function show(int $berita): JsonResponse
    {
        $record = Berita::query()
            ->where('is_publish', true)
            ->with('author')
            ->findOrFail($berita);

        return (new BeritaResource($record))
            ->response()
            ->setStatusCode(200);
    }
}
