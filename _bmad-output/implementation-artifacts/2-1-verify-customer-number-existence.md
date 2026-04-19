# Story 2.1: Verify Customer Number Existence

Status: done

## Story

As a customer,
I want to submit a nomor sambungan to check if it exists in the billing system,
So that I can begin the process of linking it to my account.

## Acceptance Criteria

1. **Given** an authenticated customer with a valid nomor sambungan **When** POST `/api/v1/customer-numbers/verify` **Then** 200 response with billing data (customer name, address) with NIK masked (e.g., `3275****0003`)
2. **Given** a nomor sambungan that does not exist in billing system **When** POST `/api/v1/customer-numbers/verify` **Then** 404 response with clear error message
3. **Given** billing API is unavailable or times out (>10s) **When** POST `/api/v1/customer-numbers/verify` **Then** 503 response with user-friendly error message, no internal details exposed **And** `BillingApiException` is thrown and caught by controller
4. **Given** billing API timeout is configurable via `BILLING_API_TIMEOUT` env variable (default 10s)
5. **Given** no authentication cookie **When** POST `/api/v1/customer-numbers/verify` **Then** 401 Unauthorized
6. **Given** a non-customer user **When** POST `/api/v1/customer-numbers/verify` **Then** 403 Forbidden

## Tasks / Subtasks

- [x] Task 1: Create `BillingApiException` (AC: #3)
  - [x] Create `app/Exceptions/BillingApiException.php` extending `RuntimeException`
  - [x] Architecture requirement AR9

- [x] Task 2: Add billing API timeout config (AC: #4)
  - [x] Add `'timeout' => env('BILLING_API_TIMEOUT', 10)` to `config/services.php` under `billing_api` key
  - [x] Add `Http::timeout()` call to `CustomerLookupService::fetchByNoSambungan()`
  - [x] Throw `BillingApiException` on connection failures (Throwable catch block) — Option A: `throwOnError` param
  - [x] Keep return format unchanged for backward compatibility with Filament callers

- [x] Task 3: Create `VerifyRequest` form request (AC: #1)
  - [x] Create `app/Http/Requests/Api/V1/CustomerNumber/VerifyRequest.php`
  - [x] Field: `no_sambungan` — `required|string|max:20`

- [x] Task 4: Create `CustomerNumberController` with `verify()` (AC: #1, #2, #3)
  - [x] Create `app/Http/Controllers/Api/V1/CustomerNumberController.php`
  - [x] `verify()` method: call `CustomerLookupService::fetchByNoSambungan()`, handle response
  - [x] On success (data found): return 200 with masked billing data
  - [x] On not found (data null, no exception): return 404 `{message: "..."}`
  - [x] On billing error (BillingApiException): return 503 `{message: "Layanan billing tidak tersedia."}`
  - [x] NIK masking: mask `no_ktp` field → `3275****0003` format (first 4 + `****` + last 4)

- [x] Task 5: Wire route (AC: #5, #6)
  - [x] Uncomment and wire `POST /customer-numbers/verify` in `routes/api_v1.php`
  - [x] Route is inside `auth:sanctum + customer + throttle:api` group (already set up)

- [x] Task 6: Create feature tests (AC: all)
  - [x] Create `tests/Feature/Api/V1/CustomerNumber/VerifyTest.php`
  - [x] Test: successful verify returns 200 with masked data
  - [x] Test: NIK is masked in response (never full NIK)
  - [x] Test: response contains expected fields (nama, alamat, no_sambungan)
  - [x] Test: non-existent nomor returns 404
  - [x] Test: billing API failure returns 503
  - [x] Test: missing no_sambungan returns 422
  - [x] Test: unauthenticated returns 401
  - [x] Test: non-customer returns 403
  - [x] Test: null NIK returns null (edge case)

### Review Findings

- [x] [Review][Decision] F2: Short NIK (≤8 chars) returned unmasked — resolved: fully mask as `'****'`
- [x] [Review][Patch] F1: HTTP 5xx from billing API treated as 404 — fixed: added server error check in service
- [x] [Review][Patch] F3: SSRF via path traversal in no_sambungan — fixed: added `regex:/^[A-Za-z0-9]+$/`
- [x] [Review][Patch] F4: 503 test missing negative assertion — fixed: added assertJsonMissingPath checks
- [x] [Review][Patch] F5: No test verifying extra billing fields excluded — fixed: added assertArrayNotHasKey checks
- [x] [Review][Defer] F6: Hardcoded API key default in config — pre-existing, diff only re-indented [config/services.php:40]
- [x] [Review][Defer] F7: Inconsistent timeout config across service methods — pre-existing, other methods hardcode timeout(10) [CustomerLookupService.php]

## Dev Notes

### Existing Service — DO NOT REINVENT

**`CustomerLookupService`** already exists at `app/Services/CustomerLookupService.php`. It is production-tested and used by Filament:
- `ComplaintsForm.php` line 51: `app(CustomerLookupService::class)->fetchByNoSambungan($state)`
- `MeterAddressChangeTable.php` line 105: `$customerService->fetchByNoSambungan($record->no_sambungan)`

**Current `fetchByNoSambungan()` behavior:**
```php
// Returns array — ALWAYS this format:
['data' => [...billing fields...], 'message' => null]       // SUCCESS
['data' => null, 'message' => 'Customer not found']          // NOT FOUND (API returned !success)
['data' => null, 'message' => 'Tidak dapat terhubung...']   // CONNECTION ERROR (Throwable)
```

**Billing API data fields** (from ComplaintsForm.php usage):
- `nama_pelanggan`, `alamat_pelanggan`, `latitude`, `longitude`
- `hp_pelanggan`, `no_telp_pelanggan`, `no_ktp`, `email`

**API endpoint:** `GET {base_uri}/external/customers/{noSambungan}` with `X-App-Key` header.

**Config** in `config/services.php`:
```php
'billing_api' => [
    'base_uri' => env('BILLING_API_BASE_URI', 'http://localhost:9000/api/v2'),
    'app_key' => env('BILLING_API_KEY', '...'),
    // ADD: 'timeout' => env('BILLING_API_TIMEOUT', 10),
],
```

### Modifying the Service — Minimal Changes Only

**NFR16:** "Uses existing CustomerLookupService with no modifications to core logic."

Allowed changes to `fetchByNoSambungan()`:
1. **Add** `Http::timeout(Config::get('services.billing_api.timeout', 10))` — adds timeout, backward compatible
2. **Change** catch block: throw `BillingApiException` instead of returning error array

**CRITICAL:** The catch block change means Filament callers that call `fetchByNoSambungan()` will now get an exception instead of the error array. Check Filament usage:
- `ComplaintsForm.php` line 51 — calls in a closure, no try-catch → will bubble up
- `MeterAddressChangeTable.php` line 105 — same

**Decision needed at dev time:** Either:
- **Option A:** Wrap the throw in a flag/parameter: `fetchByNoSambungan($no, bool $throwOnError = false)` — backward compat, Filament passes false (default), API controller passes true
- **Option B:** Throw exception always, update the 2 Filament callers to catch `BillingApiException` (they both show notifications on error — easy fix)
- **Option C:** Don't modify exception handling in service. In `CustomerNumberController`, check `$result['data'] === null && $result['message']` to distinguish errors, use string content to decide 404 vs 503 (fragile)

**Recommended: Option A** — minimal disruption, clean API.

### NIK Masking

Architecture requires NIK masked as `3275****0003` (first 4 chars + `****` + last 4 chars). The `no_ktp` field from billing API contains the full NIK.

Masking logic:
```php
function maskNik(?string $nik): ?string {
    if ($nik === null || strlen($nik) <= 8) return $nik;
    return substr($nik, 0, 4) . '****' . substr($nik, -4);
}
```

Place in a helper or directly in the controller response. Check if a `NikHelper` or masking utility exists — if not, add as a private method or create a small helper.

### Response Format

**Success (200):**
```json
{
    "data": {
        "no_sambungan": "01PNRG0001",
        "nama_pelanggan": "Pak Joko",
        "alamat_pelanggan": "Jl. Merdeka No. 5",
        "no_ktp": "3275****0003"
    }
}
```

**Not Found (404):**
```json
{"message": "Nomor sambungan tidak ditemukan."}
```

**Service Unavailable (503):**
```json
{"message": "Layanan billing sedang tidak tersedia."}
```

### Testing Strategy

**Mock `CustomerLookupService`** in feature tests using Laravel's service container:
```php
$this->mock(CustomerLookupService::class, function ($mock) {
    $mock->shouldReceive('fetchByNoSambungan')
        ->with('01PNRG0001')
        ->andReturn(['data' => [...], 'message' => null]);
});
```

For billing API failure test, mock to throw `BillingApiException`:
```php
$mock->shouldReceive('fetchByNoSambungan')
    ->andThrow(new BillingApiException('Connection timeout'));
```

Use `RefreshDatabaseCompat` trait (NOT `RefreshDatabase`) — see `tests/Concerns/RefreshDatabaseCompat.php`.

### Previous Story Learnings (from Story 1.4)

1. **actingAs($user)** — sets auth state directly, not via session. Works for testing auth middleware.
2. **Non-customer 403 tests** — always include test for non-customer user (middleware guard).
3. **FormRequest** — always use dedicated FormRequest + `$request->validated()`, never inline.
4. **Route group** — all API routes are inside `auth:sanctum + customer + throttle:api`. Just uncomment the stub.
5. **Pint** — run `./vendor/bin/pint` after changes. PSR-12 compliance.
6. **Pre-existing test failures** — 4 failures in CustomerRegistrationProcessTest, RepairReportTest, TeraMeterReportTest are pre-existing. Ignore them.

### Project Structure Notes

```
app/
├── Exceptions/
│   └── BillingApiException.php          # NEW (AR9)
├── Http/
│   ├── Controllers/Api/V1/
│   │   └── CustomerNumberController.php # NEW
│   └── Requests/Api/V1/
│       └── CustomerNumber/
│           └── VerifyRequest.php        # NEW
├── Services/
│   └── CustomerLookupService.php        # MODIFY (add timeout + BillingApiException)
config/
└── services.php                         # MODIFY (add timeout key)
routes/
└── api_v1.php                           # MODIFY (uncomment verify route)
tests/Feature/Api/V1/
└── CustomerNumber/
    └── VerifyTest.php                   # NEW
```

### References

- [Source: _bmad-output/planning-artifacts/epics.md#Epic 2, Story 2.1]
- [Source: _bmad-output/planning-artifacts/architecture.md#External Integration Boundary, Error Handling Standard]
- [Source: _bmad-output/planning-artifacts/prd.md#FR8, NFR4, NFR14-16, NFR20]
- [Source: app/Services/CustomerLookupService.php — existing service]
- [Source: app/Filament/Resources/Complaints/Schemas/ComplaintsForm.php:51 — Filament usage of service]
- [Source: config/services.php — billing_api config]
- [Source: database/migrations/2026_04_11_175325_create_customer_numbers_table.php — migration exists]
- [Source: routes/api_v1.php:50 — route stub]

## Dev Agent Record

### Agent Model Used

### Debug Log References

### Completion Notes List

### Completion Notes List
- All 6 tasks completed, 8 tests passing (26 assertions), 63 total API V1 tests passing
- Used Option A (throwOnError param) for service modification — backward compatible
- NIK masking via private controller method, null-safe

### Change Log
- Story created: 2026-04-12 — ready-for-dev
- Story implemented: 2026-04-12 — review

### File List
- `app/Exceptions/BillingApiException.php` — NEW
- `app/Http/Controllers/Api/V1/CustomerNumberController.php` — NEW
- `app/Http/Requests/Api/V1/CustomerNumber/VerifyRequest.php` — NEW
- `app/Services/CustomerLookupService.php` — MODIFIED (timeout + throwOnError param)
- `config/services.php` — MODIFIED (added timeout key)
- `routes/api_v1.php` — MODIFIED (uncommented verify route + import)
- `tests/Feature/Api/V1/CustomerNumber/VerifyTest.php` — NEW (8 tests)
