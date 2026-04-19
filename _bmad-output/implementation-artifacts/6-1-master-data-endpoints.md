# Story 6.1: Master Data Endpoints

Status: done

## Story

As a customer,
I want to retrieve reference data like programs, regions, and complaint types,
So that I can populate form dropdowns in the mobile app.

## Acceptance Criteria

1. **List active programs** — Given an authenticated customer, When GET `/api/v1/master/programs`, Then 200 response with list of active programs (`is_active = true`) via `ProgramResource`.
2. **List selectable provinces** — Given an authenticated customer, When GET `/api/v1/master/provinces`, Then 200 response with provinces filtered to `is_selectable = true` via `RegionResource`.
3. **List regencies by province** — Given a selected province ID, When GET `/api/v1/master/regencies/{province}`, Then 200 response with regencies for that province (`is_selectable = true`) via `RegionResource`.
4. **List districts by regency** — Given a selected regency ID, When GET `/api/v1/master/districts/{regency}`, Then 200 response with districts for that regency via `RegionResource`.
5. **List villages by district** — Given a selected district ID, When GET `/api/v1/master/villages/{district}`, Then 200 response with villages for that district via `RegionResource`.
6. **List active complaint types** — Given an authenticated customer, When GET `/api/v1/master/complaint-types`, Then 200 response with active complaint types (`is_active = true`) via `ComplaintTypeResource`.
7. **Caching** — All 6 master data responses are cached for 24 hours via `Cache::remember()`. Cascading endpoints use parent ID in cache key.
8. **Auth guard** — All endpoints require `auth:sanctum` + `customer` middleware. 401 for unauthenticated, 403 for non-customer.

## Tasks / Subtasks

- [x] Task 1: Create `MasterData/ProgramResource` (AC: #1)
  - [x] Create `app/Http/Resources/Api/V1/MasterData/ProgramResource.php`
  - [x] Fields: `id`, `name`
  - [x] Follow `JsonResource` pattern from existing resources

- [x] Task 2: Create `MasterData/RegionResource` (AC: #2, #3, #4, #5)
  - [x] Create `app/Http/Resources/Api/V1/MasterData/RegionResource.php`
  - [x] Fields: `id`, `name`
  - [x] Shared for province, regency, district, village (architecture decision MD-02..05)

- [x] Task 3: Create `MasterData/ComplaintTypeResource` (AC: #6)
  - [x] Create `app/Http/Resources/Api/V1/MasterData/ComplaintTypeResource.php`
  - [x] Fields: `id`, `name`

- [x] Task 4: Create `MasterDataController` with 6 methods (AC: #1–#7)
  - [x] Create `app/Http/Controllers/Api/V1/MasterDataController.php`
  - [x] `programs()`: `Program::where('is_active', true)->orderBy('name')->get()` → `ProgramResource::collection()`, cached `master.programs`
  - [x] `provinces()`: `Province::where('is_selectable', true)->orderBy('name')->get()` → `RegionResource::collection()`, cached `master.provinces`
  - [x] `regencies(int $province)`: `Regency::where('province_id', $province)->where('is_selectable', true)->orderBy('name')->get()` → `RegionResource::collection()`, cached `master.regencies.{province}`
  - [x] `districts(int $regency)`: `District::where('regency_id', $regency)->orderBy('name')->get()` → `RegionResource::collection()`, cached `master.districts.{regency}`
  - [x] `villages(int $district)`: `Village::where('district_id', $district)->orderBy('name')->get()` → `RegionResource::collection()`, cached `master.villages.{district}`
  - [x] `complaintTypes()`: `ComplaintType::where('is_active', true)->orderBy('name')->get()` → `ComplaintTypeResource::collection()`, cached `master.complaint-types`
  - [x] All caching: `Cache::remember($key, now()->addHours(24), fn () => ...)`
  - [x] Return type: `AnonymousResourceCollection` for all methods
  - [x] Use `int` param for route params (NOT route-model binding) — matches codebase pattern

- [x] Task 5: Uncomment and update 6 master data routes (AC: #1–#6, #8)
  - [x] In `routes/api_v1.php` lines ~46-51: replace commented routes with `MasterDataController` references
  - [x] Add `use App\Http\Controllers\Api\V1\MasterDataController;` import at top
  - [x] Route names preserved: `api.v1.master.programs`, `api.v1.master.provinces`, `api.v1.master.regencies`, `api.v1.master.districts`, `api.v1.master.villages`, `api.v1.master.complaint-types`
  - [x] Routes stay inside `auth:sanctum` + `customer` + `throttle:api` middleware group

- [x] Task 6: Create `tests/Feature/Api/V1/MasterData/ListTest.php` (AC: #1–#8)
  - [x] Test cases for Programs (4):
    1. `test_list_active_programs` — 200, returns only active, correct structure
    2. `test_excludes_inactive_programs` — inactive program not in response
    3. `test_programs_unauthenticated_returns_401`
    4. `test_programs_non_customer_returns_403`
  - [x] Test cases for Provinces (3):
    5. `test_list_selectable_provinces` — 200, returns only `is_selectable = true`
    6. `test_excludes_non_selectable_provinces`
    7. `test_provinces_unauthenticated_returns_401`
  - [x] Test cases for Regencies (4):
    8. `test_list_regencies_for_province` — 200, filtered by province, only selectable
    9. `test_regencies_excludes_other_provinces` — regency from province B not in response for province A
    10. `test_regencies_empty_for_nonexistent_province` — 200 with empty `data`
    11. `test_regencies_unauthenticated_returns_401`
  - [x] Test cases for Districts (3):
    12. `test_list_districts_for_regency` — 200, filtered by regency
    13. `test_districts_empty_for_nonexistent_regency`
    14. `test_districts_unauthenticated_returns_401`
  - [x] Test cases for Villages (3):
    15. `test_list_villages_for_district` — 200, filtered by district
    16. `test_villages_empty_for_nonexistent_district`
    17. `test_villages_unauthenticated_returns_401`
  - [x] Test cases for Complaint Types (3):
    18. `test_list_active_complaint_types` — 200, returns only active
    19. `test_excludes_inactive_complaint_types`
    20. `test_complaint_types_unauthenticated_returns_401`
  - [x] Test cases for Caching (1):
    21. `test_responses_are_cached` — verify Cache::has() after first request
  - [x] Total: 21 tests (76 assertions) — ALL PASSING

## Dev Notes

### Architecture Compliance
- **Controller**: Single `MasterDataController` per architecture (AR: MD-01..MD-06). No constructor injection needed (no services required).
- **Resources**: Under `app/Http/Resources/Api/V1/MasterData/` subdirectory per architecture. 3 resources: `ProgramResource`, `RegionResource` (shared for all 4 region levels), `ComplaintTypeResource`.
- **Route group**: Inside `auth:sanctum` + `customer` + `throttle:api` (120/min) — same group as all other authenticated API routes.
- **Caching**: `Cache::remember()` with 24h TTL per PRD NFR2 and architecture data decisions. Cache keys include parent ID for cascading endpoints.
- **No Form Requests**: All read-only endpoints — no validation needed per architecture mapping table.
- **No pagination**: Master data sets are small (dropdown data). Return full collections, NOT paginated.

### Model Flag Summary (CRITICAL)
| Model | Filter Column | In Model $casts | Notes |
|-------|---------------|-----------------|-------|
| Program | `is_active` | ❌ NOT in $casts | Use `where('is_active', true)` — works via DB boolean |
| Province | `is_selectable` | ✅ cast to boolean | Use `where('is_selectable', true)` |
| Regency | `is_selectable` | ✅ cast to boolean | Use `where('is_selectable', true)` |
| District | — | No flag | Return ALL for given regency |
| Village | — | No flag | Return ALL for given district |
| ComplaintType | `is_active` | ❌ NOT in $casts | Use `where('is_active', true)` — works via DB boolean |

### Existing Route Scaffold
Routes are already commented in `routes/api_v1.php` (lines ~46-51):
```php
// Route::get('/master/programs', ...)->name('api.v1.master.programs');
// Route::get('/master/provinces', ...)->name('api.v1.master.provinces');
// Route::get('/master/regencies/{province}', ...)->name('api.v1.master.regencies');
// Route::get('/master/districts/{regency}', ...)->name('api.v1.master.districts');
// Route::get('/master/villages/{district}', ...)->name('api.v1.master.villages');
// Route::get('/master/complaint-types', ...)->name('api.v1.master.complaint-types');
```
Replace the `...` with `[MasterDataController::class, 'methodName']`. Keep `/master/` prefix (matches existing scaffold). Add `MasterDataController` import.

### FK Hierarchy (Cascading)
```
Province (1) → (*) Regency (1) → (*) District (1) → (*) Village
```
- Parent existence is NOT validated — non-existent parent returns empty `data` array (200, not 404). Consistent with read-only reference data.

### Controller Pattern Reference
Follow `RegistrationController` / `ComplaintController` return type patterns:
- Collection endpoints → `AnonymousResourceCollection` (via `Resource::collection()`)
- Use `int` parameter for route params (e.g., `int $province`), NOT route-model binding

### Testing Patterns (from Stories 4.2, 5.1, 5.2)
- `RefreshDatabaseCompat` trait (NOT `RefreshDatabase`)
- `Role::findOrCreate('customer', 'web')` in setUp
- `createCustomerUser()` helper with `$overrides` param
- Auth pair: `test_*_unauthenticated_returns_401()` + `test_*_non_customer_returns_403()` (share 403 across multiple endpoint groups to reduce test count)
- Response structure: `assertJsonStructure(['data' => ['*' => ['id', 'name']]])`
- No ownership scoping needed — master data is shared across all customers
- 4 pre-existing test failures to IGNORE: `CustomerRegistrationProcessTest` (2), `RepairReportTest`, `TeraMeterReportTest`

### Caching Test Approach
- Use `Cache::shouldReceive()` Mockery approach OR assert `Cache::has($key)` after request
- Simpler: make request, then assert `Cache::has('master.programs')` returns true
- For cascading: assert `Cache::has("master.regencies.{$province->id}")` after request

### Previous Story Learnings
- **Per_page clamping**: Not applicable (no pagination for master data)
- **whenLoaded null guard**: Not applicable (no eager-loaded relationships with nullable FKs)
- **N+1 prevention**: Not needed — no relationships loaded in master data resources
- **DB defaults refresh**: Not needed — reading existing records, not creating
- **Route parameter style**: `int $param` NOT route-model binding — consistent pattern

### Response Format
All 6 endpoints return non-paginated collections:
```json
{
  "data": [
    { "id": 1, "name": "Program Name" },
    { "id": 2, "name": "Another Program" }
  ]
}
```
No `links` or `meta` keys (no pagination). Dates: ISO 8601 if present. Nulls: explicit.

### References

- [Source: _bmad-output/planning-artifacts/epics.md — Epic 6, Story 6.1 AC (lines 538-574)]
- [Source: _bmad-output/planning-artifacts/architecture.md — MasterDataController (lines 215-271), Data Architecture caching (lines 118-127), API patterns (lines 139-149)]
- [Source: _bmad-output/planning-artifacts/prd.md — FR29-FR34, NFR2 (cached 100ms), MVP master data scope]
- [Source: app/Models/Program.php — guarded=[], no is_active cast]
- [Source: app/Models/Province.php — is_selectable fillable+cast, hasMany Regency]
- [Source: app/Models/Regency.php — is_selectable fillable+cast, province_id FK, hasMany District]
- [Source: app/Models/District.php — regency_id FK, no is_selectable, hasMany Village]
- [Source: app/Models/Village.php — district_id FK, no is_selectable]
- [Source: app/Models/ComplaintType.php — guarded=[], no is_active cast]
- [Source: routes/api_v1.php — commented master data routes (lines 46-51)]
- [Source: _bmad-output/implementation-artifacts/5-2-list-view-track-complaints.md — controller patterns, review fixes]
- [Source: _bmad-output/implementation-artifacts/deferred-work.md — no items relevant to this story]

## Dev Agent Record

### Implementation Summary
All 6 tasks completed. 5 new files created, 1 existing file modified.

### Files Changed
**Created:**
- `app/Http/Resources/Api/V1/MasterData/ProgramResource.php` — id, name
- `app/Http/Resources/Api/V1/MasterData/RegionResource.php` — id, name (shared for province/regency/district/village)
- `app/Http/Resources/Api/V1/MasterData/ComplaintTypeResource.php` — id, name
- `app/Http/Controllers/Api/V1/MasterDataController.php` — 6 methods with Cache::remember() 24h
- `tests/Feature/Api/V1/MasterData/ListTest.php` — 21 tests, 76 assertions

**Modified:**
- `routes/api_v1.php` — Added MasterDataController import, replaced 6 commented routes

### Review Findings

- [x] [Review][Patch] No `whereNumber()` route constraint — non-numeric params coerced to 0 [routes/api_v1.php:49-51] ✅ FIXED
- [x] [Review][Defer] Unbounded cache key proliferation via arbitrary parent IDs [MasterDataController.php:40,52,60] — deferred, hardening for future sprint

### Test Results
- MasterData tests: 21 passed (76 assertions)
- Full suite: 202 passed, 4 pre-existing failures (unchanged)
- Pint: 377 files — PASS
