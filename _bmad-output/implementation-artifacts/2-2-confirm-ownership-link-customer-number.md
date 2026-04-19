# Story 2.2: Confirm Ownership & Link Customer Number

Status: done

## Story

As a customer,
I want to confirm ownership of a nomor sambungan by providing my NIK,
So that the number is verified and linked to my account.

## Acceptance Criteria

1. **Given** a previously verified nomor sambungan and matching NIK **When** POST `/api/v1/customer-numbers/confirm` **Then** 200 response, nomor sambungan linked in `customer_numbers` table with `verified_at` timestamp
2. **Given** a nomor sambungan with non-matching NIK **When** POST `/api/v1/customer-numbers/confirm` **Then** 422 response with error indicating NIK does not match **And** customer can retry without lockout
3. **Given** a nomor sambungan already linked to this customer **When** POST `/api/v1/customer-numbers/confirm` **Then** 409 Conflict response
4. **Given** billing API unavailable or times out **When** POST `/api/v1/customer-numbers/confirm` **Then** 503 response, no internal details, no partial state persisted
5. **Given** nomor sambungan does not exist in billing system **When** POST `/api/v1/customer-numbers/confirm` **Then** 404 response
6. **Given** no authentication cookie **When** POST `/api/v1/customer-numbers/confirm` **Then** 401 Unauthorized
7. **Given** a non-customer user **When** POST `/api/v1/customer-numbers/confirm` **Then** 403 Forbidden

## Tasks / Subtasks

- [x] Task 1: Create `CustomerNumber` model (AC: #1)
  - [x] Create `app/Models/CustomerNumber.php` with `$fillable`, `casts()`, `user()` belongsTo
  - [x] Add `scopeOwnedBy($query, $userId)` scope (AR7)
  - [x] Add `customerNumbers()` hasMany relationship to `app/Models/User.php`

- [x] Task 2: Create `CustomerNumberResource` (AC: #1)
  - [x] Create `app/Http/Resources/Api/V1/CustomerNumberResource.php`
  - [x] Fields: `id`, `no_sambungan`, `verified_at`, `created_at`

- [x] Task 3: Create `ConfirmRequest` form request (AC: #1, #2)
  - [x] Create `app/Http/Requests/Api/V1/CustomerNumber/ConfirmRequest.php`
  - [x] Fields: `no_sambungan` — `required|string|max:20|regex:/^[A-Za-z0-9]+$/`
  - [x] Fields: `nik` — `required|string|max:20`

- [x] Task 4: Add `confirm()` to `CustomerNumberController` (AC: #1-#5)
  - [x] Call `CustomerLookupService::fetchByNoSambungan()` with `throwOnError: true`
  - [x] On BillingApiException → 503
  - [x] On data null → 404
  - [x] Compare submitted `nik` with billing `no_ktp` (case-insensitive trim)
  - [x] On mismatch → 422 with validation-style error on `nik` field
  - [x] On match → create `CustomerNumber` record with `verified_at = now()`
  - [x] Handle `UniqueConstraintViolationException` → 409 Conflict
  - [x] Return `CustomerNumberResource` on success

- [x] Task 5: Wire route (AC: #6, #7)
  - [x] Uncomment `POST /customer-numbers/confirm` in `routes/api_v1.php` line 52

- [x] Task 6: Create feature tests (AC: all)
  - [x] Create `tests/Feature/Api/V1/CustomerNumber/ConfirmTest.php`
  - [x] Test: matching NIK → 200, record created, verified_at set
  - [x] Test: response uses CustomerNumberResource format
  - [x] Test: NIK mismatch → 422, no record created
  - [x] Test: already linked → 409
  - [x] Test: billing API failure → 503, no record created
  - [x] Test: nomor not found in billing → 404
  - [x] Test: unauthenticated → 401
  - [x] Test: non-customer → 403
  - [x] Test: missing fields → 422
  - [x] Test: NIK never exposed unmasked in any response
  - [x] Test: retry after mismatch succeeds (no lockout)

### Review Findings
- [x] [Review][Decision] F1: Global uniqueness on no_sambungan — RESOLVED: multiple users may link same sambungan (e.g., family members). Current (user_id, no_sambungan) unique constraint is by design.
- [x] [Review][Patch] F2: Empty NIK guard — if billing has no NIK on file (no_ktp=null), return 422 "Data NIK pelanggan belum tersedia" instead of empty-to-empty match [CustomerNumberController.php:72-76]
- [x] [Review][Patch] F3: NIK validation — added `regex:/^\d+$/` to reject non-numeric input before billing API call [ConfirmRequest.php:23]
- [x] [Review][Patch] F4: Billing non-null non-array data guard — added `! is_array($result['data'])` check to prevent TypeError [CustomerNumberController.php:64]
- [x] [Review][Patch] F5: Defensive $hidden on ConfirmRequest — added `protected $hidden = ['nik']` to prevent NIK in request logs [ConfirmRequest.php:9]
- [x] [Review][Defer] F6: NIK-specific rate limit — confirm endpoint shares generic throttle:api (120/min), dedicated stricter throttle needed [routes/api_v1.php] — deferred, architecture-level change
- [x] [Review][Defer] F7: Billing 4xx → 404 — service returns null for 4xx responses (403/429), controller maps to 404 "not found" [CustomerLookupService.php] — deferred, pre-existing service behavior

## Dev Notes

### NIK Comparison Flow — CRITICAL

The confirm endpoint is **Step 2** of a 2-step verification. It is **stateless** — no server-side state from Step 1 is required. The flow:

1. Receive `{ no_sambungan, nik }` from request
2. Call `CustomerLookupService::fetchByNoSambungan($noSambungan, throwOnError: true)`
3. If `BillingApiException` → return 503 (NFR15, NFR20 — no partial state)
4. If `$result['data'] === null` → return 404 (nomor doesn't exist)
5. Compare `$request->nik` with `$result['data']['no_ktp']` — **normalize both** (trim whitespace)
6. If mismatch → return 422 with error on `nik` field (FR10: retry allowed, no lockout)
7. If match → create `CustomerNumber` record → return 200 with `CustomerNumberResource`
8. If `UniqueConstraintViolationException` → return 409 (already linked)

**CRITICAL — NIK Security (NFR8):**
- NIK from request is used ONLY for comparison — **never stored** in local DB
- NIK from billing API is used ONLY for comparison — **never returned** in response
- NIK must **never appear in logs** — do NOT log the submitted NIK or billing no_ktp
- On 422 mismatch, the error message must NOT reveal the actual NIK from billing

### Existing Code to Reuse — DO NOT REINVENT

**`CustomerLookupService`** — already modified in Story 2.1:
- `fetchByNoSambungan(string $no, bool $throwOnError = false): array`
- Returns `['data' => [...billing fields...], 'message' => null]` on success
- Billing data includes `no_ktp` (full NIK — use for comparison only)
- Throws `BillingApiException` on connection/server errors when `throwOnError: true`

**`CustomerNumberController`** — already exists from Story 2.1:
- Has constructor injection of `CustomerLookupService`
- Has `verify()` method — follow same patterns for `confirm()`
- Has private `maskNik()` method — NOT needed for confirm (NIK not returned)

**`BillingApiException`** — already exists at `app/Exceptions/BillingApiException.php`

**`VerifyRequest`** — pattern reference for `ConfirmRequest` (same `no_sambungan` regex)

### CustomerNumber Model — NEW

```php
// app/Models/CustomerNumber.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class CustomerNumber extends Model
{
    protected $fillable = ['user_id', 'no_sambungan', 'verified_at'];

    protected function casts(): array
    {
        return ['verified_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeOwnedBy(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }
}
```

Migration already exists: `database/migrations/2026_04_11_175325_create_customer_numbers_table.php`
- Schema: `id`, `user_id` (FK), `no_sambungan` (indexed), `verified_at` (nullable), timestamps
- Unique constraint: `['user_id', 'no_sambungan']`

### User Model — ADD relationship

```php
// In app/Models/User.php, add:
public function customerNumbers(): HasMany
{
    return $this->hasMany(CustomerNumber::class);
}
```

### CustomerNumberResource — NEW

```php
// app/Http/Resources/Api/V1/CustomerNumberResource.php
// Fields: id, no_sambungan, verified_at, created_at
// NO NIK field — NIK is never stored locally
```

### Response Format

**Success (200):**
```json
{
    "data": {
        "id": 1,
        "no_sambungan": "01PNRG0001",
        "verified_at": "2026-04-12T00:00:00.000000Z",
        "created_at": "2026-04-12T00:00:00.000000Z"
    }
}
```

**NIK Mismatch (422):**
```json
{
    "message": "NIK tidak sesuai dengan data pelanggan.",
    "errors": {
        "nik": ["NIK tidak sesuai dengan data pelanggan."]
    }
}
```

**Already Linked (409):**
```json
{"message": "Nomor sambungan sudah terhubung ke akun Anda."}
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

**Mock `CustomerLookupService`** — same pattern as VerifyTest:
```php
$this->mock(CustomerLookupService::class, function (MockInterface $mock) {
    $mock->shouldReceive('fetchByNoSambungan')
        ->with('01PNRG0001', true)
        ->once()
        ->andReturn(['data' => $this->fakeBillingData(), 'message' => null]);
});
```

**For duplicate/409 test:** Create the `CustomerNumber` record first, then attempt confirm.

**For retry test:** First call with wrong NIK → 422, then call with correct NIK → 200.

**NIK security assertions:** On 422 mismatch, assert response does NOT contain the actual billing NIK.

Use `RefreshDatabaseCompat` trait (NOT `RefreshDatabase`).
Use `Role::findOrCreate('customer', 'web')` in setUp().
Use `createCustomerUser()` helper — follow VerifyTest pattern exactly.

### Previous Story Learnings (from Story 2.1)

1. **SSRF protection:** Always add `regex:/^[A-Za-z0-9]+$/` on `no_sambungan` — prevents path traversal
2. **UniqueConstraintViolationException:** Use `Illuminate\Database\UniqueConstraintViolationException` — see ProfileController:27 and AuthController:29 for exact pattern
3. **Service mock:** Use `->with('01PNRG0001', true)` — second param is `throwOnError`
4. **Response field exclusion:** Whitelist fields, don't pass billing data through — test with `assertArrayNotHasKey`
5. **503 test:** Add negative assertions: `assertJsonMissingPath('exception')`, `assertJsonMissingPath('trace')`
6. **Short/null NIK masking:** Not needed here — NIK is not returned in confirm response
7. **Pre-existing test failures to ignore:** CustomerRegistrationProcessTest, RepairReportTest, TeraMeterReportTest

### Project Structure Notes

```
app/
├── Models/
│   ├── CustomerNumber.php            # NEW — model with scopeOwnedBy
│   └── User.php                      # MODIFY — add customerNumbers() hasMany
├── Http/
│   ├── Controllers/Api/V1/
│   │   └── CustomerNumberController.php  # MODIFY — add confirm() method
│   ├── Requests/Api/V1/
│   │   └── CustomerNumber/
│   │       ├── VerifyRequest.php     # EXISTS (Story 2.1)
│   │       └── ConfirmRequest.php    # NEW
│   └── Resources/Api/V1/
│       └── CustomerNumberResource.php    # NEW
routes/
└── api_v1.php                        # MODIFY — uncomment confirm route
tests/Feature/Api/V1/
└── CustomerNumber/
    ├── VerifyTest.php                # EXISTS (Story 2.1)
    └── ConfirmTest.php               # NEW (~11 tests)
```

### References

- [Source: _bmad-output/planning-artifacts/epics.md#Epic 2, Story 2.2]
- [Source: _bmad-output/planning-artifacts/prd.md#FR8-FR10, FR38-FR39, NFR4, NFR8-9, NFR13-16, NFR20]
- [Source: _bmad-output/planning-artifacts/architecture.md#External Integration, Error Handling, Data Contracts]
- [Source: _bmad-output/planning-artifacts/prd.md#Journey 4 (Pak Joko) — NIK mismatch & retry scenario]
- [Source: app/Services/CustomerLookupService.php — existing service, fetchByNoSambungan()]
- [Source: app/Http/Controllers/Api/V1/CustomerNumberController.php — existing controller from Story 2.1]
- [Source: database/migrations/2026_04_11_175325_create_customer_numbers_table.php — migration exists]
- [Source: routes/api_v1.php:52 — route stub]
- [Source: _bmad-output/implementation-artifacts/2-1-verify-customer-number-existence.md — Story 2.1 learnings]

## Dev Agent Record

### Agent Model Used
Claude Opus 4.6

### Debug Log References
- Fixed retry test mock conflict: used `->twice()` on single mock instead of two separate mocks

### Completion Notes List
- Task 1: Created CustomerNumber model with $fillable, casts, user() belongsTo, scopeOwnedBy. Added customerNumbers() hasMany to User model.
- Task 2: Created CustomerNumberResource API resource (id, no_sambungan, verified_at, created_at). NIK never exposed.
- Task 3: Created ConfirmRequest with no_sambungan (regex SSRF protection) and nik validation.
- Task 4: Added confirm() method to CustomerNumberController — billing API lookup, NIK comparison (case-insensitive, trimmed), UniqueConstraintViolationException for 409.
- Task 5: Wired POST /customer-numbers/confirm route.
- Task 6: Created 11 feature tests covering all 7 AC scenarios (48 assertions). All pass.
- Full suite: 83 pass, 4 pre-existing failures (unchanged).

### Change Log
- Story created: 2026-04-12 — ready-for-dev
- Implementation complete: 2026-04-12 — all 6 tasks done, 11 tests pass, status → review

### File List
- app/Models/CustomerNumber.php (NEW)
- app/Models/User.php (MODIFIED — added customerNumbers() hasMany, HasMany import)
- app/Http/Resources/Api/V1/CustomerNumberResource.php (NEW)
- app/Http/Requests/Api/V1/CustomerNumber/ConfirmRequest.php (NEW)
- app/Http/Controllers/Api/V1/CustomerNumberController.php (MODIFIED — added confirm(), imports)
- routes/api_v1.php (MODIFIED — uncommented confirm route)
- tests/Feature/Api/V1/CustomerNumber/ConfirmTest.php (NEW — 11 tests)
