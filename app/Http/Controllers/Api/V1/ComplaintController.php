<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\BillingApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Complaint\StoreRequest;
use App\Http\Resources\Api\V1\ComplaintFollowUpResource;
use App\Http\Resources\Api\V1\ComplaintListResource;
use App\Http\Resources\Api\V1\ComplaintResource;
use App\Models\Complaint;
use App\Services\CustomerLookupService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class ComplaintController extends Controller
{
    public function __construct(
        protected CustomerLookupService $customerLookupService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $complaints = Complaint::ownedBy($request->user()->id)
            ->with('complaintType')
            ->latest()
            ->paginate(max(1, min($request->integer('per_page', 15), 100)));

        return ComplaintListResource::collection($complaints);
    }

    public function show(Request $request, int $complaint): JsonResponse
    {
        $record = Complaint::ownedBy($request->user()->id)
            ->with('complaintType')
            ->findOrFail($complaint);

        return (new ComplaintResource($record))
            ->response()
            ->setStatusCode(200);
    }

    public function timeline(Request $request, int $complaint): JsonResponse
    {
        $record = Complaint::ownedBy($request->user()->id)
            ->findOrFail($complaint);

        $followUps = $record->followUps()
            ->oldest('follow_up_at')
            ->get();

        return ComplaintFollowUpResource::collection($followUps)
            ->response()
            ->setStatusCode(200);
    }

    public function store(StoreRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Auto-fill nama & alamat from billing data (FR27)
        try {
            $billing = $this->customerLookupService->fetchByNoSambungan($data['no_sambungan'], throwOnError: true);
        } catch (BillingApiException) {
            return response()->json([
                'message' => 'Layanan billing sedang tidak tersedia.',
            ], 503);
        }

        $billingData = $billing['data'] ?? null;
        $data['nama'] = $billingData['nama_pelanggan'] ?? $request->user()->name;
        $data['alamat'] = $billingData['alamat_pelanggan'] ?? null;

        // Auto-set fields (FR28)
        $data['user_id'] = $request->user()->id;
        $data['sumber'] = 'mobile_apps';
        $data['tanggal'] = now();

        $complaint = DB::transaction(function () use ($data) {
            return Complaint::create($data);
        });

        $complaint->refresh();
        $complaint->load('complaintType');

        return (new ComplaintResource($complaint))
            ->response()
            ->setStatusCode(201);
    }
}
