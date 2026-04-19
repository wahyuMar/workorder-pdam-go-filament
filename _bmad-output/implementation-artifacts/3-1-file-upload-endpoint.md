# Story 3.1: File Upload Endpoint

Status: done

## Story

As a customer,
I want to upload a document file (photo or PDF) and receive a reference identifier,
So that I can attach it to my registration or complaint submission.

## Acceptance Criteria

1. **Given** an authenticated customer with a valid file (JPG, PNG, or PDF, ≤2MB)
   **When** `POST /api/v1/uploads` with multipart form data
   **Then** 201 response with `UploadResource` containing filename, original name, size, and MIME type

2. **Given** a file larger than 2MB
   **When** `POST /api/v1/uploads`
   **Then** 422 validation error with message indicating file is too large

3. **Given** a file with invalid MIME type (e.g., `.exe`, `.txt`, or a renamed extension)
   **When** `POST /api/v1/uploads`
   **Then** 422 validation error indicating invalid file type
   **And** validation uses server-side MIME detection, not client-declared content-type

4. **Given** no file attached in the request
   **When** `POST /api/v1/uploads`
   **Then** 422 validation error indicating file is required

5. **Given** a valid file upload
   **When** the upload completes successfully
   **Then** file is stored on `public` disk
   **And** `Upload` model record is created with `user_id`, `original_name`, `stored_path`, `mime_type`, `size`
   **And** no partial file is persisted on failure (atomic)

6. **Given** an unauthenticated request
   **When** `POST /api/v1/uploads`
   **Then** 401 Unauthenticated

7. **Given** an authenticated user without `customer` role
   **When** `POST /api/v1/uploads`
   **Then** 403 Forbidden

## Tasks / Subtasks

- [x] Task 1: Create `Upload` model (AC: #5)
  - [x] Create `app/Models/Upload.php` with `$fillable`, `casts()`, `user()` BelongsTo relationship
  - [x] Fields: `user_id`, `original_name`, `stored_path`, `mime_type`, `size`
  - [x] Migration already exists: `2026_04_11_175325_create_uploads_table.php` — do NOT create a new one

- [x] Task 2: Create `StoreRequest` FormRequest (AC: #1, #2, #3, #4)
  - [x] Create `app/Http/Requests/Api/V1/Upload/StoreRequest.php`
  - [x] `authorize()` returns `true` (auth handled by middleware)
  - [x] Validation rules for `file` field:
    - `required` — ensures file is present (AC #4)
    - `file` — ensures it's an uploaded file
    - `mimes:jpg,jpeg,png,pdf` — allowed extensions
    - `mimetypes:image/jpeg,image/png,application/pdf` — server-side MIME detection (AC #3)
    - `max:2048` — max 2MB in KB (AC #2)

- [x] Task 3: Create `UploadResource` API Resource (AC: #1)
  - [x] Create `app/Http/Resources/Api/V1/UploadResource.php`
  - [x] Fields: `filename` (basename of stored_path), `original_name`, `size`, `mime_type`
  - [x] Do NOT expose `user_id`, `id`, or full `stored_path`

- [x] Task 4: Create `UploadController` with `store()` method (AC: #1, #5)
  - [x] Create `app/Http/Controllers/Api/V1/UploadController.php`
  - [x] `store(StoreRequest $request)` method:
    1. Get validated file from `$request->file('file')`
    2. Store to `public` disk in `uploads/` directory using `$file->store('uploads', 'public')`
    3. Create `Upload` model record with all fields
    4. Return `UploadResource` with 201 status
  - [x] Wrap store + DB create in transaction for atomicity (AC #5)
  - [x] On storage failure, catch exception → clean up any stored file → return 500

- [x] Task 5: Uncomment and wire up route (AC: #1, #6, #7)
  - [x] Uncomment line 70 in `routes/api_v1.php`
  - [x] Wire to `[UploadController::class, 'store']`
  - [x] Add `use App\Http\Controllers\Api\V1\UploadController;` import
  - [x] Route is OUTSIDE `throttle:api` group but INSIDE `auth:sanctum` + `customer` — uses own `throttle:upload` (20/min)

- [x] Task 6: Create `StoreTest` feature tests (AC: #1–#7)
  - [x] Create `tests/Feature/Api/V1/Upload/StoreTest.php`
  - [x] Use `RefreshDatabaseCompat` trait, `Illuminate\Http\UploadedFile` for fake files, `Storage::fake('public')`
  - [x] Test cases:
    - Happy path: valid JPG → 201, DB record created, file exists on public disk
    - Happy path: valid PNG → 201
    - Happy path: valid PDF → 201
    - Response format: UploadResource structure has correct keys
    - DB record: correct `user_id`, `original_name`, `stored_path`, `mime_type`, `size`
    - File too large (>2MB) → 422
    - Invalid MIME type → 422
    - Missing file field → 422
    - Unauthenticated → 401
    - Non-customer role → 403

### Review Findings

- [x] [Review][Patch] F1: Filename >255 chars causes unhandled QueryException 500 — `getClientOriginalName()` can exceed DB column length (255 VARCHAR). Truncate before storing. [UploadController.php:29]
- [x] [Review][Patch] F2: Unsanitized original_name — control chars, null bytes, XSS payload stored as-is from multipart header. Strip control chars and limit length. [UploadController.php:29]
- [x] [Review][Defer] F3: Orphan files on user cascade delete — FK cascadeOnDelete removes DB rows but physical files remain on disk. PRD defers cleanup to Growth phase. — deferred, architecture-level

## Dev Notes

### Architecture Compliance

- **Route placement**: Upload route is OUTSIDE the `throttle:api` sub-group (line 33-67) but INSIDE the `auth:sanctum` + `customer` group (line 31). It has its own `throttle:upload` (20/min per user) middleware. See `routes/api_v1.php` line 69-70.
- **Rate limiter**: Already defined in `bootstrap/app.php` lines 44-46 as `upload` → `Limit::perMinute(20)->by($request->user()?->id ?: $request->ip())`.
- **Storage disk**: MUST use `public` disk. Filament admin already uses `public` disk with `->disk('public')` for all file uploads (ktp, kk, tagihan-listrik, foto-rumah, complaints).
- **Two-step upload pattern**: Files are uploaded first via this endpoint, then referenced by filename in registration/complaint form submissions. This is a core architecture decision for unstable mobile connections.
- **No new migration needed**: The `uploads` table was created in Story 1.1 infrastructure setup. Migration: `2026_04_11_175325_create_uploads_table.php`.
- **FormRequest required**: Unlike Story 2.3 (no FormRequest for route-param-only endpoints), this story NEEDS a FormRequest for file validation.
- **Controller pattern**: Single-action controller with `store()` method. Use typed `StoreRequest` for validation. Return `UploadResource->response()->setStatusCode(201)` for 201 status.

### Upload Storage Directory

Store files in `uploads/` subdirectory on public disk → physical path: `storage/app/public/uploads/`. Laravel's `$file->store('uploads', 'public')` generates a unique filename automatically.

### Server-Side MIME Validation (Critical — NFR11)

Laravel's `mimetypes` rule uses PHP's `finfo_file()` which reads the file's magic bytes, NOT the client-declared Content-Type header. This satisfies NFR11 (server-side MIME detection). Combined with `mimes` rule for extension check, this provides dual validation.

### Atomicity Pattern (NFR19)

Wrap the file store + DB insert in a try/catch:
```
1. Store file to disk
2. Create Upload DB record
→ If step 2 fails: delete the stored file, re-throw
→ If step 1 fails: no file saved, no DB record
```
Alternatively, use `DB::transaction()` for the DB part, with manual file cleanup in catch block.

### Previous Story Learnings (from Story 2.3)

1. **Use canonical DB values**: After storing a record, return data from the model (not raw request input). Story 2.3 review caught using raw `$no` param instead of `$customerNumber->no_sambungan`.
2. **Test patterns**: Use `RefreshDatabaseCompat` (not `RefreshDatabase`), `Role::findOrCreate('customer', 'web')` in setUp(), private `createCustomerUser()` helper per test class.
3. **Auth test pair**: Always include 401 (unauthenticated) + 403 (non-customer role) tests.
4. **Indonesian error messages**: Match existing patterns for user-facing messages.
5. **4 pre-existing test failures**: `CustomerRegistrationProcessTest`, `RepairReportTest`, `TeraMeterReportTest` — ignore these.

### Testing with Storage::fake

Use `Storage::fake('public')` to intercept file storage in tests. This replaces the real `public` disk with an in-memory filesystem. Assert with:
- `Storage::disk('public')->assertExists('uploads/...')` — file was stored
- `Storage::disk('public')->assertMissing(...)` — file was NOT stored (failure cases)
- `UploadedFile::fake()->image('photo.jpg')` — fake JPG image
- `UploadedFile::fake()->image('photo.png')` — fake PNG image
- `UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')` — fake PDF
- `UploadedFile::fake()->image('photo.jpg')->size(3000)` — oversized file (3MB)
- `UploadedFile::fake()->create('malware.exe', 100, 'application/x-msdownload')` — invalid type

### File Structure

```
app/
├── Http/
│   ├── Controllers/Api/V1/UploadController.php    ← NEW
│   ├── Requests/Api/V1/Upload/StoreRequest.php    ← NEW
│   └── Resources/Api/V1/UploadResource.php        ← NEW
├── Models/Upload.php                               ← NEW
routes/api_v1.php                                   ← MODIFY (uncomment line 70)
tests/Feature/Api/V1/Upload/StoreTest.php          ← NEW
```

### References

- [Source: _bmad-output/planning-artifacts/epics.md — Epic 3, Story 3.1 lines 395-420]
- [Source: _bmad-output/planning-artifacts/architecture.md — File Upload section, API patterns]
- [Source: _bmad-output/planning-artifacts/prd.md — FR35-FR37, NFR3, NFR11, NFR19]
- [Source: database/migrations/2026_04_11_175325_create_uploads_table.php — schema]
- [Source: routes/api_v1.php — line 69-70 upload route placeholder]
- [Source: bootstrap/app.php — lines 44-46 upload rate limiter]
- [Source: config/filesystems.php — lines 41-48 public disk config]

## Dev Agent Record

### Agent Model Used

Claude Opus 4.6 (claude-opus-4.6)

### Debug Log References

- Controller base class fix: needed `use App\Http\Controllers\Controller` import (not namespace-local)

### Completion Notes List

- All 6 tasks implemented in single pass
- Upload model with $fillable, casts, BelongsTo user relationship
- StoreRequest with dual validation: mimes (extension) + mimetypes (server-side magic bytes) for NFR11
- UploadResource exposes only filename/original_name/size/mime_type (no id/user_id/stored_path)
- UploadController.store() with atomicity: file stored first, DB insert in try/catch, cleanup on failure
- Route wired outside throttle:api, uses own throttle:upload (20/min)
- 10 feature tests covering all 7 ACs (44 assertions)
- 114 total passing, 4 pre-existing failures unchanged, Pint clean

### File List

- app/Models/Upload.php (NEW)
- app/Http/Controllers/Api/V1/UploadController.php (NEW)
- app/Http/Requests/Api/V1/Upload/StoreRequest.php (NEW)
- app/Http/Resources/Api/V1/UploadResource.php (NEW)
- routes/api_v1.php (MODIFIED — uncommented upload route, added import)
- tests/Feature/Api/V1/Upload/StoreTest.php (NEW)
