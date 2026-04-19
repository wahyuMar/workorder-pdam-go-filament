# Story 4.1: Submit SR Registration

Status: done

## Story

As a customer,
I want to submit a new SR registration with all required information and document references,
So that I can apply for a new water connection without visiting the PDAM office.

## Acceptance Criteria

1. **Given** an authenticated customer with valid registration data (personal data, addresses, house data, program, document references, coordinates)
   **When** `POST /api/v1/registrations`
   **Then** 201 response with `RegistrationResource` including auto-generated `SRPB-*` number

2. **Given** the submission
   **When** the record is created
   **Then** `source` is set to `'mobile'`, `user_id` is set to authenticated user
   **And** `no_surat` is generated via `CustomerRegistrationHelper::generateNoSurat()` inside a DB transaction
   **And** `tanggal` is set to `now()`

3. **Given** missing required fields or invalid data
   **When** `POST /api/v1/registrations`
   **Then** 422 with specific validation errors per field

4. **Given** document references (filenames from upload endpoint)
   **When** included in registration submission
   **Then** references are stored correctly as string paths in the registration record

5. **Given** the registration response
   **When** `no_ktp` field is present
   **Then** NIK is masked in the response (e.g., `3275****0003`) per FR38

6. **Given** an unauthenticated request
   **When** `POST /api/v1/registrations`
   **Then** 401 Unauthenticated

7. **Given** an authenticated user without `customer` role
   **When** `POST /api/v1/registrations`
   **Then** 403 Forbidden

## Tasks / Subtasks

- [x] Task 1: Update `CustomerRegistration` model (AC: #2)
  - [x] Add `user_id` and `source` to `$fillable` array (columns exist in DB but missing from model)
  - [x] Add `user()` BelongsTo relationship to `User` model
  - [x] Do NOT create new migration — columns already exist via `2026_04_11_175326` and `2026_04_11_175327`

- [x] Task 2: Create `Registration/StoreRequest` FormRequest (AC: #3)
  - [x] Create `app/Http/Requests/Api/V1/Registration/StoreRequest.php`
  - [x] `authorize()` returns `true` (auth handled by middleware)
  - [x] Validation rules:
    - **Required**: `nama_lengkap` (string, max:255) — only required field
    - **Personal**: `program_id` (nullable, exists:programs,id), `no_ktp` (nullable, string, max:255), `no_kk` (nullable, string, max:255), `pekerjaan` (nullable, string), `email` (nullable, email, max:255), `no_telp` (nullable, string, max:255), `no_hp` (nullable, string, max:255)
    - **KTP Address**: `alamat_ktp` (nullable, string), `dusun_kampung_ktp` (nullable, string, max:255), `rt_ktp` (nullable, integer, min:0), `rw_ktp` (nullable, integer, min:0), `province_id_ktp` (nullable, exists:provinces,id), `regency_id_ktp` (nullable, exists:regencies,id), `district_id_ktp` (nullable, exists:districts,id), `village_id_ktp` (nullable, exists:villages,id)
    - **Installation Address**: same pattern as KTP with `_pasang` suffix
    - **House/Utility**: `jumlah_penghuni_tetap` (nullable, integer, min:0), `jumlah_penghuni_tidak_tetap` (nullable, integer, min:0), `jumlah_kran_air_minum` (nullable, integer, min:0), `jenis_rumah` (nullable, in:Permanen,Semi Permanen,Non Permanen), `jumlah_kran` (nullable, integer, min:0), `daya_listrik` (nullable, integer, min:0)
    - **Upload references**: `upload_ktp`, `upload_kk`, `upload_tagihan_listrik`, `upload_foto_rumah` (all nullable, string, max:255)
    - **Coordinates**: `latitude` (nullable, string, max:255), `longitude` (nullable, string, max:255)

- [x] Task 3: Create `RegistrationResource` API Resource (AC: #1, #5)
  - [x] Create `app/Http/Resources/Api/V1/RegistrationResource.php`
  - [x] Include fields: `id`, `no_surat`, `nama_lengkap`, `program_id`, `no_ktp` (MASKED), `no_kk`, address fields, house data, upload references, coordinates, `source`, `tanggal`, `created_at`
  - [x] Mask `no_ktp` using same `maskNik()` pattern from `CustomerNumberController` (FR38)
  - [x] Do NOT expose `user_id`

- [x] Task 4: Create `RegistrationController` with `store()` method (AC: #1, #2, #4)
  - [x] Create `app/Http/Controllers/Api/V1/RegistrationController.php`
  - [x] `store(StoreRequest $request)` method:
    1. Get validated data from `$request->validated()`
    2. Wrap in `DB::transaction()` for atomicity + no_surat uniqueness (NFR18)
    3. Set auto-generated fields: `no_surat` via `CustomerRegistrationHelper::generateNoSurat()`, `tanggal` via `now()`, `user_id` from auth, `source` = `'mobile'`
    4. Create `CustomerRegistration` record
    5. Return `RegistrationResource` with 201 status
  - [x] Reuse `CustomerRegistrationHelper::generateNoSurat()` — same as Filament, no reimplementation

- [x] Task 5: Uncomment and wire up route (AC: #1, #6, #7)
  - [x] Uncomment `POST /registrations` route in `routes/api_v1.php` (line 59)
  - [x] Wire to `[RegistrationController::class, 'store']`
  - [x] Add `use App\Http\Controllers\Api\V1\RegistrationController;` import
  - [x] Route is inside `auth:sanctum` + `customer` + `throttle:api` group

- [x] Task 6: Create `StoreTest` feature tests (AC: #1–#7)
  - [x] Create `tests/Feature/Api/V1/Registration/StoreTest.php`
  - [x] Use `RefreshDatabaseCompat` trait
  - [x] Test cases:
    - Happy path: minimal valid data (nama_lengkap only) → 201, DB record with auto-generated fields
    - Happy path: full data with all optional fields → 201
    - Auto-generated fields: no_surat starts with SRPB-, tanggal is set, source='mobile', user_id matches
    - File references: upload fields stored correctly as strings
    - NIK masking: no_ktp masked in response but full value in DB
    - Missing nama_lengkap → 422
    - Invalid program_id (non-existent) → 422
    - Invalid jenis_rumah → 422
    - Invalid email format → 422
    - Unauthenticated → 401
    - Non-customer role → 403

### Review Findings

- [x] [Review][Patch] Upload fields accept path traversal payloads — add regex rejecting `..`, `\`, null bytes [StoreRequest.php:60-63]
- [x] [Review][Defer] `generateNoSurat()` race condition → duplicate `no_surat` under concurrency [CustomerRegistrationHelper.php:11-14] — deferred, pre-existing (shared with Filament)
- [x] [Review][Defer] Location FKs not hierarchically validated (province→regency→district→village) [StoreRequest.php:36-49] — deferred, pre-existing (DB schema doesn't enforce)

## Dev Notes

### Architecture Compliance

- **Route placement**: Inside `auth:sanctum` + `customer` + `throttle:api` (120/min) group at `routes/api_v1.php:59`. Standard rate limit unlike uploads.
- **Code reuse mandate**: `CustomerRegistrationHelper::generateNoSurat()` MUST be called identically to Filament. See `app/Filament/Resources/CustomerRegistrations/Pages/CreateCustomerRegistration.php:15-16` for the pattern: `$data['no_surat'] = CustomerRegistrationHelper::generateNoSurat(); $data['tanggal'] = now()->toDateString();`
- **Source field**: MUST hardcode `'mobile'` for API submissions (FR19). Filament defaults to `'manual'`.
- **Transaction safety (NFR18)**: Wrap the entire create operation (including `generateNoSurat()`) in `DB::transaction()` to prevent duplicate numbers under concurrent submissions.
- **IDOR**: Story 4.1 only creates records (no reading). `user_id` is set server-side, never from client input.

### Model Changes Required

The `CustomerRegistration` model at `app/Models/CustomerRegistration.php` is MISSING:
1. `user_id` in `$fillable` — column exists (migration `2026_04_11_175326`) but not in model
2. `source` in `$fillable` — column exists (migration `2026_04_11_175327`, default `'manual'`) but not in model
3. `user()` BelongsTo relationship — needed for API ownership queries

This was noted as deferred work from Story 1.1 code review. Story 4.1 MUST fix this.

### Upload File References

**Two-step pattern** (architecture):
```
1. Client uploads file via POST /api/v1/uploads → receives {filename, original_name, size, mime_type}
2. Client submits registration with upload_ktp/kk/etc. as string filename references
```

Upload fields (`upload_ktp`, `upload_kk`, `upload_tagihan_listrik`, `upload_foto_rumah`) store string paths on the public disk. For API uploads, the stored path is `uploads/{hash}.{ext}`. The client should send the `stored_path` value (e.g., `uploads/abc123.jpg`). Validate as `nullable|string|max:255`.

**Note**: Filament stores upload paths differently (`customer-registrations/ktp/...`). Both formats are valid public disk paths. Do NOT cross-validate against the `uploads` table — the file was already validated at upload time.

### NIK Masking (FR38)

`no_ktp` must be masked in ALL API responses. Reuse the `maskNik()` private method pattern from `CustomerNumberController`:
```php
private function maskNik(?string $nik): ?string
{
    if ($nik === null || mb_strlen(trim($nik)) === 0) return null;
    $nik = trim($nik);
    if (mb_strlen($nik) <= 4) return str_repeat('*', mb_strlen($nik));
    return mb_substr($nik, 0, 4) . str_repeat('*', mb_strlen($nik) - 8) . mb_substr($nik, -4);
}
```
Consider extracting to a shared helper or trait if used in 3+ places. For now, implement inline in `RegistrationResource`.

### Legacy Text Fields

The DB has legacy text columns: `kel_desa_ktp`, `kecamatan_ktp`, `kab_kota_ktp` (and `_pasang` equivalents). These exist alongside the FK ID columns. The Filament form does NOT populate these text fields. The API should also NOT accept them — use only FK ID columns (`province_id_ktp`, `regency_id_ktp`, `district_id_ktp`, `village_id_ktp`).

### Field Summary (42 client-submittable fields)

| Category | Fields | Count |
|----------|--------|-------|
| Personal | nama_lengkap, program_id, no_ktp, no_kk, pekerjaan, email, no_telp, no_hp | 8 |
| KTP Address | alamat_ktp, dusun_kampung_ktp, rt_ktp, rw_ktp, province_id_ktp, regency_id_ktp, district_id_ktp, village_id_ktp | 8 |
| Installation Address | alamat_pasang, dusun_kampung_pasang, rt_pasang, rw_pasang, province_id_pasang, regency_id_pasang, district_id_pasang, village_id_pasang | 8 |
| House/Utility | jumlah_penghuni_tetap, jumlah_penghuni_tidak_tetap, jumlah_kran_air_minum, jenis_rumah, jumlah_kran, daya_listrik | 6 |
| Uploads | upload_ktp, upload_kk, upload_tagihan_listrik, upload_foto_rumah | 4 |
| Coordinates | latitude, longitude | 2 |
| **Auto-set** | no_surat, tanggal, user_id, source | 4 |

### Previous Story Learnings

1. **Use canonical DB values**: Return data from model, not raw input (Story 2.3 review).
2. **Sanitize user input**: Strip control chars from filenames (Story 3.1 review).
3. **Test patterns**: `RefreshDatabaseCompat`, `Role::findOrCreate('customer', 'web')`, private `createCustomerUser()`, auth test pair (401+403).
4. **4 pre-existing failures**: Ignore `CustomerRegistrationProcessTest`, `RepairReportTest`, `TeraMeterReportTest`.
5. **Controller base class**: Must import `use App\Http\Controllers\Controller;` (Story 3.1 lesson).
6. **FormRequest**: `authorize()` always returns `true` — auth via middleware.

### File Structure

```
app/
├── Http/
│   ├── Controllers/Api/V1/RegistrationController.php  ← NEW
│   ├── Requests/Api/V1/Registration/StoreRequest.php  ← NEW
│   └── Resources/Api/V1/RegistrationResource.php      ← NEW
├── Models/CustomerRegistration.php                     ← MODIFY ($fillable + user())
routes/api_v1.php                                       ← MODIFY (uncomment line 59)
tests/Feature/Api/V1/Registration/StoreTest.php        ← NEW
```

### References

- [Source: _bmad-output/planning-artifacts/epics.md — Epic 4, Story 4.1 lines 433-450]
- [Source: _bmad-output/planning-artifacts/architecture.md — RegistrationController, StoreRequest, data flow]
- [Source: _bmad-output/planning-artifacts/prd.md — FR14-FR19, FR37-FR39, NFR18]
- [Source: app/Models/CustomerRegistration.php — model, $fillable, relationships]
- [Source: app/Helper/CustomerRegistrationHelper.php — generateNoSurat()]
- [Source: app/Filament/Resources/CustomerRegistrations/Pages/CreateCustomerRegistration.php — auto-field pattern]
- [Source: app/Filament/Resources/CustomerRegistrations/Schemas/CustomerRegistrationForm.php — field reference]
- [Source: app/Http/Controllers/Api/V1/CustomerNumberController.php — maskNik() pattern]
- [Source: routes/api_v1.php — line 59 route placeholder]

## Dev Agent Record

### Agent Model Used

Claude Opus 4.6

### Debug Log References

No issues encountered. All 6 tasks implemented in a single pass.

### Completion Notes List

- Task 1: Added `user_id`, `source` to CustomerRegistration `$fillable`; added `user()` BelongsTo with proper return types
- Task 2: Created StoreRequest with 42 field validations — `nama_lengkap` required, all others nullable with type constraints
- Task 3: Created RegistrationResource with NIK masking via `maskNik()` (same pattern as CustomerNumberController); `user_id` excluded
- Task 4: Created RegistrationController with `store()` wrapped in `DB::transaction()` for no_surat atomicity; reuses `CustomerRegistrationHelper::generateNoSurat()` identically to Filament
- Task 5: Uncommented POST /registrations route, added RegistrationController import
- Task 6: Created 11 feature tests covering: minimal/full happy paths, auto-generated fields, upload refs, NIK masking, 4 validation error cases, 401, 403

### File List

- `app/Models/CustomerRegistration.php` — MODIFIED (added user_id/source to $fillable, user() BelongsTo, return type hints)
- `app/Http/Controllers/Api/V1/RegistrationController.php` — NEW
- `app/Http/Requests/Api/V1/Registration/StoreRequest.php` — NEW
- `app/Http/Resources/Api/V1/RegistrationResource.php` — NEW
- `routes/api_v1.php` — MODIFIED (uncommented POST /registrations, added import)
- `tests/Feature/Api/V1/Registration/StoreTest.php` — NEW
