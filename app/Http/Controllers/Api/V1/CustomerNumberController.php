<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\BillingApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CustomerNumber\ConfirmRequest;
use App\Http\Requests\Api\V1\CustomerNumber\VerifyRequest;
use App\Http\Resources\Api\V1\CustomerNumberResource;
use App\Models\CustomerNumber;
use App\Services\CustomerLookupService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class CustomerNumberController extends Controller
{
    public function __construct(
        protected CustomerLookupService $customerLookupService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $numbers = CustomerNumber::ownedBy($request->user()->id)->get();

        return CustomerNumberResource::collection($numbers);
    }

    public function verify(VerifyRequest $request): JsonResponse
    {
        $noSambungan = $request->validated()['no_sambungan'];

        try {
            $result = $this->customerLookupService->fetchByNoSambungan($noSambungan, throwOnError: true);
        } catch (BillingApiException) {
            return response()->json([
                'message' => 'Layanan billing sedang tidak tersedia.',
            ], 503);
        }

        if ($result['data'] === null) {
            return response()->json([
                'message' => 'Nomor sambungan tidak ditemukan.',
            ], 404);
        }

        $data = $result['data'];

        return response()->json([
            'data' => [
                'no_sambungan' => $noSambungan,
                'nama_pelanggan' => $data['nama_pelanggan'] ?? null,
                'alamat_pelanggan' => $data['alamat_pelanggan'] ?? null,
                'no_ktp' => $this->maskNik($data['no_ktp'] ?? null),
            ],
        ]);
    }

    public function confirm(ConfirmRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $result = $this->customerLookupService->fetchByNoSambungan($validated['no_sambungan'], throwOnError: true);
        } catch (BillingApiException) {
            return response()->json([
                'message' => 'Layanan billing sedang tidak tersedia.',
            ], 503);
        }

        if ($result['data'] === null || ! is_array($result['data'])) {
            return response()->json([
                'message' => 'Nomor sambungan tidak ditemukan.',
            ], 404);
        }

        $billingNik = trim($result['data']['no_ktp'] ?? '');

        if ($billingNik === '') {
            return response()->json([
                'message' => 'Data NIK pelanggan belum tersedia di sistem billing.',
            ], 422);
        }

        $submittedNik = trim($validated['nik']);

        if (strcasecmp($billingNik, $submittedNik) !== 0) {
            throw ValidationException::withMessages([
                'nik' => ['NIK tidak sesuai dengan data pelanggan.'],
            ]);
        }

        try {
            $customerNumber = CustomerNumber::create([
                'user_id' => $request->user()->id,
                'no_sambungan' => $validated['no_sambungan'],
                'verified_at' => now(),
            ]);
        } catch (UniqueConstraintViolationException) {
            return response()->json([
                'message' => 'Nomor sambungan sudah terhubung ke akun Anda.',
            ], 409);
        }

        return (new CustomerNumberResource($customerNumber))
            ->response()
            ->setStatusCode(200);
    }

    public function billing(Request $request, string $no): JsonResponse
    {
        $customerNumber = CustomerNumber::ownedBy($request->user()->id)
            ->where('no_sambungan', $no)
            ->first();

        if (! $customerNumber) {
            return response()->json([
                'message' => 'Nomor sambungan tidak ditemukan.',
            ], 404);
        }

        try {
            $result = $this->customerLookupService->fetchByNoSambungan($customerNumber->no_sambungan, throwOnError: true);
        } catch (BillingApiException) {
            return response()->json([
                'message' => 'Layanan billing sedang tidak tersedia.',
            ], 503);
        }

        if ($result['data'] === null || ! is_array($result['data'])) {
            return response()->json([
                'message' => 'Data billing tidak ditemukan.',
            ], 404);
        }

        $data = $result['data'];

        return response()->json([
            'data' => [
                'no_sambungan' => $customerNumber->no_sambungan,
                'nama_pelanggan' => $data['nama_pelanggan'] ?? null,
                'alamat_pelanggan' => $data['alamat_pelanggan'] ?? null,
                'no_ktp' => $this->maskNik($data['no_ktp'] ?? null),
            ],
        ]);
    }

    public function destroy(Request $request, string $no): JsonResponse
    {
        $customerNumber = CustomerNumber::ownedBy($request->user()->id)
            ->where('no_sambungan', $no)
            ->first();

        if (! $customerNumber) {
            return response()->json([
                'message' => 'Nomor sambungan tidak ditemukan.',
            ], 404);
        }

        $customerNumber->delete();

        return response()->json([
            'message' => 'Nomor sambungan berhasil dihapus dari akun Anda.',
        ]);
    }

    public function tagihan(Request $request, string $no): JsonResponse
    {
        $customerNumber = CustomerNumber::ownedBy($request->user()->id)
            ->where('no_sambungan', $no)
            ->first();

        if (! $customerNumber) {
            return response()->json([
                'message' => 'Nomor sambungan tidak ditemukan.',
            ], 404);
        }

        try {
            $result = $this->customerLookupService->fetchTagihan($customerNumber->no_sambungan, throwOnError: true);
        } catch (BillingApiException) {
            return response()->json([
                'message' => 'Layanan billing sedang tidak tersedia.',
            ], 503);
        }

        if ($result['data'] === null) {
            return response()->json([
                'message' => $result['message'] ?? 'Data tagihan tidak ditemukan.',
            ], 404);
        }

        return response()->json(['data' => $result['data']]);
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
