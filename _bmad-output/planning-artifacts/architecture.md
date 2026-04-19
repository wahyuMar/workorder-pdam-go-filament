---
stepsCompleted: ['step-01-init', 'step-02-context', 'step-03-starter', 'step-04-decisions', 'step-05-patterns', 'step-06-structure', 'step-07-validation', 'step-08-complete']
inputDocuments: ['_bmad-output/planning-artifacts/prd.md', '_bmad-output/brainstorming/brainstorming-session-2026-04-11-1517.md']
workflowType: 'architecture'
project_name: 'workorder-pdam-go-filament'
user_name: 'wong'
date: '2026-04-11'
lastStep: 8
status: 'complete'
completedAt: '2026-04-11'
---

# Architecture Decision Document

_This document builds collaboratively through step-by-step discovery. Sections are appended as we work through each architectural decision together._

## Project Context Analysis

### Requirements Overview

**Functional Requirements:**
45 FRs organized into 8 capability areas. Core capabilities: customer self-service account management, 2-step billing verification for nomor sambungan, SR registration submission (mirroring all Filament form fields), complaint submission restricted to verified nomor, and admin visibility with zero workflow changes. All customer-facing operations are scoped by authenticated user ID.

**Non-Functional Requirements:**
25 NFRs driving architecture: API response <500ms, billing API timeout configurable (default 10s), HttpOnly/Secure/SameSite cookies, NIK never in logs, IDOR prevention via scoped queries, rate limiting (120 auth/100 guest/min), CSRF enforcement, atomic file uploads, transaction-safe auto-numbering, 99% uptime target, PSR-12 compliance, and Swagger/OpenAPI documentation.

**Scale & Complexity:**

- Primary domain: REST API Backend (27 endpoints, 7 domains)
- Complexity level: Medium (external billing API + dual-role auth + data ownership scoping)
- Estimated architectural components: 12 major component groups
- Concurrent users target: 50

### Technical Constraints & Dependencies

- **Existing stack:** Laravel 12, Filament 4, Livewire 3, SQLite, PHP 8.3+
- **Auth coexistence:** Sanctum SPA auth (customer) alongside Filament session auth (admin), both on `web` guard
- **External dependency:** Billing API (HMAC-SHA256 auth) for customer number verification — timeout/error handling critical
- **Data model:** Nullable `user_id` on existing tables for backward compatibility; new `customer_numbers` pivot table
- **File storage:** Public disk, separate upload endpoint pattern, 2MB max, JPG/PNG/PDF
- **Code reuse mandate:** `CustomerRegistrationHelper`, `CustomerLookupService`, `Complaint::generateNoPengaduan()` must be called identically to Filament
- **Migration rules:** Never edit existing migrations; all schema changes via new migration files
- **Zero admin disruption:** No Filament code, model, or workflow modifications allowed

### Cross-Cutting Concerns Identified

1. **Authentication & Authorization** — Every endpoint requires auth guard check + customer role verification + resource ownership validation
2. **NIK Data Protection** — Masking logic needed in every API Resource that touches customer/billing data
3. **Source Tracking** — `source: mobile` must be set on all customer-created records (registrations, complaints)
4. **Rate Limiting** — Differentiated limits across auth/guest/upload endpoints
5. **Error Response Standardization** — Consistent Laravel format across all 27 endpoints including billing API failures (503)
6. **Auto-Numbering Integrity** — SRPB-*/PGD-* generation must use existing helpers with DB transactions, no conflicts between channels

## Starter Template Evaluation

### Assessment: Brownfield Extension (No Starter Needed)

This project is a **brownfield extension** of an existing production Laravel 12 application. No starter template or boilerplate evaluation is required — the technology stack, project structure, and coding conventions are already established.

### Existing Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| Language | PHP | 8.4.1 |
| Framework | Laravel | 12.x (Streamlined Structure) |
| Admin Panel | Filament | 4.x |
| Reactivity | Livewire | 3.x |
| Styling | Tailwind CSS | 4.x |
| Database | SQLite | — |
| Build Tool | Vite | — |
| Code Standards | PSR-12, Laravel Pint | — |
| Testing | PHPUnit | `php artisan test` |

### New Packages Required

| Package | Purpose | Notes |
|---------|---------|-------|
| `laravel/sanctum` | SPA cookie-based auth for customer API | Not yet installed |
| `spatie/laravel-permission` | Role-based access control (customer role) | Not yet installed |
| `darkaonline/l5-swagger` | OpenAPI/Swagger API documentation | Not yet installed |

### Established Architectural Patterns (Inherited)

- **Code Organization:** Laravel 12 Streamlined Structure — middleware in `bootstrap/app.php`, providers in `bootstrap/providers.php`
- **Filament Patterns:** Resources use `Schemas/Components/`, `Tables/Columns/`, `Tables/Filters/`, `Actions/` organization
- **Business Logic:** Action classes and Helpers (not inline in Resources)
- **Database:** Eloquent ORM exclusively, no raw `DB::` queries. Relations via standard methods
- **Naming:** `snake_case` (DB columns), `PascalCase` (classes), `camelCase` (methods)
- **File Storage:** Public disk for uploads
- **Migrations:** New files only, never edit existing

## Core Architectural Decisions

### Decision Priority Analysis

**Critical Decisions (Block Implementation):**
1. Sanctum SPA cookie-based auth with spatie/laravel-permission for customer role
2. Laravel API Resources for JSON transformation with NIK masking
3. Form Request classes for all endpoint validation
4. Layered middleware: `auth:sanctum` → `role:customer` → per-route
5. Query scopes + Policy classes for IDOR prevention
6. Separate `routes/api_v1.php` route file
7. Resource controllers per domain (~7 controllers)

**Important Decisions (Shape Architecture):**
1. Dedicated API log channel for debugging
2. Feature + Unit test strategy
3. Scribe for API documentation generation
4. Try-catch in Service layer for billing API errors
5. `.env` variables for Sanctum/session configuration

**Deferred Decisions (Post-MVP):**
1. Monitoring tools (Telescope, Pulse)
2. Circuit breaker for billing API
3. Push notifications infrastructure
4. Email verification & OTP

### Data Architecture

| Decision | Choice | Rationale |
|----------|--------|-----------|
| API Response Layer | Laravel API Resources (`JsonResource`) | NIK masking logic has clear home, supports list vs detail variants, built-in Laravel |
| Validation | Form Request classes (one per endpoint) | Consistent with Filament schema pattern, reusable, clean controllers |
| Database | SQLite (existing) | Already established, no change needed |
| ORM | Eloquent exclusively | Project rule — no raw `DB::` queries |
| Caching | Laravel Cache (24h for master data) | PRD-defined: programs, regions, complaint types |

### Authentication & Security

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Auth Method | Sanctum SPA (cookie-based, HttpOnly) | Same-domain PWA, more secure than localStorage tokens |
| Role Management | spatie/laravel-permission | Industry standard, `customer` role, `canAccessPanel()` blocks Filament |
| Middleware Stack | Layered: `auth:sanctum` → `role:customer` → route-specific | Granular, reusable, follows Laravel convention |
| IDOR Prevention | Query Scopes (list filtering) + Policy classes (single-record auth) | Defense in depth — scopes for queries, policies for actions |
| CSRF | Standard Sanctum `/sanctum/csrf-cookie` | Built-in, PWA calls before login. Configure `SANCTUM_STATEFUL_DOMAINS` |
| Session Lifetime | 43200 minutes (30 days) via `SESSION_LIFETIME` | PRD requirement for mobile convenience |

### API & Communication Patterns

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Controller Organization | Resource controllers per domain (~7) | `AuthController`, `CustomerNumberController`, `RegistrationController`, `ComplaintController`, `MasterDataController`, `UploadController`, `ProfileController` |
| Route File | Separate `routes/api_v1.php` | Clean separation, no interference with existing routes, easy v2 path |
| API Versioning | URL prefix `/api/v1/` | PRD-defined, loaded in `bootstrap/app.php` |
| Error Format | Laravel standard `{message, errors}` for 422, `{message}` for general | PRD-defined, consistent with framework convention |
| Billing API Errors | Try-catch in `CustomerLookupService`, return 503 on failure | Reuse existing service, add proper exception types |
| API Documentation | Scribe (knuckleswtf/scribe) | Laravel-native, auto-detect Form Request rules, less boilerplate than l5-swagger |
| Rate Limiting | Laravel built-in `RateLimiter` | 120/min auth, 100/min guest, 5/min login per IP (PRD-defined) |

### Frontend Architecture

Not applicable — this is an API-only backend. PWA frontend is a separate project outside this scope.

### Infrastructure & Deployment

| Decision | Choice | Rationale |
|----------|--------|-----------|
| Logging | Dedicated `api` log channel in `config/logging.php` | Separate from Filament/admin logs for easier API debugging |
| Testing | Feature tests (endpoint behavior) + Unit tests (services, policies, masking) | Feature tests in `Tests\Feature\Api\V1\*`, Unit for business logic |
| Environment Config | `.env` variables + existing config files | `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN`, standard Laravel approach |
| Monitoring | Deferred to Post-MVP | Current logging sufficient, Telescope/Pulse added later |
| Code Standards | PSR-12, Laravel Pint (existing) | No change from current project standards |

### Decision Impact Analysis

**Implementation Sequence:**
1. Install packages: `laravel/sanctum`, `spatie/laravel-permission`, `knuckleswtf/scribe`
2. Create migrations (6 new files)
3. Configure Sanctum + Permission + middleware in `bootstrap/app.php`
4. Create `routes/api_v1.php` with route groups
5. Implement Auth controllers + Form Requests
6. Implement domain controllers with API Resources
7. Add Query Scopes + Policies
8. Configure logging channel
9. Write tests

**Cross-Component Dependencies:**
- Sanctum config must be complete before any auth endpoint works
- spatie/permission setup required before middleware can check roles
- API Resources depend on model relations being correct (existing)
- Form Requests depend on model `$fillable` being updated (new fields)
- Scribe depends on Form Requests + controllers being written first

## Implementation Patterns & Consistency Rules

### Pattern Categories Defined

**12 potential conflict areas** identified where AI agents could make different choices. All resolved below.

### Naming Patterns

**Database Naming:**
- Tables: `snake_case` plural — `customer_numbers`, `budget_items`, `complaints`
- Columns: `snake_case` — `user_id`, `no_sambungan`, `verified_at`
- Foreign keys: `{singular_table}_id` — `user_id`, `survey_id`, `customer_registration_id`
- Indexes: Laravel default — `{table}_{column}_index`

**API Naming:**
- Endpoints: `kebab-case` plural — `/api/v1/customer-numbers`, `/api/v1/complaints`
- Route parameters: `{model}` — `/api/v1/complaints/{complaint}`
- Query parameters: `snake_case` — `?per_page=15&sort_by=created_at`
- Route names: `dot.notation` — `api.v1.complaints.store`, `api.v1.auth.login`

**Code Naming:**
- Controllers: `{Model}Controller` — `ComplaintController`, `AuthController`
- Form Requests: `{Action}{Model}Request` — `StoreComplaintRequest`, `UpdateProfileRequest`
- API Resources: `{Model}Resource` — `ComplaintResource`, `CustomerNumberResource`
- Policies: `{Model}Policy` — `RegistrationPolicy`, `ComplaintPolicy`
- Middleware: `PascalCase` — `EnsureCustomerRole`
- Test classes: `{Model}{Action}Test` — `ComplaintStoreTest`, `AuthLoginTest`
- Services: `{Domain}Service` — `CustomerLookupService` (existing)
- Helpers: `{Domain}Helper` — `CustomerRegistrationHelper` (existing)

### Structure Patterns

**New Files Location (API Layer Only):**
```
app/
+-- Http/
|   +-- Controllers/Api/V1/           # NEW: API controllers
|   |   +-- AuthController.php
|   |   +-- CustomerNumberController.php
|   |   +-- RegistrationController.php
|   |   +-- ComplaintController.php
|   |   +-- MasterDataController.php
|   |   +-- UploadController.php
|   |   +-- ProfileController.php
|   +-- Middleware/                     # NEW: Custom middleware
|   |   +-- EnsureCustomerRole.php
|   +-- Requests/Api/V1/              # NEW: Form Requests
|   |   +-- Auth/
|   |   |   +-- LoginRequest.php
|   |   |   +-- RegisterRequest.php
|   |   +-- StoreRegistrationRequest.php
|   |   +-- StoreComplaintRequest.php
|   |   +-- VerifyCustomerNumberRequest.php
|   |   +-- ConfirmCustomerNumberRequest.php
|   |   +-- UpdateProfileRequest.php
|   |   +-- UploadFileRequest.php
|   +-- Resources/Api/V1/             # NEW: API Resources
|       +-- CustomerNumberResource.php
|       +-- RegistrationResource.php
|       +-- RegistrationListResource.php
|       +-- ComplaintResource.php
|       +-- ComplaintListResource.php
|       +-- ProgramResource.php
|       +-- RegionResource.php
|       +-- ComplaintTypeResource.php
|       +-- UploadResource.php
|       +-- ProfileResource.php
+-- Models/                            # EXTEND: Add scopes, relations
+-- Policies/                          # NEW: Authorization
|   +-- RegistrationPolicy.php
|   +-- ComplaintPolicy.php
+-- Services/                          # REUSE + extend existing
routes/
+-- api_v1.php                         # NEW: All v1 API routes
tests/
+-- Feature/Api/V1/                    # NEW: API feature tests
|   +-- Auth/
|   +-- CustomerNumber/
|   +-- Registration/
|   +-- Complaint/
|   +-- MasterData/
|   +-- Upload/
+-- Unit/Api/                          # NEW: API unit tests
    +-- NikMaskingTest.php
    +-- OwnershipScopeTest.php
    +-- BillingServiceTest.php
```

### Format Patterns

**API Response - Success (single resource):**
```json
{ "data": { "id": 1, "no_surat": "SRPB-001", "created_at": "2026-04-11T10:30:00Z" } }
```

**API Response - Success (collection with pagination):**
```json
{ "data": [{"..."}, {"..."}], "links": { "first": "...", "last": "...", "prev": null, "next": "..." }, "meta": { "current_page": 1, "last_page": 5, "per_page": 15, "total": 72 } }
```

**API Response - Validation Error (422):**
```json
{ "message": "The given data was invalid.", "errors": { "nama": ["Nama wajib diisi."], "nik": ["NIK harus 16 digit."] } }
```

**API Response - General Error (4xx/5xx):**
```json
{ "message": "Layanan billing sedang tidak tersedia." }
```

**Data Format Rules:**
- JSON fields: `snake_case` - matches database columns
- Dates: ISO 8601 - `"2026-04-11T10:30:00Z"`
- Booleans: JSON native `true`/`false` (never `1`/`0`)
- Nulls: Explicit `null` in JSON (never omit the field)
- NIK: Always masked `"3275****0003"` - never full value in response or logs

### Process Patterns

**Controller Method Standard:**
```php
public function store(StoreRegistrationRequest $request): JsonResponse
{
    $data = $request->validated();
    $data['source'] = 'mobile';
    $data['user_id'] = auth()->id();
    // Business logic via Action/Helper/Service
    $record = RegistrationAction::execute($data);
    return new RegistrationResource($record);
}
```

**Error Handling Standard:**
```php
try {
    $result = $service->execute();
    return new ModelResource($result);
} catch (BillingApiException $e) {
    return response()->json(['message' => 'Layanan billing tidak tersedia.'], 503);
} catch (ModelNotFoundException $e) {
    return response()->json(['message' => 'Data tidak ditemukan.'], 404);
}
```

**Query Scope for Ownership:**
```php
// In Model
public function scopeOwnedBy(Builder $query, User $user): Builder
{
    return $query->where('user_id', $user->id);
}

// In Controller
$records = Model::ownedBy(auth()->user())->paginate();
```

### Enforcement Guidelines

**All AI Agents MUST:**
- Use Form Request for ALL validation (never inline)
- Use `$request->validated()` (never `$request->all()`)
- Set `source = 'mobile'` on all customer-created records
- Mask NIK in all API Resources
- Use Eloquent exclusively (no `DB::` raw queries)
- Place business logic in Action/Service/Helper (not controller)
- Use `config()` for environment variables (not `env()`)
- Follow existing naming conventions exactly

**Anti-Patterns (FORBIDDEN):**
- Raw `DB::` queries
- Full NIK in responses or logs
- Inline controller validation
- `$request->all()` instead of `$request->validated()`
- Business logic in controllers
- Editing existing migration files
- Hardcoded master data values
- `env()` direct access instead of `config()`

## Project Structure & Boundaries

### Complete API Layer Directory Structure

> Brownfield extension: only NEW and MODIFIED files shown. Existing project structure remains untouched.

```
app/
+-- Http/
|   +-- Controllers/Api/V1/
|   |   +-- AuthController.php              # FR: AM-01..AM-07
|   |   +-- CustomerNumberController.php    # FR: CNV-01..CNV-06
|   |   +-- RegistrationController.php      # FR: SRR-01..SRR-06
|   |   +-- ComplaintController.php         # FR: CM-01..CM-09
|   |   +-- MasterDataController.php        # FR: MD-01..MD-06
|   |   +-- UploadController.php            # FR: FM-01..FM-03
|   |   +-- ProfileController.php           # FR: AM-05..AM-07
|   +-- Middleware/
|   |   +-- EnsureCustomerRole.php          # Cross-cutting: Auth
|   +-- Requests/Api/V1/
|   |   +-- Auth/
|   |   |   +-- RegisterRequest.php         # AM-01
|   |   |   +-- LoginRequest.php            # AM-02
|   |   +-- CustomerNumber/
|   |   |   +-- VerifyRequest.php           # CNV-01
|   |   |   +-- ConfirmRequest.php          # CNV-02
|   |   +-- Registration/
|   |   |   +-- StoreRequest.php            # SRR-01
|   |   +-- Complaint/
|   |   |   +-- StoreRequest.php            # CM-01
|   |   +-- Upload/
|   |   |   +-- StoreRequest.php            # FM-01
|   |   +-- Profile/
|   |       +-- UpdateRequest.php           # AM-06
|   |       +-- ChangePasswordRequest.php   # AM-07
|   +-- Resources/Api/V1/
|       +-- Auth/
|       |   +-- UserResource.php            # AM-05
|       +-- CustomerNumberResource.php      # CNV-03..06
|       +-- Registration/
|       |   +-- RegistrationResource.php    # SRR-03
|       |   +-- RegistrationListResource.php # SRR-02
|       +-- Complaint/
|       |   +-- ComplaintResource.php       # CM-04
|       |   +-- ComplaintListResource.php   # CM-03
|       +-- MasterData/
|           +-- ProgramResource.php         # MD-01
|           +-- RegionResource.php          # MD-02..05
|           +-- ComplaintTypeResource.php   # MD-06
+-- Models/ (EXTEND existing)
|   +-- User.php                   # ADD: customer role, customerNumbers()
|   +-- CustomerNumber.php         # NEW: pivot model
|   +-- CustomerRegistration.php   # ADD: user(), scopeOwnedBy()
|   +-- Complaint.php              # ADD: user(), scopeOwnedBy()
|   +-- Upload.php                 # NEW model
+-- Policies/
|   +-- CustomerRegistrationPolicy.php
|   +-- ComplaintPolicy.php
+-- Exceptions/
|   +-- BillingApiException.php
database/migrations/
+-- xxxx_add_role_to_users_table.php
+-- xxxx_create_customer_numbers_table.php
+-- xxxx_add_user_id_to_customer_registrations_table.php
+-- xxxx_add_user_id_and_source_to_complaints_table.php
+-- xxxx_create_uploads_table.php
+-- xxxx_add_source_to_customer_registrations_table.php
routes/
+-- api_v1.php                     # NEW: 27 API endpoints
config/
+-- logging.php                    # MODIFY: Add 'api' channel
tests/
+-- Feature/Api/V1/
|   +-- Auth/
|   |   +-- RegisterTest.php
|   |   +-- LoginTest.php
|   |   +-- LogoutTest.php
|   +-- CustomerNumber/
|   |   +-- VerifyTest.php
|   |   +-- ConfirmTest.php
|   +-- Registration/
|   |   +-- StoreTest.php
|   |   +-- ListTest.php
|   |   +-- ShowTest.php
|   +-- Complaint/
|   |   +-- StoreTest.php
|   |   +-- ListTest.php
|   |   +-- ShowTest.php
|   +-- MasterData/
|       +-- ListTest.php
+-- Unit/Api/
    +-- NikMaskingTest.php
    +-- OwnershipScopeTest.php
    +-- BillingServiceTest.php
```

### Architectural Boundaries

**API Boundary:** All customer traffic enters via `routes/api_v1.php` into `Controllers/Api/V1/`. No Filament code is touched or called from this layer.

**Auth Boundary:** Middleware stack `auth:sanctum` then `EnsureCustomerRole` guarantees only authenticated customers reach API endpoints. Admin users are rejected from API; customers are rejected from Filament via `canAccessPanel()`.

**Data Ownership Boundary:** `scopeOwnedBy()` on models + Policy classes ensure customers can only access their own records. Defense in depth: scope filters queries, policy authorizes individual actions.

**External Integration Boundary:** `CustomerLookupService` is the single gateway to the billing API. All billing communication is encapsulated here. Timeout and error handling centralized via `BillingApiException`.

**File Storage Boundary:** Uploads go through `UploadController` to public disk. Form submissions reference filenames (not binary data). Validation (type, size) in `Upload/StoreRequest`.

### Requirements to Structure Mapping

| FR Category | Controller | Form Requests | API Resources | Tests |
|-------------|-----------|---------------|---------------|-------|
| Account Management (AM-01..07) | AuthController, ProfileController | RegisterRequest, LoginRequest, UpdateRequest, ChangePasswordRequest | UserResource | Auth/, Profile/ |
| Customer Number Verification (CNV-01..06) | CustomerNumberController | VerifyRequest, ConfirmRequest | CustomerNumberResource | CustomerNumber/ |
| SR Registration (SRR-01..06) | RegistrationController | StoreRequest | RegistrationResource, RegistrationListResource | Registration/ |
| Complaint Management (CM-01..09) | ComplaintController | StoreRequest | ComplaintResource, ComplaintListResource | Complaint/ |
| Master Data (MD-01..06) | MasterDataController | — (read-only) | ProgramResource, RegionResource, ComplaintTypeResource | MasterData/ |
| File Management (FM-01..03) | UploadController | StoreRequest | UploadResource | Upload/ |
| Data Security (DSP-01..04) | Cross-cutting | — | NIK masking in all Resources | Unit/NikMaskingTest |
| Admin Visibility (AV-01..04) | No API needed | — | — | — (Filament existing) |

### Data Flow

```
PWA -> GET /sanctum/csrf-cookie -> XSRF-TOKEN cookie set
PWA -> POST /api/v1/auth/login -> Session cookie returned
PWA -> GET /api/v1/* (with session cookie) -> Middleware stack -> Controller -> Service/Helper -> Model -> SQLite
PWA -> POST /api/v1/customer-numbers/verify -> CustomerNumberController -> CustomerLookupService -> Billing API (external)
PWA -> POST /api/v1/uploads -> UploadController -> Public disk storage -> filename returned
PWA -> POST /api/v1/registrations (with filename reference) -> RegistrationController -> CustomerRegistrationHelper -> DB
```

## Architecture Validation Results

### Coherence Validation

**Decision Compatibility:** All technology choices verified compatible:
- Sanctum SPA cookie auth + same-domain PWA = secure, no localStorage tokens
- spatie/laravel-permission + EnsureCustomerRole middleware = clean role enforcement
- Laravel API Resources + NIK masking logic = centralized data transformation
- Scribe + Form Request classes = auto-generated documentation
- Separate api_v1.php + middleware groups = complete isolation from admin

**Pattern Consistency:** All naming, structure, and format patterns align with:
- Existing project conventions (snake_case DB, PascalCase classes, camelCase methods)
- Laravel framework conventions (resource controllers, Form Requests, API Resources)
- PRD-defined standards (error formats, rate limits, response structures)

**Structure Alignment:** Directory structure directly supports all architectural decisions. Each controller maps to a domain, each Form Request to a validation need, each Resource to a response format.

### Requirements Coverage Validation

**Functional Requirements: 45/45 covered**

| FR Category | FRs | Architectural Support |
|-------------|-----|----------------------|
| Account Management (AM-01..07) | 7 | AuthController + ProfileController |
| Customer Number Verification (CNV-01..06) | 6 | CustomerNumberController + CustomerLookupService |
| SR Registration (SRR-01..06) | 6 | RegistrationController + CustomerRegistrationHelper |
| Complaint Management (CM-01..09) | 9 | ComplaintController + Complaint model |
| Master Data (MD-01..06) | 6 | MasterDataController + 24h cache |
| File Management (FM-01..03) | 3 | UploadController + public disk |
| Data Security & Privacy (DSP-01..04) | 4 | Cross-cutting: NIK masking, scopeOwnedBy, Policies |
| Admin Visibility (AV-01..04) | 4 | No API needed (existing Filament handles this) |

**Non-Functional Requirements: 25/25 covered**
- Performance (5): Response time <500ms, billing timeout configurable, cache strategy, pagination, eager loading
- Security (8): Sanctum auth, CSRF, IDOR prevention, NIK masking, rate limiting, validated input, HttpOnly cookies, role isolation
- Integration (4): Billing API via service, helper reuse, Filament data sharing, source tracking
- Reliability (4): Transaction-safe numbering, error handling, file upload atomicity, graceful billing failures
- Maintainability (4): PSR-12, Pint, Scribe docs, consistent patterns

### Gap Analysis Results

**Critical Gaps:** None

**Important Gaps (documented, non-blocking):**
1. Password reset flow deferred to Post-MVP. Add commented route placeholder in api_v1.php.
2. Email verification deferred. Do NOT apply MustVerifyEmail interface to User model now.
3. OTP verification deferred. Architecture supports adding middleware later.

**Nice-to-Have (future enhancement):**
1. API versioning deprecation strategy for v2 transition
2. Request/response logging middleware for API debugging
3. Laravel Telescope/Pulse for monitoring

### Architecture Completeness Checklist

**Requirements Analysis**
- [x] Project context thoroughly analyzed (45 FRs, 25 NFRs)
- [x] Scale and complexity assessed (Medium, 50 concurrent users)
- [x] Technical constraints identified (8 constraints)
- [x] Cross-cutting concerns mapped (6 concerns)

**Architectural Decisions**
- [x] Critical decisions documented with rationale (7 critical)
- [x] Technology stack fully specified (existing + 3 new packages)
- [x] Integration patterns defined (billing API, file upload, auth)
- [x] Performance considerations addressed (caching, pagination, timeouts)

**Implementation Patterns**
- [x] Naming conventions established (12 categories)
- [x] Structure patterns defined (API layer directory tree)
- [x] Format patterns specified (JSON response standards)
- [x] Process patterns documented (controller flow, error handling, ownership)
- [x] Anti-patterns listed (8 forbidden practices)

**Project Structure**
- [x] Complete directory structure defined (all new files mapped)
- [x] Component boundaries established (API, Auth, Data, External, Storage)
- [x] Integration points mapped (billing API, Filament data, file storage)
- [x] Requirements to structure mapping complete (45 FRs to specific files)

### Architecture Readiness Assessment

**Overall Status:** READY FOR IMPLEMENTATION

**Confidence Level:** HIGH

**Key Strengths:**
- Surgical extension that adds API layer without touching existing admin code
- Complete FR-to-file mapping eliminates ambiguity for implementing agents
- Defense-in-depth security (middleware + scopes + policies)
- Full reuse of existing business logic (helpers, services, models)
- Clear anti-patterns prevent common mistakes

**Areas for Future Enhancement:**
- Password reset, email verification, OTP (deferred to Post-MVP)
- Monitoring and observability (Telescope/Pulse)
- API deprecation strategy for v2
- Performance profiling once real traffic data available

### Implementation Handoff

**AI Agent Guidelines:**
- Follow all architectural decisions exactly as documented
- Use implementation patterns consistently across all components
- Respect project structure and boundaries
- Refer to this document for all architectural questions
- Never modify existing Filament code or admin workflows

**First Implementation Priority:**
1. Install packages: `composer require laravel/sanctum spatie/laravel-permission knuckleswtf/scribe`
2. Run package setup commands (publish configs, run migrations)
3. Create 6 database migrations
4. Configure Sanctum in bootstrap/app.php
5. Implement AuthController (register + login) as proof-of-concept
