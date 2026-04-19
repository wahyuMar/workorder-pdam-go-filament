# Story 2.3: List, View & Remove Verified Numbers

Status: done

## Story

As a customer,
I want to view all my verified nomor sambungan and remove ones I no longer need,
So that I can manage which numbers are linked to my account.

## Acceptance Criteria

1. **Given** an authenticated customer with verified numbers
   **When** `GET /api/v1/customer-numbers`
   **Then** 200 response with list of all verified nomor sambungan for this user only

2. **Given** an authenticated customer with a specific verified nomor
   **When** `GET /api/v1/customer-numbers/{customerNumber}/billing`
   **Then** 200 response with billing data for that nomor sambungan (NIK masked)

3. **Given** an authenticated customer wanting to unlink a nomor
   **When** `DELETE /api/v1/customer-numbers/{customerNumber}`
   **Then** 200 response, record hard-deleted from `customer_numbers` table

4. **Given** a customer trying to access/delete another customer's nomor
   **When** any customer-numbers endpoint with non-owned `{customerNumber}`
   **Then** 404 Not Found (ownership enforced via `scopeOwnedBy` — does not reveal record existence)

5. **Given** an unauthenticated request
   **When** any customer-numbers endpoint
   **Then** 401 Unauthorized

6. **Given** a non-customer role user
   **When** any customer-numbers endpoint
   **Then** 403 Forbidden (via `EnsureCustomerRole` middleware)

7. **Given** billing API is unavailable when fetching billing data
   **When** `GET /api/v1/customer-numbers/{customerNumber}/billing`
   **Then** 503 with user-friendly error, no internal details

## Tasks / Subtasks

- [x] Task 1: Add `index()` method to CustomerNumberController (AC: 1, 4, 5, 6)
  - [x] Query `CustomerNumber::ownedBy($request->user()->id)->get()`
  - [x] Return `CustomerNumberResource::collection($numbers)`
  - [x] No pagination needed — a customer's linked numbers will be a small set
- [x] Task 2: Add `billing()` method to CustomerNumberController (AC: 2, 4, 7)
  - [x] Find `CustomerNumber` by route param `{customerNumber}` using `no_sambungan` + `ownedBy()` scope
  - [x] If not found → 404 `"Nomor sambungan tidak ditemukan."`
  - [x] Call `CustomerLookupService::fetchByNoSambungan($noSambungan, throwOnError: true)`
  - [x] Handle `BillingApiException` → 503 (same pattern as `verify()`)
  - [x] Handle null billing data → 404 `"Data billing tidak ditemukan."`
  - [x] Mask NIK using existing `maskNik()` private method
  - [x] Return billing data in same format as `verify()` response
- [x] Task 3: Add `destroy()` method to CustomerNumberController (AC: 3, 4)
  - [x] Find `CustomerNumber` by route param `{customerNumber}` using `no_sambungan` + `ownedBy()` scope
  - [x] If not found → 404 `"Nomor sambungan tidak ditemukan."`
  - [x] Hard delete `$customerNumber->delete()`
  - [x] Return 200 `"Nomor sambungan berhasil dihapus dari akun Anda."`
- [x] Task 4: Uncomment routes in `routes/api_v1.php` (AC: all)
  - [x] Uncomment `GET /customer-numbers` → `index` (line ~53)
  - [x] Uncomment `GET /customer-numbers/{no}/billing` → `billing` (line ~54)
  - [x] Uncomment `DELETE /customer-numbers/{no}` → `destroy` (line ~55)
  - [x] Route param `{no}` maps to `no_sambungan` (NOT model ID)
- [x] Task 5: Create `IndexTest.php` (AC: 1, 4, 5, 6)
  - [x] Test: authenticated customer with numbers → 200 with `CustomerNumberResource` collection format
  - [x] Test: only returns own numbers (create numbers for 2 users, verify isolation)
  - [x] Test: customer with no numbers → 200 with empty `data: []`
  - [x] Test: response uses correct resource format (`id`, `no_sambungan`, `verified_at`, `created_at`)
  - [x] Test: unauthenticated → 401
  - [x] Test: non-customer role → 403
- [x] Task 6: Create `BillingTest.php` (AC: 2, 4, 7)
  - [x] Test: verified number with billing data → 200 with masked NIK
  - [x] Test: NIK is masked (not full value) in response
  - [x] Test: non-owned number → 404 (not 403 — ownership via scope)
  - [x] Test: non-existent number → 404
  - [x] Test: billing API failure → 503
  - [x] Test: billing returns null data → 404
  - [x] Test: unauthenticated → 401
  - [x] Test: non-customer role → 403
- [x] Task 7: Create `DestroyTest.php` (AC: 3, 4)
  - [x] Test: delete own number → 200, record removed from DB
  - [x] Test: non-owned number → 404
  - [x] Test: non-existent number → 404
  - [x] Test: unauthenticated → 401
  - [x] Test: non-customer role → 403

### Review Findings

- [x] [Review][Patch] F1: Use canonical DB value in billing() — `$no` (user route input) is forwarded to billing API and response JSON instead of `$customerNumber->no_sambungan`. On case-insensitive DB collations, casing mismatch could cause billing API failure or inconsistent response. [CustomerNumberController.php:125,142]
- [x] [Review][Defer] F2: Missing `verified_at` guard on index/billing/destroy — currently safe because only `confirm()` creates records (always with `verified_at`). Defensive `whereNotNull('verified_at')` would close future gap. — deferred, pre-existing design

## Dev Notes

### Architecture Compliance

- **Controller**: Add methods to existing `CustomerNumberController` — do NOT create new controllers
- **Route params**: The commented routes use `{no}` for `no_sambungan`, NOT model ID. Do NOT use route model binding. Manually query `CustomerNumber::ownedBy(...)->where('no_sambungan', $no)->first()`
- **IDOR prevention**: Use `scopeOwnedBy()` for ALL queries. Non-owned records return 404 (not 403) — this is more secure as it doesn't reveal record existence. The AC's "403" intent is satisfied by preventing access entirely.
- **Hard delete**: Model has NO `SoftDeletes` trait. Use `->delete()` for permanent removal.
- **No new FormRequests needed**: `index()` has no body, `billing()` and `destroy()` only need route params. Validation is handled by the scope + 404 pattern.

### Response Formats

**Index response** — standard Laravel collection (no pagination needed for small set):
```json
{
  "data": [
    { "id": 1, "no_sambungan": "01PNRG0001", "verified_at": "2026-...", "created_at": "2026-..." },
    { "id": 2, "no_sambungan": "01PNRG0002", "verified_at": "2026-...", "created_at": "2026-..." }
  ]
}
```

**Billing response** — reuse same format as `verify()` method (masked NIK):
```json
{
  "data": {
    "nama": "John Doe",
    "alamat": "Jl. Contoh No. 1",
    "no_ktp": "3275****0003",
    ...other billing fields...
  }
}
```

**Destroy response**:
```json
{ "message": "Nomor sambungan berhasil dihapus dari akun Anda." }
```

### Billing Method — Reuse Existing Patterns

The `billing()` method follows the EXACT same billing API integration as `verify()`:
1. `CustomerLookupService::fetchByNoSambungan($noSambungan, throwOnError: true)`
2. `BillingApiException` → 503 `"Layanan billing sedang tidak tersedia."`
3. Null data → 404 `"Data billing tidak ditemukan."`
4. Mask NIK using existing `maskNik()` private method (already in controller)
5. Return structured response

**Key difference from `verify()`**: The billing endpoint requires the number to be **linked to the customer's account** first (ownership check via scope). The verify endpoint works for any number without account linking.

### Error Messages (Indonesian — match existing patterns)

| Scenario | Message | Status |
|----------|---------|--------|
| Number not found / not owned | `"Nomor sambungan tidak ditemukan."` | 404 |
| Billing API failure | `"Layanan billing sedang tidak tersedia."` | 503 |
| Billing data not found | `"Data billing tidak ditemukan."` | 404 |
| Number removed | `"Nomor sambungan berhasil dihapus dari akun Anda."` | 200 |

### Route Reference (`routes/api_v1.php`)

Lines ~53-55 contain pre-scaffolded commented routes:
```php
// Route::get('/customer-numbers', [CustomerNumberController::class, 'index'])->name('api.v1.customer-numbers.index');
// Route::get('/customer-numbers/{no}/billing', [CustomerNumberController::class, 'billing'])->name('api.v1.customer-numbers.billing');
// Route::delete('/customer-numbers/{no}', [CustomerNumberController::class, 'destroy'])->name('api.v1.customer-numbers.destroy');
```
Simply uncomment these. The `{no}` param is received as `$no` in controller method signatures.

### Testing Patterns (from Story 2.2)

- **Trait**: `RefreshDatabaseCompat` (NOT `RefreshDatabase`)
- **Role setup**: `Role::findOrCreate('customer', 'web')` in `setUp()`
- **User helper**: Extract `createCustomerUser()` — same pattern as ConfirmTest/VerifyTest
- **Billing mock**: `$this->mock(CustomerLookupService::class, fn (MockInterface $mock) => ...)` with `->shouldReceive('fetchByNoSambungan')->with($noSambungan, true)->once()->andReturn(...)`
- **Factory alternative**: No `CustomerNumber::factory()` exists. Create records directly: `CustomerNumber::create(['user_id' => $user->id, 'no_sambungan' => '01PNRG0001', 'verified_at' => now()])`
- **Test file naming**: `{Model}{Action}Test` → `IndexTest.php`, `BillingTest.php`, `DestroyTest.php` in `tests/Feature/Api/V1/CustomerNumber/`
- **Pre-existing test failures**: Ignore `CustomerRegistrationProcessTest`, `RepairReportTest`, `TeraMeterReportTest` — these are pre-existing and unrelated

### Code Location Summary

| File | Action |
|------|--------|
| `app/Http/Controllers/Api/V1/CustomerNumberController.php` | Add `index()`, `billing()`, `destroy()` methods |
| `routes/api_v1.php` | Uncomment 3 route lines |
| `tests/Feature/Api/V1/CustomerNumber/IndexTest.php` | CREATE — 6 tests |
| `tests/Feature/Api/V1/CustomerNumber/BillingTest.php` | CREATE — 8 tests |
| `tests/Feature/Api/V1/CustomerNumber/DestroyTest.php` | CREATE — 5 tests |

### Previous Story Intelligence (from 2.2 review)

1. **Mock conflict fix**: For tests needing multiple billing calls, use `->twice()` on single mock — NOT two separate `$this->mock()` calls
2. **Non-array billing guard**: Check `$result['data'] === null || !is_array($result['data'])` before accessing billing data (from F4 patch)
3. **Empty billing NIK guard**: If billing `no_ktp` trims to empty string, handle appropriately (from F2 patch) — relevant for billing endpoint's NIK masking
4. **Service mock signature**: Always include second param `true` for `throwOnError` → `->with('01PNRG0001', true)`
5. **Deferred: Rate limiting** (F6) — Customer number endpoints share generic `throttle:api` (120/min). Stricter per-resource throttle deferred.
6. **Deferred: Billing 4xx→null** (F7) — Service returns null for any non-5xx billing response. Pre-existing behavior.

### References

- [Source: _bmad-output/planning-artifacts/epics.md#Epic 2, Story 2.3 lines 367-389]
- [Source: _bmad-output/planning-artifacts/architecture.md#API Naming lines 199-203]
- [Source: _bmad-output/planning-artifacts/architecture.md#Process Patterns lines 302-339]
- [Source: _bmad-output/planning-artifacts/architecture.md#IDOR Prevention line 135]
- [Source: _bmad-output/planning-artifacts/architecture.md#Format Patterns lines 273-301]
- [Source: _bmad-output/planning-artifacts/prd.md#Customer Numbers endpoints lines 298-306]
- [Source: _bmad-output/implementation-artifacts/2-2-confirm-ownership-link-customer-number.md#Review Findings]

## Dev Agent Record

### Agent Model Used

Claude Opus 4.6

### Debug Log References

### Completion Notes List

- All 7 tasks implemented in single pass — no failures or retries needed
- 3 controller methods added: `index()`, `billing()`, `destroy()` — all follow existing patterns
- `billing()` reuses `verify()` pattern exactly (billing API call, BillingApiException handling, NIK masking via `maskNik()`)
- `destroy()` uses hard delete — model has no SoftDeletes trait
- Ownership enforced via `scopeOwnedBy()` + `where('no_sambungan', $no)` — returns 404 for non-owned (more secure than 403)
- 19 new tests across 3 files (63 assertions), 104 total passing, 4 pre-existing failures unchanged
- Pint clean, no regressions

### File List

- `app/Http/Controllers/Api/V1/CustomerNumberController.php` (modified) — added `index()`, `billing()`, `destroy()` + Request/AnonymousResourceCollection imports
- `routes/api_v1.php` (modified) — uncommented 3 customer-number routes (index, billing, destroy)
- `tests/Feature/Api/V1/CustomerNumber/IndexTest.php` (created) — 6 tests
- `tests/Feature/Api/V1/CustomerNumber/BillingTest.php` (created) — 8 tests
- `tests/Feature/Api/V1/CustomerNumber/DestroyTest.php` (created) — 5 tests
