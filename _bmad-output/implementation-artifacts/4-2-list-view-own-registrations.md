# Story 4.2: List & View Own Registrations

Status: done

## Story

As a customer,
I want to view my SR registrations and their current status,
So that I can track the progress of my applications.

## Acceptance Criteria

1. **Given** an authenticated customer with registrations
   **When** `GET /api/v1/registrations`
   **Then** 200 with paginated list of **own registrations only** using `RegistrationListResource`
   **And** response includes standard Laravel pagination envelope (`data`, `links`, `meta`)

2. **Given** an authenticated customer
   **When** `GET /api/v1/registrations/{id}`
   **Then** 200 with full registration detail using `RegistrationResource` (includes survey status)

3. **Given** a customer trying to access **another** customer's registration
   **When** `GET /api/v1/registrations/{id}`
   **Then** 404 Not Found (scope-based IDOR protection — does NOT leak record existence)

4. **Given** pagination parameters `?page=2&per_page=15`
   **When** `GET /api/v1/registrations`
   **Then** proper pagination with `meta` (current_page, last_page, per_page, total) and `links` (first, last, prev, next)

5. **Given** any registration response (list or detail)
   **When** `no_ktp` field is present
   **Then** NIK is masked (e.g., `3275****0003`) per FR38

6. **Given** an unauthenticated request
   **When** `GET /api/v1/registrations` or `GET /api/v1/registrations/{id}`
   **Then** 401 Unauthenticated

7. **Given** an authenticated user without `customer` role
   **When** `GET /api/v1/registrations` or `GET /api/v1/registrations/{id}`
   **Then** 403 Forbidden

## Tasks / Subtasks

- [x] Task 1: Add `scopeOwnedBy()` to `CustomerRegistration` model (AC: #1, #3)
  - [x] Add `scopeOwnedBy(Builder $query, int $userId): Builder` — identical signature to `CustomerNumber::scopeOwnedBy()`
  - [x] Add `use Illuminate\Database\Eloquent\Builder;` import
  - [x] Place method after the last relationship (`survey()`) at line ~131

- [x] Task 2: Create `RegistrationListResource` (AC: #1, #4, #5)
  - [x] Create `app/Http/Resources/Api/V1/RegistrationListResource.php`
  - [x] Compact fields: `id`, `no_surat`, `nama_lengkap`, `program_id`, `no_ktp` (MASKED), `source`, `tanggal`, `has_survey` (boolean), `created_at`
  - [x] Reuse identical `maskNik()` private method from existing `RegistrationResource`
  - [x] `has_survey` = `$this->survey()->exists()` (lightweight check, no eager load needed)

- [x] Task 3: Update `RegistrationResource` for survey status (AC: #2, #5)
  - [x] Add `has_survey` boolean field (same as list)
  - [x] Add `survey` nested object when survey exists: `{ no_survey, tanggal_survey }` (or `null`)
  - [x] Use null-safe accessor: `$this->whenLoaded('survey', fn() => [...])` to avoid N+1
  - [x] Preserve all existing fields — do NOT remove or reorder anything

- [x] Task 4: Add `index()` and `show()` to `RegistrationController` (AC: #1, #2, #3)
  - [x] `index(Request $request): AnonymousResourceCollection`
  - [x] `show(Request $request, int $registration): JsonResponse`
  - [x] Add required imports: `Request`, `AnonymousResourceCollection`, `RegistrationListResource`

- [x] Task 5: Uncomment GET routes (AC: #1, #2, #6, #7)
  - [x] Uncomment line 61: `Route::get('/registrations', [RegistrationController::class, 'index'])->name('api.v1.registrations.index');`
  - [x] Uncomment line 62: `Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('api.v1.registrations.show');`
  - [x] No new imports needed — `RegistrationController` import already exists at line 4

- [x] Task 6: Create `IndexTest` feature tests (AC: #1, #4, #5, #6, #7)
  - [x] Create `tests/Feature/Api/V1/Registration/IndexTest.php`
  - [x] 9 test cases: paginated list, ownership scoping, empty state, pagination params, NIK masking, has_survey, ordering, 401, 403

- [x] Task 7: Create `ShowTest` feature tests (AC: #2, #3, #5, #6, #7)
  - [x] Create `tests/Feature/Api/V1/Registration/ShowTest.php`
  - [x] 8 test cases: detail view, IDOR 404, non-existent 404, NIK masking, survey data, no survey null, 401, 403

### Review Findings

- [x] [Review][Patch] N+1 query on `has_survey` — fixed with `withExists('survey')` + `$this->survey_exists`
- [x] [Review][Patch] Unbounded `per_page` — fixed with `min(..., 100)` clamp
- [x] [Review][Defer] maskNik() duplicated across 3 classes (RegistrationResource, RegistrationListResource, CustomerNumberController) — deferred, extract to shared trait when next touched
- [x] [Review][Defer] Upload paths returned as raw storage strings, not URLs — deferred, pre-existing pattern from Story 4.1 (Filament stores same way)

## Dev Notes

### Architecture Compliance

- **Route placement**: Inside `auth:sanctum` + `customer` + `throttle:api` (120/min) group at `routes/api_v1.php:33-69`. Routes already scaffolded (commented) at lines 61-62.
- **IDOR defense**: Scope-based (`scopeOwnedBy`) for BOTH index and show. The `show()` method uses scope + `findOrFail()` which returns 404 for non-owned records. This is intentional — returning 404 instead of 403 prevents information leakage about record existence. Follows `CustomerNumberController` pattern exactly.
- **Pagination**: This is the **FIRST paginated endpoint** in the codebase. Use Laravel's `->paginate()` which returns standard envelope with `data`, `links`, `meta`. Default `per_page=15` (architecture spec). Accept `?per_page` query param with `$request->integer('per_page', 15)`.
- **Two resources**: Architecture mandates `RegistrationListResource` (compact, SRR-02) and `RegistrationResource` (full detail, SRR-03). Both mask NIK.
- **Eager loading**: For `show()`, eager load `['program', 'survey']` to prevent N+1. For `index()`, no eager loading needed — `RegistrationListResource` uses lightweight `$this->survey()->exists()`.

### scopeOwnedBy Pattern (MUST follow exactly)

Copy from `app/Models/CustomerNumber.php:25-28`:
```php
public function scopeOwnedBy(Builder $query, int $userId): Builder
{
    return $query->where('user_id', $userId);
}
```
Usage: `CustomerRegistration::ownedBy($request->user()->id)->...`

### show() Method — Route Parameter Convention

The route `{registration}` should be received as `int $registration` (NOT route-model binding). This matches the scope-based IDOR pattern from `CustomerNumberController`:
```php
public function show(Request $request, int $registration): JsonResponse
{
    $reg = CustomerRegistration::ownedBy($request->user()->id)
        ->with(['program', 'survey'])
        ->findOrFail($registration);
    return (new RegistrationResource($reg))->response()->setStatusCode(200);
}
```
Using `findOrFail()` throws `ModelNotFoundException` which Laravel auto-converts to 404. Do NOT catch it manually.

### RegistrationListResource Fields

Compact representation for list/card views:
```
id, no_surat, nama_lengkap, program_id, no_ktp (MASKED), source, tanggal, has_survey, created_at
```
- `has_survey`: `(bool) $this->survey()->exists()` — no eager load, simple DB check
- `tanggal`: `$this->tanggal?->toIso8601String()`
- `created_at`: `$this->created_at?->toIso8601String()`

### RegistrationResource Update (Survey Status for FR17)

Add to the existing `toArray()` return, after `created_at`:
```php
'has_survey' => (bool) $this->survey()->exists(),
'survey' => $this->whenLoaded('survey', fn () => [
    'no_survey' => $this->survey->no_survey,
    'tanggal_survey' => $this->survey->tanggal_survey?->toIso8601String(),
]),
```
If survey is not eager-loaded, `whenLoaded` returns nothing (no key in response). When eager-loaded (in `show()`), returns survey summary or `null`.

### NIK Masking (Shared Pattern — FR38)

Both resources need `maskNik()`. Current implementation in `RegistrationResource:74-85`:
```php
private function maskNik(?string $nik): ?string
{
    if ($nik === null || mb_strlen(trim($nik)) === 0) {
        return null;
    }
    $nik = trim($nik);
    if (mb_strlen($nik) <= 8) {
        return '****';
    }
    return mb_substr($nik, 0, 4) . '****' . mb_substr($nik, -4);
}
```
Copy this exactly into `RegistrationListResource`. Do NOT extract to shared trait yet (only 2 usages).

### Testing Patterns (MUST follow)

- Use `RefreshDatabaseCompat` trait (NOT `RefreshDatabase`)
- `setUp()`: `Role::findOrCreate('customer', 'web')`
- Private `createCustomerUser(array $overrides = [])` helper
- Auth test pair: `test_unauthenticated_returns_401()` + `test_non_customer_user_returns_403()`
- Use `$this->actingAs($user)->getJson(...)` for GET requests
- Assert pagination structure: `assertJsonStructure(['data', 'links', 'meta'])`
- Assert ownership: create records for user A and B, verify user A's index doesn't contain B's records
- **4 pre-existing test failures to ignore**: `CustomerRegistrationProcessTest`, `RepairReportTest`, `TeraMeterReportTest`

### Creating Test Registrations

In tests, create registrations using the model directly (no need to go through controller):
```php
private function createRegistration(User $user, array $overrides = []): CustomerRegistration
{
    return CustomerRegistration::create(array_merge([
        'user_id' => $user->id,
        'source' => 'mobile',
        'no_surat' => 'SRPB-' . fake()->unique()->randomNumber(5),
        'nama_lengkap' => fake()->name(),
        'tanggal' => now(),
    ], $overrides));
}
```

### Survey Test Setup

For `has_survey` tests, create a survey linked to a registration:
```php
use App\Models\Survey;

Survey::create([
    'customer_registration_id' => $registration->id,
    'no_survey' => 'SRV-00001',
    'tanggal_survey' => now(),
    // other fields use model defaults ($guarded = [])
]);
```

### Previous Story Learnings (from Story 4.1)

1. **FormRequest authorize()**: Always returns `true` — auth via middleware. But Story 4.2 `index()` and `show()` use plain `Request` (no body validation needed).
2. **Controller base class**: Must `use App\Http\Controllers\Controller;`
3. **Return pattern**: `(new Resource($model))->response()->setStatusCode(200)` for single resource. `Resource::collection($paginator)` for paginated collection.
4. **Upload path traversal**: Review found path traversal issue in StoreRequest — NOT applicable to read endpoints.
5. **Pre-existing race condition**: `generateNoSurat()` has a race condition — not relevant to read endpoints.
6. **Pint clean**: Run `./vendor/bin/pint` after implementation to fix any style issues.

### File Structure

```
app/
├── Http/
│   ├── Controllers/Api/V1/RegistrationController.php  ← MODIFY (add index + show)
│   └── Resources/Api/V1/
│       ├── RegistrationResource.php                   ← MODIFY (add survey status)
│       └── RegistrationListResource.php               ← NEW
├── Models/CustomerRegistration.php                     ← MODIFY (add scopeOwnedBy)
routes/api_v1.php                                       ← MODIFY (uncomment lines 61-62)
tests/Feature/Api/V1/Registration/
├── IndexTest.php                                       ← NEW
└── ShowTest.php                                        ← NEW
```

### References

- [Source: _bmad-output/planning-artifacts/epics.md — Epic 4, Story 4.2 lines 452-476]
- [Source: _bmad-output/planning-artifacts/architecture.md — GET endpoints, pagination, scopeOwnedBy, RegistrationListResource]
- [Source: _bmad-output/planning-artifacts/prd.md — FR16, FR17, FR38, FR39, NFR1]
- [Source: app/Models/CustomerRegistration.php — model with 47 fillable fields, relationships, no existing scopes]
- [Source: app/Models/CustomerNumber.php:25-28 — scopeOwnedBy reference implementation]
- [Source: app/Http/Controllers/Api/V1/CustomerNumberController.php:24-29 — index() pattern]
- [Source: app/Http/Controllers/Api/V1/RegistrationController.php — existing store(), add index+show]
- [Source: app/Http/Resources/Api/V1/RegistrationResource.php — existing full resource, add survey status]
- [Source: app/Models/Survey.php — customerRegistration() belongsTo, tanggal_survey cast]
- [Source: routes/api_v1.php:61-62 — commented GET routes ready to uncomment]
- [Source: _bmad-output/implementation-artifacts/4-1-submit-sr-registration.md — previous story patterns]

## Dev Agent Record

### Agent Model Used

Claude Opus 4.6

### Debug Log References

- IndexTest: Survey::create needed `created_by` (non-nullable FK) — fixed
- IndexTest: Ordering test needed `$this->travel(1)->seconds()` to ensure distinct `created_at` timestamps

### Completion Notes List

- Task 1: Added `scopeOwnedBy(Builder $query, int $userId): Builder` to CustomerRegistration model with Builder import
- Task 2: Created RegistrationListResource — 9 compact fields (id, no_surat, nama_lengkap, program_id, no_ktp masked, source, tanggal, has_survey, created_at)
- Task 3: Updated RegistrationResource — added `has_survey` boolean and `survey` nested object via `whenLoaded()` for N+1 prevention
- Task 4: Added `index()` (ownedBy + latest + paginate) and `show()` (ownedBy + with + findOrFail) to RegistrationController
- Task 5: Uncommented GET /registrations and GET /registrations/{registration} routes
- Task 6: Created IndexTest with 9 tests (50 assertions) — paginated list, ownership scoping, empty state, pagination params, NIK masking, has_survey, ordering, 401, 403
- Task 7: Created ShowTest with 8 tests (33 assertions) — detail view, IDOR 404, non-existent 404, NIK masking, survey data, no survey null, 401, 403

### File List

- `app/Models/CustomerRegistration.php` — MODIFIED (added Builder import, scopeOwnedBy method)
- `app/Http/Controllers/Api/V1/RegistrationController.php` — MODIFIED (added index + show methods, new imports)
- `app/Http/Resources/Api/V1/RegistrationResource.php` — MODIFIED (added has_survey + survey nested object)
- `app/Http/Resources/Api/V1/RegistrationListResource.php` — NEW
- `routes/api_v1.php` — MODIFIED (uncommented GET registration routes)
- `tests/Feature/Api/V1/Registration/IndexTest.php` — NEW
- `tests/Feature/Api/V1/Registration/ShowTest.php` — NEW
