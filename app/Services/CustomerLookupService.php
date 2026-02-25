<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CustomerLookupService
{
    protected string $baseUri;
    protected string $appKey;

    public function __construct()
    {
        $this->baseUri = rtrim(Config::get('services.billing_api.base_uri'), '/');
        $this->appKey = Config::get('services.billing_api.app_key');
    }

    public function fetchByNoSambungan(string $noSambungan): array
    {
        try {
            $response = Http::withHeaders([
                'X-App-Key' => $this->appKey,
            ])->get($this->baseUri . '/external/customers/' . $noSambungan);
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

    /**
     * Fetch all units with nested hierarchy data
     * Cached for 6 hours
     */
    public function fetchUnits(): array
    {
        try {
            return Cache::remember('customer_api:units', 6 * 60 * 60, function () {
                $response = Http::timeout(10)->withHeaders([
                    'X-App-Key' => $this->appKey,
                ])->get($this->baseUri . '/external/form/units');

                if ($response->successful()) {
                    return $response->json('data', []);
                }
                Log::warning('Failed to fetch units', ['status' => $response->status(), 'body' => $response->body()]);
                return [];
            });
        } catch (\Throwable $e) {
            Log::error('Failed to fetch units', ['error' => $e->getMessage(), 'url' => $this->baseUri . '/external/form/units']);
            return [];
        }
    }

    /**
     * Fetch desa/kelurahan by unit ID
     * Cached for 6 hours
     */
    public function fetchDesaByUnit(int $unitId): array
    {
        try {
            return Cache::remember("customer_api:desa:unit_{$unitId}", 6 * 60 * 60, function () use ($unitId) {
                $response = Http::timeout(10)->withHeaders([
                    'X-App-Key' => $this->appKey,
                ])->get($this->baseUri . "/external/form/desa/{$unitId}");

                if ($response->successful()) {
                    return $response->json('data', []);
                }
                return [];
            });
        } catch (\Throwable $e) {
            Log::error('Failed to fetch desa', ['unit_id' => $unitId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch RT/RW by desa ID
     * Cached for 6 hours
     */
    public function fetchRtRwByDesa(int $desaId): array
    {
        try {
            return Cache::remember("customer_api:rt_rw:desa_{$desaId}", 6 * 60 * 60, function () use ($desaId) {
                $response = Http::timeout(10)->withHeaders([
                    'X-App-Key' => $this->appKey,
                ])->get($this->baseUri . "/external/form/rt-rw/{$desaId}");

                if ($response->successful()) {
                    return $response->json('data', []);
                }
                return [];
            });
        } catch (\Throwable $e) {
            Log::error('Failed to fetch RT/RW', ['desa_id' => $desaId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch wilayah by unit ID
     * Cached for 6 hours
     */
    public function fetchWilayahByUnit(int $unitId): array
    {
        try {
            return Cache::remember("customer_api:wilayah:unit_{$unitId}", 6 * 60 * 60, function () use ($unitId) {
                $response = Http::timeout(10)->withHeaders([
                    'X-App-Key' => $this->appKey,
                ])->get($this->baseUri . "/external/form/wilayah/{$unitId}");

                if ($response->successful()) {
                    return $response->json('data', []);
                }
                return [];
            });
        } catch (\Throwable $e) {
            Log::error('Failed to fetch wilayah', ['unit_id' => $unitId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Fetch jalan by wilayah ID
     * Cached for 6 hours
     */
    public function fetchJalanByWilayah(int $wilayahId): array
    {
        try {
            return Cache::remember("customer_api:jalan:wilayah_{$wilayahId}", 6 * 60 * 60, function () use ($wilayahId) {
                $response = Http::timeout(10)->withHeaders([
                    'X-App-Key' => $this->appKey,
                ])->get($this->baseUri . "/external/form/jalan/{$wilayahId}");

                if ($response->successful()) {
                    return $response->json('data', []);
                }
                return [];
            });
        } catch (\Throwable $e) {
            Log::error('Failed to fetch jalan', ['wilayah_id' => $wilayahId, 'error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Clear all address change related caches
     */
    public function clearAddressChangeCaches(): void
    {
        Cache::forget('customer_api:units');
        foreach (Cache::tags(['customer_api'])->flush() as $key) {
            Cache::forget($key);
        }
    }
}
