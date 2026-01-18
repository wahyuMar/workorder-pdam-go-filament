<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomerLookupService
{
    public function fetchByNoSambungan(string $noSambungan): array
    {
        $baseUri = rtrim(Config::get('services.billing_api.base_uri'), '/');
        $appKey = Config::get('services.billing_api.app_key');

        try {
            $response = Http::withHeaders([
                'X-App-Key' => $appKey,
            ])->get($baseUri . '/external/customers/' . $noSambungan);
        } catch (\Throwable $e) {
            Log::warning('Customer lookup failed', [
                'no_sambungan' => $noSambungan,
                'error' => $e->getMessage(),
            ]);

            return [
                'data' => null,
                'message' => 'Tidak dapat terhubung ke layanan pelanggan',
            ];
        }

        if (! $response->successful() || ! $response->json('success')) {
            return [
                'data' => null,
                'message' => $response->json('message') ?? 'Customer not found',
            ];
        }

        return [
            'data' => $response->json('data') ?? null,
            'message' => null,
        ];
    }
}
