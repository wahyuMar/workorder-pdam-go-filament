# Story 5.1: Submit Complaint

Status: done

## Story

As a customer,
I want to submit a complaint for one of my verified customer numbers,
So that I can report issues with my water service.

## Acceptance Criteria

1. **Happy path: valid submission** — Given an authenticated customer with a verified nomor sambungan, valid complaint type, and description, When POST `/api/v1/complaints`, Then 201 response with complaint data including auto-generated `PGD-*` number.
2. **Unverified nomor** — Given a nomor sambungan that is NOT verified/linked to customer, When POST `/api/v1/complaints`, Then 422 validation error — complaints only allowed for verified numbers.
3. **Auto-fill & auto-set behavior** — Given the submission with a verified nomor, When record is created, Then customer `nama` and `alamat` are auto-filled from billing data, `sumber` set to `mobile_apps`, initial `status` and `priority` auto-set, `user_id` set from auth, `tanggal` set to now(), and `no_pengaduan` auto-generated via `Complaint::boot()`.
4. **Photos happy path (≤5)** — Given up to 5 photo references from upload endpoint, When included in complaint submission, Then photo references are stored in the `foto` JSON array.
5. **Photos over limit** — Given more than 5 photo references, When POST `/api/v1/complaints`, Then 422 validation error on `foto` field.

## Tasks / Subtasks

- [x] Task 1: Update Complaint Model (AC: #1, #3)
  - [x] Add `user_id` to `$fillable` array (deferred from Story 1.1)
  - [x] Add `use Illuminate\Database\Eloquent\Builder;` import
  - [x] Add `user()` → `belongsTo(User::class)` relationship (deferred from Story 1.1)
  - [x] Add `scopeOwnedBy(Builder $query, int $userId): Builder` method — pattern: `return $query->where('user_id', $userId);`
  - [x] Preserve ALL existing code — do NOT modify `boot()`, `generateNoPengaduan()`, `peekNextNoPengaduan()`, existing relationships, casts, or fillable order

- [x] Task 2: Create `ComplaintResource` (AC: #1)
  - [x] Create `app/Http/Resources/Api/V1/ComplaintResource.php`
  - [x] Fields: `id`, `no_pengaduan`, `complaint_type` (via `whenLoaded`), `no_sambungan`, `nama`, `alamat`, `latitude`, `longitude`, `email`, `no_hp`, `no_ktp` (MASKED via `maskNik()`), `sumber`, `judul_pengaduan`, `isi_pengaduan`, `foto`, `tanggal`, `status`, `priority`, `created_at`
  - [x] Exclude `user_id` from response
  - [x] `complaint_type`: use `$this->whenLoaded('complaintType', fn () => ['id' => $this->complaintType->id, 'name' => $this->complaintType->name])`
  - [x] Copy `maskNik()` private method from `RegistrationResource` (known duplication — deferred trait extraction)

- [x] Task 3: Create `Complaint/StoreRequest` (AC: #1, #2, #4, #5)
  - [x] Create `app/Http/Requests/Api/V1/Complaint/StoreRequest.php`
  - [x] `authorize()` returns `true` (auth handled by middleware)
  - [x] Validation rules:
    - `no_sambungan`: `['required', 'string', Rule::exists('customer_numbers', 'no_sambungan')->where('user_id', $this->user()->id)->whereNotNull('verified_at')]`
    - `complaint_type_id`: `['required', 'integer', Rule::exists('complaint_types', 'id')->where('is_active', true)]`
    - `judul_pengaduan`: `['required', 'string', 'max:255']`
    - `isi_pengaduan`: `['required', 'string', 'max:65535']`
    - `foto`: `['nullable', 'array', 'max:5']`
    - `foto.*`: `['string', 'max:255', 'not_regex:/\.\.|\\\\|\x00/']` (path traversal protection from Story 4.1 review)
    - `latitude`: `['nullable', 'numeric', 'between:-90,90']`
    - `longitude`: `['nullable', 'numeric', 'between:-180,180']`
  - [x] Use `Illuminate\Validation\Rule` import

- [x] Task 4: Create `ComplaintController::store()` (AC: #1, #3)
  - [x] Create `app/Http/Controllers/Api/V1/ComplaintController.php`
  - [x] Constructor-inject `CustomerLookupService` (same pattern as `CustomerNumberController`)
  - [x] `store(StoreRequest $request): JsonResponse` method:
    1. Get `$data = $request->validated()`
    2. Fetch billing data: `$this->customerLookupService->fetchByNoSambungan($data['no_sambungan'], throwOnError: true)` — wrap in try/catch for `BillingApiException` → return 503
    3. If billing returns `data === null` → still proceed (use empty strings for nama/alamat — DB allows null for alamat but not nama, so fallback to `$request->user()->name`)
    4. Auto-fill from billing: `$data['nama'] = $billing['nama_pelanggan'] ?? $request->user()->name`, `$data['alamat'] = $billing['alamat_pelanggan'] ?? null`
    5. Auto-set: `$data['user_id'] = $request->user()->id`, `$data['sumber'] = 'mobile_apps'`, `$data['tanggal'] = now()`
    6. Wrap `Complaint::create($data)` in `DB::transaction()` — the model's `boot()` auto-generates `no_pengaduan` inside the transaction
    7. Refresh + load `complaintType` relation on the created record (refresh needed for DB defaults: status, priority)
    8. Return `(new ComplaintResource($complaint))->response()->setStatusCode(201)`
  - [x] Required imports: `App\Services\CustomerLookupService`, `App\Exceptions\BillingApiException`, `App\Http\Requests\Api\V1\Complaint\StoreRequest`, `App\Http\Resources\Api\V1\ComplaintResource`, `App\Models\Complaint`, `Illuminate\Http\JsonResponse`, `Illuminate\Support\Facades\DB`

- [x] Task 5: Uncomment POST /complaints route (AC: #1)
  - [x] In `routes/api_v1.php`: add `use App\Http\Controllers\Api\V1\ComplaintController;` import at top
  - [x] Uncomment line ~68: `Route::post('/complaints', [ComplaintController::class, 'store'])->name('api.v1.complaints.store');`
  - [x] Keep other complaint routes (GET index, show, timeline) commented — they belong to Story 5.2

- [x] Task 6: Create `StoreTest` feature tests (AC: #1, #2, #3, #4, #5)
  - [x] Create `tests/Feature/Api/V1/Complaint/StoreTest.php`
  - [x] Test cases (16 tests, 70 assertions):
    1. `test_can_submit_complaint_for_verified_number` — happy path, 201, check PGD-* format, check all auto-set fields
    2. `test_can_submit_complaint_with_photos` — happy path with foto array (≤5 items), verify stored
    3. `test_cannot_submit_for_unverified_number` — 422 (no_sambungan not in customer_numbers)
    4. `test_cannot_submit_for_another_users_number` — 422 (no_sambungan belongs to different user)
    5. `test_cannot_submit_with_more_than_5_photos` — 422 on foto
    6. `test_auto_fills_nama_alamat_from_billing` — verify nama/alamat from billing response
    7. `test_auto_sets_sumber_status_priority` — sumber=mobile_apps, status=pending, priority=medium
    8. `test_auto_generates_no_pengaduan` — PGD-YYYYMMDD-XXXX format
    9. `test_sets_user_id_from_auth` — user_id in DB matches auth user
    10. `test_masks_no_ktp_in_response` — response has masked NIK, DB has full
    11. `test_missing_required_fields_returns_422` — omit judul_pengaduan, isi_pengaduan, etc.
    12. `test_invalid_complaint_type_returns_422` — nonexistent or inactive complaint_type_id
    13. `test_unauthenticated_returns_401`
    14. `test_non_customer_user_returns_403`
  - [x] Test setup: use `RefreshDatabaseCompat`, `Role::findOrCreate('customer', 'web')`, `createCustomerUser()` helper
  - [x] Mock billing API: use Mockery to mock `CustomerLookupService` (matches BillingTest pattern)
  - [x] Create ComplaintType factory/record in setUp or per-test
  - [x] Create CustomerNumber (verified) for test user: `CustomerNumber::create(['user_id' => $user->id, 'no_sambungan' => '12345', 'verified_at' => now()])`
  - [x] 4 pre-existing test failures to IGNORE: `CustomerRegistrationProcessTest`, `RepairReportTest`, `TeraMeterReportTest`

## Dev Notes

### Architecture Compliance
- **Route group**: Inside `auth:sanctum` + `customer` + `throttle:api` (120/min) — same as all other API routes
- **Controller pattern**: Follow `RegistrationController::store()` structure exactly — FormRequest → transaction → auto-set → create → Resource(201)
- **IDOR protection**: Query scope (`scopeOwnedBy`) + validated `no_sambungan` must belong to auth user
- **Auto-number**: `Complaint::boot()` already handles `generateNoPengaduan()` on `creating` event — do NOT call it manually in controller. The `DB::transaction()` in controller wraps the `create()` which triggers boot, so the number generation's own transaction nests correctly.
- **NFR18**: The `generateNoPengaduan()` already uses `DB::transaction()` + `lockForUpdate()` internally — safe for concurrency
- **Form Request**: `authorize()` returns `true` — role enforcement done by `EnsureCustomerRole` middleware, not the request

### Billing Auto-Fill (FR27)
- `CustomerLookupService::fetchByNoSambungan()` returns `['data' => ['nama_pelanggan' => ..., 'alamat_pelanggan' => ..., 'no_ktp' => ...], 'message' => null]`
- On success: map `nama_pelanggan` → `nama`, `alamat_pelanggan` → `alamat`
- On `BillingApiException`: return 503 `{ "message": "Layanan billing sedang tidak tersedia." }` — same error message as `CustomerNumberController`
- On `data === null` (customer not found in billing): use `$request->user()->name` as fallback for `nama` (required field in DB)
- Constructor inject `CustomerLookupService` — same pattern as `CustomerNumberController`

### Field Auto-Set Values (FR28)
| Field | Value | Source |
|-------|-------|--------|
| `user_id` | `$request->user()->id` | Auth |
| `sumber` | `'mobile_apps'` | Hardcoded (matches Filament enum: `website`, `kantor`, `sosial_media`, `telepon`, `mobile_apps`) |
| `tanggal` | `now()` | Server time |
| `status` | `'pending'` | DB column default — do NOT set explicitly |
| `priority` | `'medium'` | DB column default — do NOT set explicitly |
| `no_pengaduan` | `PGD-YYYYMMDD-XXXX` | Auto via `Complaint::boot()` — do NOT set explicitly |

### Verified Nomor Validation
- `customer_numbers` table: `user_id` (FK), `no_sambungan` (string), `verified_at` (timestamp nullable)
- Unique constraint: `(user_id, no_sambungan)`
- Validation rule: `Rule::exists('customer_numbers', 'no_sambungan')->where('user_id', $this->user()->id)->whereNotNull('verified_at')`
- This single rule handles both "not owned" and "not verified" cases → 422

### Photo References
- `foto` column: `json` type, cast as `array` in model
- Filament stores filenames like `["complaints/abc.jpg"]` in this column
- API stores filenames from Upload endpoint (e.g., `["uploads/xxx.jpg"]`)
- Max 5 items validated in StoreRequest: `'foto' => ['nullable', 'array', 'max:5']`
- Each item validated with path traversal regex: `'foto.*' => ['string', 'max:255', 'not_regex:/\.\.|\\\\|\x00/']`

### Testing: Billing API Mocking
- `CustomerLookupService` calls external billing API via `Http::get()`
- Use `Http::fake()` to mock responses in tests:
```php
Http::fake([
    '*/external/customers/*' => Http::response([
        'success' => true,
        'data' => [
            'nama_pelanggan' => 'Test Customer',
            'alamat_pelanggan' => 'Jl. Test 123',
            'no_ktp' => '3275012345670003',
        ],
    ]),
]);
```
- For 503 test: use `Http::fake(['*/external/customers/*' => Http::response([], 500)])`

### Existing Model State (Complaint.php)
- `$fillable`: already has most fields, MISSING `user_id`
- `$casts`: `foto` → `array`, `tanggal` → `datetime`
- `boot()`: auto-generates `no_pengaduan` on `creating` if empty
- Relationships: `complaintType()`, `followUps()`, many work-order `hasOne`
- MISSING: `user()`, `scopeOwnedBy()`
- Do NOT touch existing `generateNoPengaduan()`, `peekNextNoPengaduan()`, or work-order relationships

### Project Structure Notes
- Controller: `app/Http/Controllers/Api/V1/ComplaintController.php` (new file)
- Request: `app/Http/Requests/Api/V1/Complaint/StoreRequest.php` (new file, note: subfolder `Complaint/`)
- Resource: `app/Http/Resources/Api/V1/ComplaintResource.php` (new file)
- Test: `tests/Feature/Api/V1/Complaint/StoreTest.php` (new file)
- Model: `app/Models/Complaint.php` (modify — add fillable, relationship, scope)
- Routes: `routes/api_v1.php` (modify — uncomment 1 route, add 1 import)

### Previous Story Learnings
- **Survey::create needed `created_by`** (from Story 4.2) — watch for non-nullable FKs when creating related records in tests
- **Ordering tests need `$this->travel(1)->seconds()`** for distinct timestamps
- **Path traversal regex** from Story 4.1 review: `'not_regex:/\.\.|\\\\|\x00/'` on all filename string fields
- **maskNik() is duplicated** across RegistrationResource, RegistrationListResource, CustomerNumberController — now adding a 4th copy in ComplaintResource (deferred trait extraction)
- **Use `RefreshDatabaseCompat`** trait, NOT `RefreshDatabase`
- **4 pre-existing test failures** are expected: `CustomerRegistrationProcessTest`, `RepairReportTest`, `TeraMeterReportTest`

### References

- [Source: _bmad-output/planning-artifacts/epics.md — Epic 5, Story 5.1 lines 482-510]
- [Source: _bmad-output/planning-artifacts/architecture.md — API endpoints, controller patterns, IDOR, testing]
- [Source: _bmad-output/planning-artifacts/prd.md — FR20-FR28, FR38, FR39, NFR18]
- [Source: app/Models/Complaint.php — existing model, boot(), generateNoPengaduan()]
- [Source: app/Http/Controllers/Api/V1/RegistrationController.php — store() pattern]
- [Source: app/Http/Controllers/Api/V1/CustomerNumberController.php — billing() and CustomerLookupService usage]
- [Source: app/Http/Requests/Api/V1/Registration/StoreRequest.php — FormRequest pattern]
- [Source: routes/api_v1.php lines 68-71 — commented complaint routes]
- [Source: _bmad-output/implementation-artifacts/4-1-submit-sr-registration.md — store endpoint pattern]
- [Source: _bmad-output/implementation-artifacts/4-2-list-view-own-registrations.md — scopeOwnedBy, review learnings]
- [Source: _bmad-output/implementation-artifacts/deferred-work.md — Complaint user_id, user() deferred from Story 1.1]

## Dev Agent Record

### Agent Model Used

Claude Opus 4.6

### Debug Log References

- Fixed `status`/`priority` not appearing in 201 response — DB column defaults not loaded on `create()`. Added `$complaint->refresh()` after create to pick up DB defaults before serialization.
- Used Mockery `$this->mock(CustomerLookupService::class)` instead of `Http::fake()` — matches existing BillingTest pattern and provides cleaner control over service behavior.
- Added 2 extra tests beyond story spec (inactive complaint type, billing API failure) for 16 total.

### Completion Notes List

- All 6 tasks implemented and verified
- All 5 ACs satisfied: happy path 201 with PGD-*, unverified nomor 422, auto-fill/auto-set, photos ≤5, photos >5 422
- 16 new tests (70 assertions), 158 total passing, 4 pre-existing failures unchanged
- Pint clean (367 files)
- Resolved Story 1.1 deferred items: added `user_id` to Complaint `$fillable`, added `user()` BelongsTo relationship

### Review Findings

- [x] [Review][Patch] F1: No test for latitude/longitude persistence and response [tests/Feature/Api/V1/Complaint/StoreTest.php] — FIXED: added lat/lng to happy path test with DB + response assertions
- [x] [Review][Patch] F2: No boundary test for exactly 5 photos [tests/Feature/Api/V1/Complaint/StoreTest.php] — FIXED: added `test_can_submit_complaint_with_exactly_5_photos`
- [x] [Review][Defer] F3: `no_pengaduan` remains mass-assignable in $fillable [app/Models/Complaint.php] — deferred, pre-existing. boot() guard only generates if empty; any code path mass-assigning no_pengaduan bypasses auto-generation
- [x] [Review][Defer] F4: `complaintType()` relation lacks return type declaration [app/Models/Complaint.php] — deferred, pre-existing. New `user()` method has return type but existing `complaintType()` and `followUps()` do not

### File List

- `app/Models/Complaint.php` — MODIFIED (added Builder import, BelongsTo import, user_id to $fillable, user() relationship, scopeOwnedBy())
- `app/Http/Controllers/Api/V1/ComplaintController.php` — NEW (store method with billing auto-fill, transaction)
- `app/Http/Requests/Api/V1/Complaint/StoreRequest.php` — NEW (verified nomor validation, complaint type active check, foto array max:5)
- `app/Http/Resources/Api/V1/ComplaintResource.php` — NEW (full detail resource with masked no_ktp, whenLoaded complaintType)
- `routes/api_v1.php` — MODIFIED (added ComplaintController import, uncommented POST /complaints route)
- `tests/Feature/Api/V1/Complaint/StoreTest.php` — NEW (16 tests, 70 assertions)
