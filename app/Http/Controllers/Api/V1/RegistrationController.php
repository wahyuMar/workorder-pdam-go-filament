<?php

namespace App\Http\Controllers\Api\V1;

use App\Helper\CustomerRegistrationHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Registration\StoreRequest;
use App\Http\Resources\Api\V1\RegistrationListResource;
use App\Http\Resources\Api\V1\RegistrationResource;
use App\Models\CustomerRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class RegistrationController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $registrations = CustomerRegistration::ownedBy($request->user()->id)
            ->withExists('survey')
            ->latest()
            ->paginate(min($request->integer('per_page', 15), 100));

        return RegistrationListResource::collection($registrations);
    }

    public function show(Request $request, int $registration): JsonResponse
    {
        $reg = CustomerRegistration::ownedBy($request->user()->id)
            ->withExists('survey')
            ->with(['program', 'survey'])
            ->findOrFail($registration);

        return (new RegistrationResource($reg))
            ->response()
            ->setStatusCode(200);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $registration = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $data['no_surat'] = CustomerRegistrationHelper::generateNoSurat();
            $data['tanggal'] = now()->toDateString();
            $data['user_id'] = $request->user()->id;
            $data['source'] = 'mobile';

            return CustomerRegistration::create($data);
        });

        return (new RegistrationResource($registration))
            ->response()
            ->setStatusCode(201);
    }
}
