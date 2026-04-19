# Story 5.2: List, View & Track Complaints

Status: done

## Story

As a customer,
I want to view my complaints and track their resolution progress,
So that I can stay informed about my reported issues.

## Acceptance Criteria

1. **List own complaints** — Given an authenticated customer with complaints, When GET `/api/v1/complaints`, Then 200 paginated list of own complaints only via `ComplaintListResource`. Supports `?per_page` (default 15, max 100). Results ordered by latest first.
2. **View complaint detail** — Given an authenticated customer, When GET `/api/v1/complaints/{complaint}`, Then 200 full complaint detail including status via `ComplaintResource` with `complaintType` eager-loaded.
3. **View follow-up timeline** — Given an authenticated customer, When GET `/api/v1/complaints/{complaint}/timeline`, Then 200 response with follow-up timeline (progress history) for that complaint, ordered chronologically.
4. **IDOR protection** — Given a customer trying to access another customer's complaint, When any GET complaints endpoint, Then 404 Not Found (via `scopeOwnedBy` + `findOrFail` — consistent with `RegistrationController` pattern). NIK values masked in all responses.
5. **Empty states** — Given a customer with no complaints (index) or a complaint with no follow-ups (timeline), When queried, Then return empty `data` arrays (200, not error).

## Tasks / Subtasks

- [x] Task 1: Create `ComplaintListResource` (AC: #1, #4)
  - [x] Create `app/Http/Resources/Api/V1/ComplaintListResource.php`
  - [x] Compact fields: `id`, `no_pengaduan`, `complaint_type` (via `whenLoaded`), `no_sambungan`, `judul_pengaduan`, `status`, `priority`, `tanggal`, `created_at`
  - [x] Copy `maskNik()` private method (known duplication — deferred trait extraction) — NOT needed here since `no_ktp` is omitted from list resource
  - [x] Follow `RegistrationListResource` pattern exactly

- [x] Task 2: Create `ComplaintFollowUpResource` (AC: #3)
  - [x] Create `app/Http/Resources/Api/V1/ComplaintFollowUpResource.php`
  - [x] Fields: `id`, `work_order` (string value from WorkOrderEnum), `notes`, `photos`, `carbon_copies`, `follow_up_at` (ISO 8601), `created_at` (ISO 8601)
  - [x] Exclude `complaint_id`, `complaint_number` (redundant — parent complaint is known)

- [x] Task 3: Add `index()`, `show()`, `timeline()` to ComplaintController (AC: #1, #2, #3, #4)
  - [x] `index(Request $request): AnonymousResourceCollection` — pattern: `Complaint::ownedBy($request->user()->id)->with('complaintType')->latest()->paginate(min($request->integer('per_page', 15), 100))` → `ComplaintListResource::collection($paginator)`
  - [x] `show(Request $request, int $complaint): JsonResponse` — pattern: `Complaint::ownedBy($request->user()->id)->with('complaintType')->findOrFail($complaint)` → `(new ComplaintResource($record))->response()->setStatusCode(200)`
  - [x] `timeline(Request $request, int $complaint): JsonResponse` — find complaint via `ownedBy()->findOrFail()`, then load `$complaint->followUps()->oldest('follow_up_at')->get()` → return `ComplaintFollowUpResource::collection($followUps)->response()->setStatusCode(200)`
  - [x] Use `int $complaint` parameter (NOT route-model binding) — matches `RegistrationController::show()` pattern
  - [x] Import `Illuminate\Http\Request` (index/show/timeline don't need FormRequest)

- [x] Task 4: Uncomment 3 GET complaint routes (AC: #1, #2, #3)
  - [x] In `routes/api_v1.php`: uncomment the 3 GET routes (~lines 67-69)
  - [x] Verify route names: `api.v1.complaints.index`, `api.v1.complaints.show`, `api.v1.complaints.timeline`
  - [x] No new imports needed — `ComplaintController` already imported from Story 5.1

- [x] Task 5: Create `IndexTest` (AC: #1, #4, #5)
  - [x] Create `tests/Feature/Api/V1/Complaint/IndexTest.php`
  - [x] 9 test cases: paginated list, ownership isolation, empty state, pagination, per_page clamp, ordering, complaint_type, 401, 403

- [x] Task 6: Create `ShowTest` (AC: #2, #4)
  - [x] Create `tests/Feature/Api/V1/Complaint/ShowTest.php`
  - [x] 7 test cases: view detail, IDOR 404, nonexistent 404, NIK masking, complaint_type, 401, 403

- [x] Task 7: Create `TimelineTest` (AC: #3, #4, #5)
  - [x] Create `tests/Feature/Api/V1/Complaint/TimelineTest.php`
  - [x] 6 test cases: timeline data, chronological order, empty timeline, IDOR 404, 401, 403

## Dev Notes

### Architecture Compliance
- **Route group**: Inside `auth:sanctum` + `customer` + `throttle:api` (120/min) — same as all other API routes
- **Controller pattern**: Follow `RegistrationController::index()/show()` exactly — scope → paginate/findOrFail → Resource
- **IDOR protection**: `scopeOwnedBy()` on Complaint model (added in Story 5.1). `show()` and `timeline()` use scope + `findOrFail()` → 404 for non-owned records. This matches existing `RegistrationController` pattern (returns 404, NOT 403 as spec originally suggested via Policy).
- **Note on ComplaintPolicy**: Architecture spec (AR8) mentions creating `ComplaintPolicy` for 403 enforcement. Current codebase uses scope+findOrFail→404 pattern consistently. Follow existing pattern for consistency. Policy creation deferred.
- **Pagination**: `paginate(min($request->integer('per_page', 15), 100))` — clamped at 100 per review finding from Story 4.2
- **N+1 prevention**: `index()` uses `with('complaintType')` for eager loading. No `withExists()` needed here unlike registrations (no boolean existence check).

### Existing Code to Build On (from Story 5.1)
- `ComplaintController` already exists with `store()` — add `index()`, `show()`, `timeline()` to it
- `ComplaintResource` already exists with full detail fields + `maskNik()` — reuse for `show()` as-is
- `Complaint::scopeOwnedBy()` already exists — use for all 3 endpoints
- `Complaint::complaintType()` BelongsTo relationship exists
- `Complaint::followUps()` HasMany relationship exists (→ `ComplaintFollowUp`)
- `routes/api_v1.php` already has `ComplaintController` import and 3 commented GET routes

### ComplaintFollowUp Model Fields
- `complaint_id` (FK), `complaint_number` (string), `carbon_copies` (json→array), `work_order` (WorkOrderEnum), `notes` (text), `photos` (json→array), `follow_up_at` (datetime)
- `WorkOrderEnum` values: Ganti Meter, Tutup, Buka Kembali, Cabut, Ubah Nama, Ganti Alamat, Ganti Tarif, Perbaikan, Tera Meter
- Factory exists: `ComplaintFollowUpFactory` — use in timeline tests

### Response Format Reference
- **Paginated list** (index): `{ "data": [...], "links": {...}, "meta": {...} }` — standard Laravel paginator envelope
- **Single resource** (show): `{ "data": {...} }` — via `(new Resource($model))->response()->setStatusCode(200)`
- **Timeline collection** (timeline): `{ "data": [...] }` — array of follow-ups, NOT paginated (follow-ups are few per complaint)
- **Dates**: ISO 8601 format via `->toIso8601String()`
- **Nulls**: Explicit `null` in JSON (never omit the field)

### Testing Patterns (from Story 4.2 and 5.1)
- `RefreshDatabaseCompat` trait (NOT `RefreshDatabase`)
- `Role::findOrCreate('customer', 'web')` in setUp
- `createCustomerUser()` helper with `$overrides` param
- Auth pair: `test_unauthenticated_returns_401()` + `test_non_customer_user_returns_403()`
- Ownership isolation: create records for user A and B, verify A's index doesn't contain B's
- Ordering: use `$this->travel(1)->seconds()` for distinct timestamps
- Pagination structure: `assertJsonStructure(['data' => [...], 'links', 'meta'])`
- Use `Complaint::create()` directly in tests (boot() auto-generates `no_pengaduan`)
- Use `ComplaintFollowUp::create()` or factory for timeline test data
- 4 pre-existing test failures to IGNORE: `CustomerRegistrationProcessTest` (2), `RepairReportTest`, `TeraMeterReportTest`

### ComplaintListResource Fields (compact)
| Field | Type | Notes |
|-------|------|-------|
| `id` | int | |
| `no_pengaduan` | string | PGD-* format |
| `complaint_type` | object\|null | `whenLoaded('complaintType', fn() => ['id' => ..., 'name' => ...])` |
| `no_sambungan` | string | |
| `judul_pengaduan` | string | |
| `status` | string | pending, in_progress, resolved, closed |
| `priority` | string | low, medium, high |
| `tanggal` | string\|null | ISO 8601 |
| `created_at` | string\|null | ISO 8601 |

**Excluded from list** (present in detail): `nama`, `alamat`, `latitude`, `longitude`, `email`, `no_hp`, `no_ktp`, `sumber`, `isi_pengaduan`, `foto`

### Previous Story Learnings
- **DB defaults refresh**: `$complaint->refresh()` needed after create to load DB defaults (status, priority) — relevant for test setup
- **maskNik() duplication**: Now across 4 classes (RegistrationResource, RegistrationListResource, CustomerNumberController, ComplaintResource). ComplaintListResource does NOT need it since `no_ktp` is excluded from list view.
- **Per_page clamping**: Always use `min($request->integer('per_page', 15), 100)` — fixed in Story 4.2 review
- **N+1 in list**: Story 4.2 review caught `has_survey` N+1 on registration index — check for similar issues here
- **Path traversal regex**: `'not_regex:/\.\.|\\\\|\x00/'` on filename fields — not needed for read endpoints

### Project Structure Notes
- Controller: `app/Http/Controllers/Api/V1/ComplaintController.php` (modify — add 3 methods)
- List Resource: `app/Http/Resources/Api/V1/ComplaintListResource.php` (new file)
- FollowUp Resource: `app/Http/Resources/Api/V1/ComplaintFollowUpResource.php` (new file)
- Routes: `routes/api_v1.php` (modify — uncomment 3 routes)
- Tests: `tests/Feature/Api/V1/Complaint/IndexTest.php` (new)
- Tests: `tests/Feature/Api/V1/Complaint/ShowTest.php` (new)
- Tests: `tests/Feature/Api/V1/Complaint/TimelineTest.php` (new)

### References

- [Source: _bmad-output/planning-artifacts/epics.md — Epic 5, Story 5.2 lines 512-537]
- [Source: _bmad-output/planning-artifacts/architecture.md — API endpoints, pagination pattern, IDOR protection, ComplaintPolicy]
- [Source: _bmad-output/planning-artifacts/prd.md — FR23 (list), FR24 (detail), FR25 (timeline), FR38 (NIK masking), FR39 (IDOR)]
- [Source: app/Http/Controllers/Api/V1/RegistrationController.php — index()/show() pattern]
- [Source: app/Http/Resources/Api/V1/RegistrationListResource.php — compact list resource pattern]
- [Source: app/Models/Complaint.php — scopeOwnedBy(), complaintType(), followUps()]
- [Source: app/Models/ComplaintFollowUp.php — follow-up model fields and relationships]
- [Source: app/Http/Resources/Api/V1/ComplaintResource.php — existing detail resource from Story 5.1]
- [Source: routes/api_v1.php lines 70-72 — commented GET complaint routes]
- [Source: _bmad-output/implementation-artifacts/4-2-list-view-own-registrations.md — list/view pattern reference]
- [Source: _bmad-output/implementation-artifacts/5-1-submit-complaint.md — complaint controller/resource/model patterns]
- [Source: _bmad-output/implementation-artifacts/deferred-work.md — maskNik duplication, ComplaintPolicy deferred]

## Dev Agent Record

### File List (Created/Modified)

**Created:**
- `app/Http/Resources/Api/V1/ComplaintListResource.php` — compact list resource (9 fields, no maskNik)
- `app/Http/Resources/Api/V1/ComplaintFollowUpResource.php` — timeline follow-up resource (7 fields)
- `tests/Feature/Api/V1/Complaint/IndexTest.php` — 9 tests for complaint list endpoint
- `tests/Feature/Api/V1/Complaint/ShowTest.php` — 7 tests for complaint detail endpoint
- `tests/Feature/Api/V1/Complaint/TimelineTest.php` — 6 tests for complaint timeline endpoint

**Modified:**
- `app/Http/Controllers/Api/V1/ComplaintController.php` — added `index()`, `show()`, `timeline()` methods + imports
- `routes/api_v1.php` — uncommented 3 GET complaint routes (index, show, timeline)

### Test Results
- **22 new tests** (9 index + 7 show + 6 timeline), all passing
- **Total**: 181 passed, 4 pre-existing failures, 677 assertions
- **Pint**: Clean (372 files)

### Review Findings
- [x] [Review][Patch] F1: whenLoaded callback crashes when complaintType is null — add null guard in closure [ComplaintListResource.php:19]
- [x] [Review][Patch] F2: Negative per_page bypasses pagination — use max(1, min(..., 100)) [ComplaintController.php:29]
- [x] [Review][Defer] F3: whenLoaded null crash in ComplaintResource.php:18 — deferred, pre-existing (Story 5.1)

### Agent Model Used

{{agent_model_name_version}}

### Debug Log References

### Completion Notes List

### File List
