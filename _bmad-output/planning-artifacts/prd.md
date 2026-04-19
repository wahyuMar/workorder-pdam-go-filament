---
stepsCompleted: ['step-01-init', 'step-02-discovery', 'step-02b-vision', 'step-02c-executive-summary', 'step-03-success', 'step-04-journeys', 'step-05-domain', 'step-06-innovation-skipped', 'step-07-project-type', 'step-08-scoping', 'step-09-functional', 'step-10-nonfunctional', 'step-11-polish', 'step-12-complete']
workflowCompleted: true
inputDocuments: ['_bmad-output/brainstorming/brainstorming-session-2026-04-11-1517.md']
workflowType: 'prd'
documentCounts:
  briefs: 0
  research: 0
  brainstorming: 1
  projectDocs: 0
  projectContext: 0
classification:
  projectType: 'api_backend'
  domain: 'utilities_water_management'
  complexity: 'medium'
  projectContext: 'brownfield'
---

# Product Requirements Document - workorder-pdam-go-filament

**Author:** wong
**Date:** 2026-04-11

## Executive Summary

The PDAM Work Order system operates as a Laravel 12 + Filament 4 admin panel for internal staff managing customer registrations (SR), surveys, budgeting, complaints, and work orders. This PRD defines a **customer-facing REST API layer** (27 endpoints across 7 domains) extending the system with a Progressive Web App interface — enabling customers to self-register for new water connections, verify subscription numbers against the billing system, submit complaints, and track request statuses in real-time from mobile devices.

The API layer uses Laravel Sanctum SPA authentication (cookie-based, same-domain), integrates with the external billing system via HMAC-SHA256 for customer identity verification, and enforces strict data isolation between customer accounts. All mobile-submitted data flows into existing Filament admin workflows with zero disruption.

### What Makes This Special

This is not a separate mobile app with its own backend — it is a **surgical extension** of a production-ready admin system. The existing Filament panel, domain models, business logic (auto-numbering, billing lookup service, complaint follow-up workflows), and data integrity chains (Master Data → Registration → Survey → Budgeting) remain completely untouched. The API layer reuses existing Eloquent models, helper classes (`CustomerRegistrationHelper`, `CustomerLookupService`, `Complaint::generateNoPengaduan`), and validation rules, adding only the authentication guard, route definitions, API Resources, and controller thin layer needed to expose these capabilities to customers.

The core insight: **the admin system is already the source of truth** — the mobile API simply opens a customer-facing window into it, bridging the gap between PDAM office operations and customer convenience.

## Project Classification

- **Type:** API Backend (REST, 27 endpoints, Sanctum SPA auth)
- **Domain:** Utilities / Water Management (PDAM)
- **Complexity:** Medium — external billing API integration, multi-role auth, customer identity verification via NIK matching
- **Context:** Brownfield — extending existing Laravel 12 + Filament 4 production system

## Success Criteria

### User Success

- **Zero office visits required:** Customers can complete SR registration, complaint submission, and subscription number management entirely through the PWA without visiting a PDAM office.
- **Self-service completion:** A customer with all required documents ready can complete an SR registration submission in a single session from their mobile device.
- **Real-time transparency:** Customers can check the current status of their SR registration and complaint at any time, with a clear timeline view showing progress through each processing stage.
- **Frictionless identity verification:** Customers can verify and link their subscription numbers (nomor sambungan) to their account using the 2-step verification flow (number check → NIK confirmation) without admin assistance.

### Business Success

- **Channel adoption:** A measurable percentage of new SR registrations and complaints originate from the mobile channel within 3 months of launch, reducing walk-in volume at PDAM offices.
- **Zero admin workflow disruption:** All mobile-submitted data appears in the existing Filament admin panel with no changes to admin processing workflows. Admin staff require zero retraining.
- **Data quality parity:** Mobile submissions maintain the same validation standards as Filament-entered data, ensuring no degradation in data quality.
- **Operational efficiency:** Admin staff can identify the source of each submission (`manual` vs `mobile`) for reporting and process optimization.

### Technical Success

- **Non-breaking integration:** The API layer operates alongside the existing Filament admin with zero modifications to existing models, migrations, helpers, or business logic.
- **Security baseline:** Sanctum SPA auth with HttpOnly cookies, IDOR protection via scoped queries, server-side file validation, rate limiting (120 req/min auth, 100 req/min guest), and NIK masking in API responses.
- **External API reliability:** Billing API integration via HMAC-SHA256 handles timeouts and errors gracefully, displaying clear error messages to the customer.
- **API documentation:** Complete Swagger/OpenAPI spec and Markdown documentation covering all 27 endpoints.

### Measurable Outcomes

- All 27 API endpoints functional and manually tested
- Customer self-registration flow end-to-end operational (register account → verify nomor → submit SR → track status)
- Complaint flow end-to-end operational (select verified nomor → submit complaint with photos → view timeline)
- Billing API integration verified with real external billing system
- Filament admin panel continues operating without any regressions

## Product Scope

### MVP - Minimum Viable Product

**Auth (3 endpoints):** Customer self-register (nama, email, password), login (Sanctum SPA cookie), logout. Role-based access via `spatie/laravel-permission` (customer role only, existing admin untouched).

**Master Data (6 endpoints):** Programs, provinces, regencies, districts, villages, complaint types — all cached 24h, filtered by `is_selectable`/`is_active` flags.

**File Upload (1 endpoint):** Separate upload endpoint for PWA reliability. Max 2MB, JPG/PNG/PDF, server-side MIME validation.

**SR Registration (3 endpoints):** Create registration (all fields matching Filament form, file references from upload endpoint), list own registrations, view detail with status.

**Customer Numbers (5 endpoints):** 2-step verification (verify nomor → confirm NIK), list verified numbers, customer billing lookup, remove number from account.

**Complaints (4 endpoints):** Submit complaint (verified nomor only, multi-photo, auto-set source/status/priority), list own complaints, view detail, view follow-up timeline.

**Profile (3 endpoints):** View profile, update profile, change password.

**Infrastructure:** Migrations (role column, customer_numbers pivot, user_id + source on registrations/complaints, uploads table), API Resources with NIK masking, Laravel standard error responses, Swagger/OpenAPI + Markdown docs.

### Growth Features (Post-MVP)

- **Email verification toggle** — Config-driven `REQUIRE_EMAIL_VERIFICATION=true/false`, activate without redeploy
- **Forgot password OTP** — Email-based OTP flow, endpoints prepared in MVP routing
- **Push notifications** — Infrastructure prepared for status change alerts (SR and complaint updates)
- **Spam protection** — Rate limiting per customer on complaint submission

### Vision (Future)

- **Payment integration** — View and pay water bills directly from the PWA
- **Usage monitoring** — Real-time water meter data and consumption history
- **Multi-language support** — Indonesian + regional language options
- **Offline capability** — PWA service worker for form data caching in areas with poor connectivity
- **Native mobile app** — Migrate from PWA to native Android/iOS if adoption warrants it


## User Journeys

### Journey 1: Ibu Siti — New Water Connection Registration (Customer Happy Path)

**Persona:** Ibu Siti, 42, homemaker in Kecamatan Cibinong. Recently moved to a new house and needs a PDAM water connection. Previously had to take time off work to queue at the PDAM office — wasting time and transportation costs.

**Opening Scene:** Ibu Siti hears from her neighbor that PDAM now has a mobile app. During her lunch break, she opens the PWA in her phone’s browser.

**Rising Action:**
1. She registers a new account — fills in name, email, password. Immediately enters the dashboard.
2. She wants to register for a new connection. Opens the SR Registration form — many fields (personal data, KTP address, installation address, house data).
3. She fills them in one by one. For addresses, the province → regency → district → village cascading dropdowns make it easy — no manual typing needed.
4. She uploads KTP photo, family card, electricity bill, and house photo — one by one through the upload endpoint. Progress bars on each file give confidence that files are sent.
5. Phone automatically captures GPS coordinates for location.
6. She submits the form.

**Climax:** Backend auto-generates `no_surat` (SRPB-*). Screen shows confirmation: "Registration successful! Letter number: SRPB-20260411-0015. You can monitor status on the My Registrations page." Ibu Siti smiles — everything done in 15 minutes without leaving home.

**Resolution:** Next day, Ibu Siti checks status — it has changed to "Survey Scheduled". She knows the process is moving without needing to call or visit the office. Trust in PDAM increases.

**Capabilities Revealed:** Auth register/login, master data endpoints (cascading dropdowns), file upload endpoint, SR registration create, registration list/detail with status tracking.

---

### Journey 2: Ibu Siti — Water Pressure Complaint (Customer Complaint Flow)

**Persona:** Ibu Siti (same), now an existing PDAM customer with a verified subscription number linked to her account.

**Opening Scene:** For 3 days, water pressure at Ibu Siti’s house has been very weak. Laundry is piling up. She’s frustrated and wants to report it, but dreads going to the office.

**Rising Action:**
1. Opens PWA, auto-logged in (session still active, 1-month cookie).
2. Opens "New Complaint" page. Selects subscription number from dropdown (she has 1 verified number).
3. Name, address, phone number auto-filled from linked billing data.
4. Selects complaint type: "Low Water Pressure" from complaint types dropdown.
5. Fills in title: "Weak water pressure 3 days at Perum Griya Asri". Fills in complaint details.
6. Photos of pipe and water meter — uploads 3 photos.
7. GPS auto-captures location. Submits.

**Climax:** "Complaint submitted! Complaint No: PGD-20260411-0003. Our team will follow up shortly." Ibu Siti feels relieved — at least her report is officially recorded.

**Resolution:** 2 days later, Ibu Siti checks the complaint timeline — status changed: "Being Handled → Technician Assigned". She can see progress without manual follow-up. Water returns to normal on day 4.

**Capabilities Revealed:** Complaint create (verified nomor only), complaint types master data, customer billing lookup (auto-fill), file upload (multiple), complaint list/detail, timeline/follow-up view.

---

### Journey 3: Pak Budi — Processing Mobile Submissions (Admin Flow)

**Persona:** Pak Budi, 35, PDAM admin staff in the customer service department. Works daily in the Filament admin panel. Already comfortable with the existing workflow.

**Opening Scene:** Monday morning, Pak Budi opens Filament as usual. In the registration list, there are 3 new entries — 2 marked `source: manual` (entered by colleagues at the counter), 1 marked `source: mobile`.

**Rising Action:**
1. Pak Budi clicks the mobile registration. Data is complete — all fields filled, photos uploaded, GPS coordinates present.
2. He processes it as usual — no workflow difference. Reviews data, schedules a survey.
3. In the complaints section, there are 2 new complaints from mobile (`source: mobile_apps`). Attached photos are clear.
4. Pak Budi assigns follow-up: "Field technician assigned". Status automatically updates.

**Climax:** Pak Budi realizes that mobile data is actually more complete — photos directly from phone, GPS accurate, all fields validated. No paper forms with illegible handwriting.

**Resolution:** Within a month, Pak Budi sees 30% of registrations coming from mobile. Counter queues decrease. He doesn’t need to learn anything new — Filament stays the same, just more data coming in at higher quality.

**Capabilities Revealed:** Zero admin workflow change, source field filtering (`manual`/`mobile`), existing Filament panel unchanged, data quality parity, complaint follow-up updates visible to customer.

---

### Journey 4: Pak Joko — First Time Onboarding & Number Verification (New Customer)

**Persona:** Pak Joko, 28, recently married and moved to a house inherited from his parents. The house already has a PDAM connection (subscription number from his late father) but he’s never managed it himself.

**Opening Scene:** Pak Joko wants to make sure his water connection is registered to his account and know how to report issues if needed. A neighbor tells him about the PDAM PWA.

**Rising Action:**
1. Opens PWA, registers with name, email, password. Enters an empty dashboard — no subscription numbers linked yet.
2. Clicks "Add Subscription Number". Enters the subscription number from an old payment receipt.
3. System verifies against billing API — "Number valid! Please enter NIK to confirm."
4. Pak Joko enters his own NIK. **Fails!** The NIK in billing is still under his father’s name.
5. He remembers his father’s NIK from an old KTP. Tries again — **success!** Subscription number is now linked to his account.
6. Dashboard now shows 1 verified subscription number. He can submit complaints and view status.

**Climax:** Pak Joko feels relieved — his family’s water connection is digitally registered. If there’s a problem, just open the phone.

**Resolution:** A week later, Pak Joko wants to transfer the subscription name to his own. That’s not a mobile feature yet, but at least he knows his subscription is active and can report from his phone. (Future: name transfer via mobile?)

**Capabilities Revealed:** Customer register, customer number verify (2-step), NIK retry on failure (no lock), customer number management (add/list), dashboard with verified numbers, edge case: NIK mismatch recovery.

---

### Journey Requirements Summary

| Capability | J1 | J2 | J3 | J4 |
|------------|:--:|:--:|:--:|:--:|
| Auth (register/login/logout) | ✅ | ✅ | — | ✅ |
| Master Data (cascading dropdowns) | ✅ | ✅ | — | — |
| File Upload (separate endpoint) | ✅ | ✅ | — | — |
| SR Registration (create/list/detail) | ✅ | — | ✅ | — |
| Customer Number (verify/confirm/list) | — | — | — | ✅ |
| Customer Lookup (billing auto-fill) | — | ✅ | — | — |
| Complaint (create/list/detail/timeline) | — | ✅ | ✅ | — |
| Profile (view/update) | — | — | — | ✅ |
| Source Tracking (manual/mobile) | ✅ | ✅ | ✅ | ✅ |
| Status Tracking (real-time) | ✅ | ✅ | — | — |

All 27 API endpoints are covered across these 4 journeys.

## Domain-Specific Requirements

### Compliance & Regulatory

- **Customer Identity Protection:** NIK (national identity number) is classified as sensitive personal data. All API responses must mask NIK values (e.g., `3275****0003`). NIK is never stored in API logs or error responses.
- **Auto-Numbering Integrity:** Official document number formats (`SRPB-*` for registrations, `SRV-*` for surveys, `BGT-*` for budgets, `PGD-*` for complaints) are regulated identifiers. The generation logic (sequential counters with date-based prefixes) must remain untouched and consistent between admin and mobile channels.
- **Data Source Auditability:** Every record created through the mobile API must carry `source: mobile` metadata, enabling regulatory audit trails that distinguish between admin-entered and customer-submitted data.

### Technical Constraints

- **External Billing API Dependency:** The billing system is an external service authenticated via HMAC-SHA256. The API must handle billing system downtime gracefully — returning clear error messages to customers rather than failing silently. Timeout threshold and retry policy must be configurable via environment variables.
- **Backward-Compatible Data Model:** Adding `user_id` to `customer_registrations` and `complaints` tables must use nullable columns to preserve all existing admin-created records. The system must never assume `user_id` is present on legacy data.
- **Session-Based Auth for Same-Domain PWA:** Sanctum SPA auth uses HttpOnly session cookies (not bearer tokens). This constrains the PWA to same-domain deployment but provides stronger security against XSS-based token theft. Cross-domain deployment would require architecture change.
- **File Upload Reliability:** Separate upload endpoint pattern (upload first, reference in form) is a domain constraint driven by unstable mobile connections in service areas. Server must validate MIME types server-side regardless of client-declared content type.

### Integration Requirements

- **Billing API — 2-Step Verification Flow:**
  - Step 1: `POST /verify` — lightweight check if nomor sambungan exists in billing system
  - Step 2: `POST /confirm` — NIK match validation (only if step 1 succeeds)
  - Both steps use `CustomerLookupService` which is already production-tested in the Filament complaint form
- **Filament Admin Panel — Zero-Touch Integration:**
  - Mobile-submitted data appears in existing Filament list/detail views without any Filament code changes
  - Admin users process mobile and manual submissions identically
  - Source column enables filtering but does not change workflow
- **Existing Helper Reuse:**
  - `CustomerRegistrationHelper::generateNoSurat()` — must be called for mobile submissions exactly as for admin
  - `Complaint::generateNoPengaduan()` — transaction-safe auto-numbering must be used identically
  - `CustomerLookupService::fetchByNoSambungan()` — billing API integration already handles auth and error mapping

### Risk Mitigations

| Risk | Impact | Mitigation |
|------|--------|------------|
| Billing API downtime during customer verification | Customer cannot link nomor sambungan | Clear error message with retry guidance; no partial state saved |
| NIK data leak via API response | Privacy violation, regulatory risk | Server-side masking in API Resource layer; NIK never in logs |
| Mobile submissions overwhelming admin queue | Admin workflow disruption | Source-based filtering in Filament; rate limiting on mobile endpoints |
| Conflicting auto-number generation (race condition) | Duplicate document numbers | Existing DB transaction + lock mechanism in helpers (already production-tested) |
| Customer submitting SR for already-registered address | Duplicate registrations | Same validation rules as Filament form; backend uniqueness checks |
| Orphaned file uploads (uploaded but never referenced) | Storage bloat | Uploads table with `used` flag; periodic cleanup job (Growth feature) |

## API Backend Specific Requirements

### Project-Type Overview

REST API backend extending an existing Laravel 12 + Filament 4 admin system. 27 endpoints across 7 domains, serving a same-domain PWA client. The API layer reuses existing Eloquent models, helpers, and validation logic — adding only controllers, API Resources, routes, and Sanctum auth guard.

### Endpoint Specifications

#### Auth Domain (3 endpoints)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/register` | Guest | Create customer account (name, email, password) |
| POST | `/api/v1/login` | Guest | Sanctum SPA login, returns session cookie |
| POST | `/api/v1/logout` | Auth | Destroy session |

#### Master Data Domain (6 endpoints)

| Method | Endpoint | Auth | Cache | Description |
|--------|----------|------|-------|-------------|
| GET | `/api/v1/master/programs` | Auth | 24h | Active programs list |
| GET | `/api/v1/master/provinces` | Auth | 24h | Provinces (is_selectable) |
| GET | `/api/v1/master/regencies/{province}` | Auth | 24h | Regencies by province |
| GET | `/api/v1/master/districts/{regency}` | Auth | 24h | Districts by regency |
| GET | `/api/v1/master/villages/{district}` | Auth | 24h | Villages by district |
| GET | `/api/v1/master/complaint-types` | Auth | 24h | Active complaint types |

#### File Upload Domain (1 endpoint)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/uploads` | Auth | Upload file (max 2MB, JPG/PNG/PDF), returns filename |

#### SR Registration Domain (3 endpoints)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/registrations` | Auth | Create SR registration (all Filament form fields + file refs) |
| GET | `/api/v1/registrations` | Auth | List own registrations (paginated) |
| GET | `/api/v1/registrations/{id}` | Auth | View registration detail with status |

#### Customer Numbers Domain (5 endpoints)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/customer-numbers/verify` | Auth | Step 1: Check nomor sambungan exists |
| POST | `/api/v1/customer-numbers/confirm` | Auth | Step 2: Confirm NIK match, link to account |
| GET | `/api/v1/customer-numbers` | Auth | List verified numbers |
| GET | `/api/v1/customer-numbers/{no}/billing` | Auth | Fetch billing data for verified number |
| DELETE | `/api/v1/customer-numbers/{no}` | Auth | Remove number from account |

#### Complaints Domain (4 endpoints)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/v1/complaints` | Auth | Submit complaint (verified nomor only) |
| GET | `/api/v1/complaints` | Auth | List own complaints (paginated) |
| GET | `/api/v1/complaints/{id}` | Auth | View complaint detail |
| GET | `/api/v1/complaints/{id}/timeline` | Auth | View follow-up timeline |

#### Profile Domain (3 endpoints)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/v1/profile` | Auth | View own profile |
| PUT | `/api/v1/profile` | Auth | Update profile |
| PUT | `/api/v1/profile/password` | Auth | Change password |

### Authentication Model

- **Method:** Laravel Sanctum SPA Authentication (cookie-based)
- **Session Lifetime:** 1 month (`SESSION_LIFETIME=43200`)
- **Cookie:** HttpOnly, Secure, SameSite=Lax (same-domain PWA)
- **CSRF:** Sanctum's `/sanctum/csrf-cookie` endpoint for SPA flow
- **Guard:** Customer role via `spatie/laravel-permission`, separated from admin Filament guard
- **Admin Isolation:** Customer role users blocked from Filament via `canAccessPanel()` returning false

### Data Schemas

- **Request Format:** `application/json` for all endpoints, `multipart/form-data` for file upload
- **Response Format:** JSON with Laravel API Resource wrappers
- **Pagination:** Laravel default pagination envelope (`data`, `links`, `meta`)
- **Sensitive Data:** NIK masked in all responses (e.g., `3275****0003`), never in logs
- **Timestamps:** ISO 8601 format (`2026-04-11T10:30:00Z`)
- **File References:** Upload returns `{filename, original_name, size, mime_type}`, forms reference by filename

### Error Codes

| HTTP Status | Format | Usage |
|-------------|--------|-------|
| 200 | `{data: {...}}` | Success response |
| 201 | `{data: {...}, message: "..."}` | Resource created |
| 401 | `{message: "Unauthenticated."}` | Session expired or not logged in |
| 403 | `{message: "..."}` | IDOR violation, wrong role, unverified nomor |
| 404 | `{message: "..."}` | Resource not found or not owned by user |
| 422 | `{message: "...", errors: {field: [...]}}` | Validation failure (Laravel standard) |
| 429 | `{message: "Too Many Requests"}` | Rate limit exceeded |
| 500 | `{message: "Server Error"}` | Unexpected error (no stack trace in production) |
| 503 | `{message: "Billing service unavailable"}` | External billing API timeout/error |

### Rate Limits

- **Authenticated endpoints:** 120 requests/minute per user
- **Guest endpoints (login/register):** 100 requests/minute per IP
- **File upload:** 20 requests/minute per user (subset of auth limit)
- **Implementation:** Laravel's built-in `RateLimiter` in `bootstrap/app.php`

### API Documentation

- **Swagger/OpenAPI 3.0** specification file generated via `l5-swagger` or manual YAML
- **Markdown documentation** for developer reference (all endpoints, request/response examples)
- **Location:** `docs/api/` directory in repository
- **Maintained alongside code** — updated with each endpoint change

### Implementation Considerations

- **Controller Pattern:** Thin controllers delegating to existing helpers/services. No business logic in controllers.
- **API Resources:** One resource class per model for consistent response formatting and NIK masking.
- **Route File:** Separate `routes/api_v1.php` loaded in `bootstrap/app.php` with `/api/v1` prefix.
- **Middleware Stack:** `web` (for Sanctum SPA cookies) + `auth:sanctum` + custom `role:customer` middleware.
- **Versioning:** URL-based (`/api/v1/`), enabling future `/api/v2/` without breaking existing clients.

## Project Scoping & Phased Development

### MVP Strategy & Philosophy

**MVP Approach:** Problem-Solving MVP — deliver the minimum feature set that eliminates the need for customers to visit PDAM offices for SR registration and complaint submission.

**Resource Requirements:** 1 full-stack Laravel developer (familiar with Filament + Sanctum). No frontend developer needed for MVP (API-only, PWA built separately). No DevOps — same server, same database.

**Key Constraint:** Zero disruption to production Filament admin. All changes are additive (new files, new migrations, new routes).

### MVP Feature Set (Phase 1)

**Core User Journeys Supported:**
- Journey 1: SR Registration (customer submits from mobile)
- Journey 2: Complaint submission (customer reports issues)
- Journey 3: Admin processing (zero workflow change)
- Journey 4: Onboarding & number verification

**Must-Have Capabilities (25 endpoints):**

| Domain | Endpoints | Rationale |
|--------|-----------|-----------|
| Auth | 3 | Cannot use app without account |
| Master Data | 6 | SR form requires cascading dropdowns |
| File Upload | 1 | SR requires document uploads |
| SR Registration | 3 | Primary value proposition |
| Customer Numbers | 5 | Required for complaint submission + identity |
| Complaints | 4 | Secondary value proposition |
| Profile | 3 | Basic account management |

**Deferred from MVP (explicit exclusions):**
- Email verification flow (prepared, config-toggled off)
- Forgot password OTP (endpoints reserved but not implemented)
- Push notifications (infrastructure only)
- Spam protection beyond rate limiting

### Post-MVP Features

See [Product Scope > Growth Features and Vision](#product-scope) for the complete phased roadmap.

### Risk Mitigation Strategy

**Technical Risks:**
- *Billing API integration complexity* — Mitigated: `CustomerLookupService` already production-tested in Filament complaints. Reuse same service, same HMAC auth.
- *Sanctum SPA auth conflicts with Filament* — Mitigated: Sanctum SPA uses `web` guard with session cookies. Filament already uses `web` guard. Customer role separation via `canAccessPanel()` + middleware.
- *Migration breaking existing data* — Mitigated: All new columns nullable, new tables only, no existing column modifications.

**Market Risks:**
- *Low customer adoption* — Mitigated: MVP validates with real users before Growth investment. Source tracking enables adoption measurement.
- *Customers prefer office visits* — Mitigated: API doesn't replace office channel, it adds a parallel channel. Zero risk of alienating non-digital customers.

**Resource Risks:**
- *Single developer bottleneck* — Mitigated: Each domain is independent (Auth → Master Data → Registration → etc.). Can be parallelized if second developer available.
- *Reduced scope needed* — Minimum viable: Auth + Master Data + SR Registration (3 domains, 12 endpoints) still delivers primary value. Complaints and Customer Numbers can be Phase 1b.

## Functional Requirements

### Account Management

- **FR1:** Customer can create a new account by providing name, email, and password
- **FR2:** Customer can log in to the system and receive an authenticated session
- **FR3:** Customer can log out and terminate their session
- **FR4:** Customer can view their own profile information
- **FR5:** Customer can update their profile information
- **FR6:** Customer can change their password
- **FR7:** System rejects duplicate email addresses during registration

### Customer Number Verification

- **FR8:** Customer can submit a nomor sambungan to verify its existence in the billing system
- **FR9:** Customer can confirm ownership of a nomor sambungan by providing matching NIK
- **FR10:** Customer can retry NIK verification without lockout if the first attempt fails
- **FR11:** Customer can view a list of all nomor sambungan verified and linked to their account
- **FR12:** Customer can remove a previously verified nomor sambungan from their account
- **FR13:** Customer can retrieve billing data for any of their verified nomor sambungan

### SR Registration

- **FR14:** Customer can submit a new SR registration with all required fields (personal data, KTP address, installation address, house data, program selection, document references, GPS coordinates)
- **FR15:** Customer can upload supporting documents (KTP, KK, electricity bill, house photo) before submitting the registration form
- **FR16:** Customer can view a paginated list of their own SR registrations
- **FR17:** Customer can view the full detail of any of their own SR registrations including current processing status
- **FR18:** System auto-generates an official registration number (SRPB-*) upon successful submission
- **FR19:** System records `source: mobile` on all mobile-submitted registrations

### Complaint Management

- **FR20:** Customer can submit a complaint only for a nomor sambungan that is verified and linked to their account
- **FR21:** Customer can select a complaint type from available active complaint types
- **FR22:** Customer can attach multiple photos (up to 5) to a complaint submission
- **FR23:** Customer can view a paginated list of their own complaints
- **FR24:** Customer can view the full detail of any of their own complaints
- **FR25:** Customer can view the follow-up timeline (progress history) of any of their own complaints
- **FR26:** System auto-generates an official complaint number (PGD-*) upon successful submission
- **FR27:** System auto-fills customer name and address from billing data when a verified nomor is selected
- **FR28:** System auto-sets source, initial status, and priority on mobile-submitted complaints

### Master Data Access

- **FR29:** Customer can retrieve the list of active programs
- **FR30:** Customer can retrieve provinces, filtered to selectable entries
- **FR31:** Customer can retrieve regencies filtered by a selected province
- **FR32:** Customer can retrieve districts filtered by a selected regency
- **FR33:** Customer can retrieve villages filtered by a selected district
- **FR34:** Customer can retrieve active complaint types

### File Management

- **FR35:** Customer can upload a file (JPG, PNG, or PDF, max 2MB) and receive a reference identifier
- **FR36:** System validates file MIME type server-side regardless of client-declared content type
- **FR37:** Customer can reference previously uploaded files when submitting registration or complaint forms

### Data Security & Privacy

- **FR38:** System masks NIK values in all API responses (e.g., `3275****0003`)
- **FR39:** Customer can only access their own registrations, complaints, and customer numbers (IDOR protection)
- **FR40:** Customer role users cannot access the Filament admin panel
- **FR41:** System enforces rate limiting on all endpoints (120/min auth, 100/min guest)

### Admin Visibility (Zero-Change)

- **FR42:** Admin can view mobile-submitted registrations in the existing Filament registration list without any workflow changes
- **FR43:** Admin can view mobile-submitted complaints in the existing Filament complaint list without any workflow changes
- **FR44:** Admin can filter submissions by source (`manual` / `mobile`) to distinguish channel origin
- **FR45:** Admin can process mobile-submitted data using the exact same workflow as manually-entered data

## Non-Functional Requirements

### Performance

- **NFR1:** API endpoints return responses within 500ms for standard CRUD operations (list, detail, create) under normal load
- **NFR2:** Master data endpoints return cached responses within 100ms after initial cache population
- **NFR3:** File upload endpoint accepts and processes files up to 2MB within 3 seconds
- **NFR4:** Billing API verification requests complete within 5 seconds (including external API call); timeout at 10 seconds with graceful error
- **NFR5:** System supports 50 concurrent authenticated users without performance degradation

### Security

- **NFR6:** All API communication uses HTTPS (TLS 1.2+)
- **NFR7:** Session cookies are HttpOnly, Secure, and SameSite=Lax
- **NFR8:** NIK values are never written to application logs, error responses, or debug output
- **NFR9:** All database queries for customer data are scoped by authenticated user ID (preventing IDOR)
- **NFR10:** Failed login attempts are rate-limited to 5 attempts per minute per IP address
- **NFR11:** File uploads are validated by server-side MIME type detection, not client-declared headers
- **NFR12:** Billing API credentials (HMAC keys) are stored in environment variables, never in source code
- **NFR13:** CSRF protection is enforced on all state-changing endpoints via Sanctum's cookie-based flow

### Integration

- **NFR14:** Billing API timeout is configurable via environment variable (`BILLING_API_TIMEOUT`, default 10s)
- **NFR15:** Billing API failures return a user-friendly 503 response without exposing internal error details
- **NFR16:** Billing API integration uses the existing `CustomerLookupService` with no modifications to its core logic
- **NFR17:** All mobile-submitted data is immediately visible in Filament admin without cache delay or manual sync

### Reliability

- **NFR18:** Auto-number generation (SRPB-*, PGD-*) uses database transactions to prevent duplicate numbers under concurrent submissions
- **NFR19:** File upload failures do not corrupt or partially save data; uploads are atomic
- **NFR20:** If billing API is unavailable, customer number verification gracefully fails with clear error message; no partial state is persisted
- **NFR21:** System maintains 99% uptime for API endpoints (excluding scheduled maintenance)

### Maintainability

- **NFR22:** API code follows PSR-12 standard and passes Laravel Pint linting
- **NFR23:** All endpoints are documented in Swagger/OpenAPI 3.0 spec and Markdown
- **NFR24:** API versioning via URL prefix (`/api/v1/`) allows future versions without breaking existing clients
- **NFR25:** New API features can be added without modifying existing Filament admin code
