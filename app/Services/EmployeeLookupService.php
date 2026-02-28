<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EmployeeLookupService
{
    public function fetchEmployees(): array
    {
        $baseUri = rtrim(Config::get('services.billing_api.base_uri'), '/');
        $appKey = Config::get('services.billing_api.app_key');

        try {
            $response = Http::withHeaders([
                'X-App-Key' => $appKey,
            ])->get($baseUri . '/external/employees');
        } catch (\Throwable $e) {
            Log::warning('Employee lookup failed', [
                'error' => $e->getMessage(),
            ]);

            return [
                'data' => null,
                'message' => 'Tidak dapat terhubung ke layanan pegawai',
            ];
        }

        if (! $response->successful() || ! $response->json('success')) {
            return [
                'data' => null,
                'message' => $response->json('message') ?? 'Employees not found',
            ];
        }

        return [
            'data' => $response->json('data') ?? [],
            'message' => null,
        ];
    }
}
