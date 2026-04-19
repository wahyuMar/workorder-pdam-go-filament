---
stepsCompleted: ['step-01-validate-prerequisites', 'step-02-design-epics', 'step-03-create-stories', 'step-04-final-validation']
inputDocuments: ['_bmad-output/planning-artifacts/prd.md', '_bmad-output/planning-artifacts/architecture.md']
---

# workorder-pdam-go-filament - Epic Breakdown

## Overview

This document provides the complete epic and story breakdown for workorder-pdam-go-filament, decomposing the requirements from the PRD and Architecture into implementable stories for the Mobile API layer (customer-facing PWA backend).

## Requirements Inventory

### Functional Requirements

- **FR1:** Customer can create a new account by providing name, email, and password
- **FR2:** Customer can log in to the system and receive an authenticated session
- **FR3:** Customer can log out and terminate their session
- **FR4:** Customer can view their own profile information
- **FR5:** Customer can update their profile information
- **FR6:** Customer can change their password
- **FR7:** System rejects duplicate email addresses during registration
- **FR8:** Customer can submit a nomor sambungan to verify its existence in the billing system
- **FR9:** Customer can confirm ownership of a nomor sambungan by providing matching NIK
- **FR10:** Customer can retry NIK verification without lockout if the first attempt fails
- **FR11:** Customer can view a list of all nomor sambungan verified and linked to their account
- **FR12:** Customer can remove a previously verified nomor sambungan from their account
- **FR13:** Customer can retrieve billing data for any of their verified nomor sambungan
- **FR14:** Customer can submit a new SR registration with all required fields (personal data, KTP address, installation address, house data, program selection, document references, GPS coordinates)
- **FR15:** Customer can upload supporting documents (KTP, KK, electricity bill, house photo) before submitting the registration form
- **FR16:** Customer can view a paginated list of their own SR registrations
- **FR17:** Customer can view the full detail of any of their own SR registrations including current processing status
- **FR18:** System auto-generates an official registration number (SRPB-*) upon successful submission
- **FR19:** System records `source: mobile` on all mobile-submitted registrations
- **FR20:** Customer can submit a complaint only for a nomor sambungan that is verified and linked to their account
- **FR21:** Customer can select a complaint type from available active complaint types
- **FR22:** Customer can attach multiple photos (up to 5) to a complaint submission
- **FR23:** Customer can view a paginated list of their own complaints
- **FR24:** Customer can view the full detail of any of their own complaints
- **FR25:** Customer can view the follow-up timeline (progress history) of any of their own complaints
- **FR26:** System auto-generates an official complaint number (PGD-*) upon successful submission
- **FR27:** System auto-fills customer name and address from billing data when a verified nomor is selected
- **FR28:** System auto-sets source, initial status, and priority on mobile-submitted complaints
- **FR29:** Customer can retrieve the list of active programs
- **FR30:** Customer can retrieve provinces, filtered to selectable entries
- **FR31:** Customer can retrieve regencies filtered by a selected province
- **FR32:** Customer can retrieve districts filtered by a selected regency
- **FR33:** Customer can retrieve villages filtered by a selected district
- **FR34:** Customer can retrieve active complaint types
- **FR35:** Customer can upload a file (JPG, PNG, or PDF, max 2MB) and receive a reference identifier
- **FR36:** System validates file MIME type server-side regardless of client-declared content type
- **FR37:** Customer can reference previously uploaded files when submitting registration or complaint forms
- **FR38:** System masks NIK values in all API responses (e.g., `3275****0003`)
- **FR39:** Customer can only access their own registrations, complaints, and customer numbers (IDOR protection)
- **FR40:** Customer role users cannot access the Filament admin panel
- **FR41:** System enforces rate limiting on all endpoints (120/min auth, 100/min guest)
- **FR42:** Admin can view mobile-submitted registrations in the existing Filament registration list without any workflow changes
- **FR43:** Admin can view mobile-submitted complaints in the existing Filament complaint list without any workflow changes
- **FR44:** Admin can filter submissions by source (`manual` / `mobile`) to distinguish channel origin
- **FR45:** Admin can process mobile-submitted data using the exact same workflow as manually-entered data

### NonFunctional Requirements

- **NFR1:** API endpoints return responses within 500ms for standard CRUD operations under normal load
- **NFR2:** Master data endpoints return cached responses within 100ms after initial cache population
- **NFR3:** File upload endpoint accepts and processes files up to 2MB within 3 seconds
- **NFR4:** Billing API verification requests complete within 5 seconds; timeout at 10 seconds with graceful error
- **NFR5:** System supports 50 concurrent authenticated users without performance degradation
- **NFR6:** All API communication uses HTTPS (TLS 1.2+)
- **NFR7:** Session cookies are HttpOnly, Secure, and SameSite=Lax
- **NFR8:** NIK values are never written to application logs, error responses, or debug output
- **NFR9:** All database queries for customer data are scoped by authenticated user ID (preventing IDOR)
- **NFR10:** Failed login attempts are rate-limited to 5 attempts per minute per IP address
- **NFR11:** File uploads are validated by server-side MIME type detection, not client-declared headers
- **NFR12:** Billing API credentials (HMAC keys) are stored in environment variables, never in source code
- **NFR13:** CSRF protection is enforced on all state-changing endpoints via Sanctum's cookie-based flow
- **NFR14:** Billing API timeout is configurable via environment variable (default 10s)
- **NFR15:** Billing API failures return a user-friendly 503 response without exposing internal error details
- **NFR16:** Billing API integration uses the existing CustomerLookupService with no modifications to its core logic
- **NFR17:** All mobile-submitted data is immediately visible in Filament admin without cache delay or manual sync
- **NFR18:** Auto-number generation (SRPB-*, PGD-*) uses database transactions to prevent duplicate numbers under concurrent submissions
- **NFR19:** File upload failures do not corrupt or partially save data; uploads are atomic
- **NFR20:** If billing API is unavailable, customer number verification gracefully fails with clear error message
- **NFR21:** System maintains 99% uptime for API endpoints
- **NFR22:** API code follows PSR-12 standard and passes Laravel Pint linting
- **NFR23:** All endpoints are documented in Swagger/OpenAPI 3.0 spec and Markdown
- **NFR24:** API versioning via URL prefix (`/api/v1/`) allows future versions without breaking existing clients
- **NFR25:** New API features can be added without modifying existing Filament admin code

### Additional Requirements

- AR1: Install and configure `laravel/sanctum` for SPA cookie-based authentication
- AR2: Install and configure `spatie/laravel-permission` for customer role management
- AR3: Install and configure `knuckleswtf/scribe` for API documentation generation
- AR4: Create 6 new database migrations (users role, customer_numbers pivot, user_id on registrations, user_id+source on complaints, uploads table, source on registrations)
- AR5: Create `routes/api_v1.php` and register it in `bootstrap/app.php`
- AR6: Create `EnsureCustomerRole` middleware and register in middleware stack
- AR7: Add `scopeOwnedBy()` to CustomerRegistration and Complaint models
- AR8: Create Policy classes for CustomerRegistration and Complaint
- AR9: Create `BillingApiException` custom exception class
- AR10: Add `api` log channel to `config/logging.php`
- AR11: Configure Sanctum stateful domains and session settings
- AR12: Block customer role from Filament via `canAccessPanel()` on User model
- AR13: No starter template needed — brownfield extension of existing Laravel 12 project

### UX Design Requirements

No UX Design document exists. This is an API-only backend project; the PWA frontend is a separate project outside this scope.

### FR Coverage Map

| FR | Epic | Description |
|----|------|------------|
| FR1 | Epic 1 | Customer account registration |
| FR2 | Epic 1 | Customer login with session |
| FR3 | Epic 1 | Customer logout |
| FR4 | Epic 1 | View own profile |
| FR5 | Epic 1 | Update profile |
| FR6 | Epic 1 | Change password |
| FR7 | Epic 1 | Reject duplicate email |
| FR8 | Epic 2 | Verify nomor sambungan existence |
| FR9 | Epic 2 | Confirm ownership via NIK |
| FR10 | Epic 2 | Retry NIK verification |
| FR11 | Epic 2 | List verified nomor sambungan |
| FR12 | Epic 2 | Remove verified nomor |
| FR13 | Epic 2 | Retrieve billing data |
| FR14 | Epic 4 | Submit SR registration |
| FR15 | Epic 4 | Upload supporting documents |
| FR16 | Epic 4 | List own SR registrations |
| FR17 | Epic 4 | View SR registration detail |
| FR18 | Epic 4 | Auto-generate SRPB-* number |
| FR19 | Epic 4 | Record source: mobile |
| FR20 | Epic 5 | Submit complaint for verified nomor |
| FR21 | Epic 5 | Select complaint type |
| FR22 | Epic 5 | Attach photos to complaint |
| FR23 | Epic 5 | List own complaints |
| FR24 | Epic 5 | View complaint detail |
| FR25 | Epic 5 | View complaint timeline |
| FR26 | Epic 5 | Auto-generate PGD-* number |
| FR27 | Epic 5 | Auto-fill from billing data |
| FR28 | Epic 5 | Auto-set source, status, priority |
| FR29 | Epic 6 | List active programs |
| FR30 | Epic 6 | List provinces (selectable) |
| FR31 | Epic 6 | List regencies by province |
| FR32 | Epic 6 | List districts by regency |
| FR33 | Epic 6 | List villages by district |
| FR34 | Epic 6 | List complaint types |
| FR35 | Epic 3 | Upload file (JPG/PNG/PDF, 2MB) |
| FR36 | Epic 3 | Server-side MIME validation |
| FR37 | Epic 3 | Reference uploaded files in forms |
| FR38 | Epic 1 | NIK masking in responses |
| FR39 | Epic 1 | IDOR protection (ownership scoping) |
| FR40 | Epic 1 | Block customer from Filament |
| FR41 | Epic 1 | Rate limiting enforcement |
| FR42 | Epic 6 | Admin view mobile registrations |
| FR43 | Epic 6 | Admin view mobile complaints |
| FR44 | Epic 6 | Admin filter by source |
| FR45 | Epic 6 | Admin process mobile data same workflow |

## Epic List

### Epic 1: Foundation & Account Management
Customer dapat register, login, logout, dan mengelola profil mereka. Infrastructure API (Sanctum, routes, middleware, role management) disetup sebagai bagian delivery.
**FRs covered:** FR1, FR2, FR3, FR4, FR5, FR6, FR7, FR38, FR39, FR40, FR41
**ARs covered:** AR1, AR2, AR3, AR4, AR5, AR6, AR10, AR11, AR12

### Epic 2: Customer Number Verification
Customer dapat memverifikasi dan menghubungkan nomor sambungan ke akun mereka melalui integrasi billing API.
**FRs covered:** FR8, FR9, FR10, FR11, FR12, FR13
**ARs covered:** AR7, AR9

### Epic 3: File Upload Management
Customer dapat mengupload dokumen pendukung (foto, PDF) dan mendapat reference ID untuk digunakan di form registrasi atau pengaduan.
**FRs covered:** FR35, FR36, FR37

### Epic 4: SR Registration Submission
Customer dapat mengajukan permohonan sambungan baru (SR) lengkap dengan dokumen pendukung, kemudian melacak statusnya.
**FRs covered:** FR14, FR15, FR16, FR17, FR18, FR19
**ARs covered:** AR8 (RegistrationPolicy)

### Epic 5: Complaint Management
Customer dapat mengajukan pengaduan untuk nomor sambungan yang sudah diverifikasi, melampirkan foto, dan melacak progress penyelesaian.
**FRs covered:** FR20, FR21, FR22, FR23, FR24, FR25, FR26, FR27, FR28
**ARs covered:** AR8 (ComplaintPolicy)

### Epic 6: Master Data & Admin Visibility
Customer dapat mengakses data referensi (program, wilayah, tipe pengaduan). Admin dapat memfilter data berdasarkan source channel.
**FRs covered:** FR29, FR30, FR31, FR32, FR33, FR34, FR42, FR43, FR44, FR45


## Epic 1: Foundation & Account Management

Customer dapat register, login, logout, dan mengelola profil mereka. Infrastructure API (Sanctum, routes, middleware, role management) disetup sebagai bagian delivery.

### Story 1.1: Project Setup & API Infrastructure

As a developer,
I want the API foundation installed and configured,
So that all subsequent stories have the infrastructure they need.

**Acceptance Criteria:**

**Given** a fresh project state
**When** packages are installed
**Then** `laravel/sanctum`, `spatie/laravel-permission`, and `knuckleswtf/scribe` are available

**Given** packages are installed
**When** Sanctum config is published
**Then** `config/sanctum.php` exists with `stateful` domains configured

**Given** Sanctum is configured
**When** `routes/api_v1.php` is created
**Then** it is registered in `bootstrap/app.php` with `/api/v1` prefix

**Given** routes exist
**When** `EnsureCustomerRole` middleware is created
**Then** it checks `auth:sanctum` and customer role via spatie/permission

**Given** permission package installed
**When** `customer` role is seeded
**Then** role exists in permissions table

**Given** migrations run
**When** checking schema
**Then** `role` column exists on users table, `customer_numbers` table created, `uploads` table created, `user_id` nullable column added to customer_registrations and complaints, `source` column added to customer_registrations and complaints

**Given** config changes
**When** logging config updated
**Then** `api` channel exists in `config/logging.php`
**And** rate limiters (120 auth/100 guest/5 login per IP) are registered in `bootstrap/app.php`

### Story 1.2: Customer Registration

As a customer,
I want to create an account with my name, email, and password,
So that I can access mobile services.

**Acceptance Criteria:**

**Given** valid name, email, and password
**When** POST `/api/v1/auth/register`
**Then** account is created with customer role, 201 response with user data in API Resource format

**Given** an email that already exists in the system
**When** POST `/api/v1/auth/register`
**Then** 422 response with validation error on email field

**Given** a password less than 8 characters
**When** POST `/api/v1/auth/register`
**Then** 422 response with validation error on password field

**Given** successful registration
**When** checking session state
**Then** user is automatically logged in with session cookie set
**And** user has `customer` role assigned via spatie/permission
**And** `canAccessPanel()` returns false for customer role users

### Story 1.3: Customer Login & Logout

As a customer,
I want to log in and log out of my account,
So that I can securely access and leave the system.

**Acceptance Criteria:**

**Given** valid email and password credentials
**When** POST `/api/v1/auth/login`
**Then** 200 response with user data, session cookie set

**Given** invalid credentials
**When** POST `/api/v1/auth/login`
**Then** 401 response with error message

**Given** 5 failed login attempts within 1 minute from same IP
**When** POST `/api/v1/auth/login` again
**Then** 429 Too Many Requests response

**Given** an authenticated session
**When** POST `/api/v1/auth/logout`
**Then** 200 response, session destroyed, cookie invalidated

**Given** no authentication cookie
**When** accessing any protected endpoint
**Then** 401 Unauthorized response

**Given** customer wants to make state-changing request
**When** GET `/sanctum/csrf-cookie` is called first
**Then** XSRF-TOKEN cookie is set and valid for subsequent requests

### Story 1.4: Customer Profile Management

As a customer,
I want to view and update my profile and change my password,
So that I can keep my account information current.

**Acceptance Criteria:**

**Given** an authenticated customer
**When** GET `/api/v1/profile`
**Then** 200 response with user name, email in API Resource format

**Given** an authenticated customer with valid name and email
**When** PUT `/api/v1/profile`
**Then** 200 response with updated user data

**Given** an email change to an already-existing email
**When** PUT `/api/v1/profile`
**Then** 422 validation error on email field

**Given** an authenticated customer with correct current password and valid new password
**When** PUT `/api/v1/auth/password`
**Then** 200 response, password changed successfully

**Given** wrong current password
**When** PUT `/api/v1/auth/password`
**Then** 422 validation error
**And** all API responses containing NIK values mask them in `3275****0003` format
**And** customer can only access their own profile (IDOR protected)

## Epic 2: Customer Number Verification

Customer dapat memverifikasi dan menghubungkan nomor sambungan ke akun mereka melalui integrasi billing API.

### Story 2.1: Verify Customer Number Existence

As a customer,
I want to submit a nomor sambungan to check if it exists in the billing system,
So that I can begin the process of linking it to my account.

**Acceptance Criteria:**

**Given** an authenticated customer with a valid nomor sambungan
**When** POST `/api/v1/customer-numbers/verify`
**Then** 200 response with billing data (customer name, address) with NIK masked

**Given** a nomor sambungan that does not exist in billing system
**When** POST `/api/v1/customer-numbers/verify`
**Then** 404 response with clear error message

**Given** billing API is unavailable or times out (>10s)
**When** POST `/api/v1/customer-numbers/verify`
**Then** 503 response with user-friendly error message, no internal details exposed
**And** `BillingApiException` is thrown and caught by controller
**And** billing API timeout is configurable via `BILLING_API_TIMEOUT` env variable

### Story 2.2: Confirm Ownership & Link Customer Number

As a customer,
I want to confirm ownership of a nomor sambungan by providing my NIK,
So that the number is verified and linked to my account.

**Acceptance Criteria:**

**Given** a previously verified nomor sambungan and matching NIK
**When** POST `/api/v1/customer-numbers/confirm`
**Then** 200 response, nomor sambungan linked to customer account in `customer_numbers` pivot table with `verified_at` timestamp

**Given** a nomor sambungan with non-matching NIK
**When** POST `/api/v1/customer-numbers/confirm`
**Then** 422 response with error indicating NIK does not match
**And** customer can retry without lockout

**Given** a nomor sambungan already linked to this customer
**When** POST `/api/v1/customer-numbers/confirm`
**Then** 409 Conflict or appropriate response indicating already linked

### Story 2.3: List, View & Remove Verified Numbers

As a customer,
I want to view all my verified nomor sambungan and remove ones I no longer need,
So that I can manage which numbers are linked to my account.

**Acceptance Criteria:**

**Given** an authenticated customer with verified numbers
**When** GET `/api/v1/customer-numbers`
**Then** 200 response with list of all verified nomor sambungan (NIK masked)

**Given** an authenticated customer with a specific verified nomor
**When** GET `/api/v1/customer-numbers/{customerNumber}/billing`
**Then** 200 response with billing data for that nomor sambungan

**Given** an authenticated customer wanting to unlink a nomor
**When** DELETE `/api/v1/customer-numbers/{customerNumber}`
**Then** 200 response, nomor removed from pivot table

**Given** a customer trying to access another customer's nomor
**When** any customer-numbers endpoint
**Then** 403 Forbidden (ownership check via scopeOwnedBy or pivot query)

## Epic 3: File Upload Management

Customer dapat mengupload dokumen pendukung (foto, PDF) dan mendapat reference ID untuk digunakan di form registrasi atau pengaduan.

### Story 3.1: File Upload Endpoint

As a customer,
I want to upload a document file and receive a reference identifier,
So that I can attach it to my registration or complaint submission.

**Acceptance Criteria:**

**Given** an authenticated customer with a valid file (JPG, PNG, or PDF, max 2MB)
**When** POST `/api/v1/uploads` with multipart form data
**Then** 201 response with upload reference (filename/ID) in API Resource format

**Given** a file larger than 2MB
**When** POST `/api/v1/uploads`
**Then** 422 validation error indicating file too large

**Given** a file with invalid MIME type (e.g., .exe)
**When** POST `/api/v1/uploads`
**Then** 422 validation error indicating invalid file type
**And** MIME type is validated server-side regardless of client-declared content type

**Given** a file upload that fails mid-transfer
**When** processing the upload
**Then** no partial file is saved (atomic upload)
**And** file is stored on public disk
**And** Upload model record is created with user_id, original filename, stored path, MIME type

## Epic 4: SR Registration Submission

Customer dapat mengajukan permohonan sambungan baru (SR) lengkap dengan dokumen pendukung, kemudian melacak statusnya.

### Story 4.1: Submit SR Registration

As a customer,
I want to submit a new SR registration with all required information,
So that I can apply for a new water connection.

**Acceptance Criteria:**

**Given** an authenticated customer with all required fields (personal data, KTP address, installation address, house data, program selection, document references, GPS coordinates)
**When** POST `/api/v1/registrations`
**Then** 201 response with registration data including auto-generated SRPB-* number

**Given** the submission
**When** record is created in database
**Then** `source` is set to `mobile`, `user_id` is set to authenticated user
**And** `CustomerRegistrationHelper::generateNoSurat()` is used for number generation
**And** number generation uses database transaction to prevent duplicates

**Given** missing required fields
**When** POST `/api/v1/registrations`
**Then** 422 with specific validation errors per field

**Given** document references (from upload endpoint)
**When** included in registration submission
**Then** references are stored correctly in registration record

### Story 4.2: List & View Own Registrations

As a customer,
I want to view my SR registrations and their current status,
So that I can track the progress of my applications.

**Acceptance Criteria:**

**Given** an authenticated customer with registrations
**When** GET `/api/v1/registrations`
**Then** 200 response with paginated list of own registrations only (RegistrationListResource)

**Given** an authenticated customer
**When** GET `/api/v1/registrations/{registration}`
**Then** 200 response with full registration detail including current status (RegistrationResource)

**Given** a customer trying to view another customer's registration
**When** GET `/api/v1/registrations/{registration}`
**Then** 403 Forbidden via RegistrationPolicy

**Given** pagination parameters
**When** GET `/api/v1/registrations?page=2&per_page=15`
**Then** proper pagination meta and links in response
**And** NIK values are masked in all response data
**And** `scopeOwnedBy` filters all list queries

## Epic 5: Complaint Management

Customer dapat mengajukan pengaduan untuk nomor sambungan yang sudah diverifikasi, melampirkan foto, dan melacak progress penyelesaian.

### Story 5.1: Submit Complaint

As a customer,
I want to submit a complaint for one of my verified customer numbers,
So that I can report issues with my water service.

**Acceptance Criteria:**

**Given** an authenticated customer with a verified nomor sambungan, valid complaint type, and description
**When** POST `/api/v1/complaints`
**Then** 201 response with complaint data including auto-generated PGD-* number

**Given** a nomor sambungan that is NOT verified/linked to customer
**When** POST `/api/v1/complaints`
**Then** 422 validation error — complaints only allowed for verified numbers

**Given** the submission with a verified nomor
**When** record is created
**Then** customer name and address are auto-filled from billing data
**And** `source` set to `mobile`, initial status and priority auto-set
**And** `Complaint::generateNoPengaduan()` used with DB transaction

**Given** up to 5 photo references from upload endpoint
**When** included in complaint submission
**Then** photo references are stored with the complaint record

**Given** more than 5 photo references
**When** POST `/api/v1/complaints`
**Then** 422 validation error on photos field

### Story 5.2: List, View & Track Complaints

As a customer,
I want to view my complaints and track their resolution progress,
So that I can stay informed about my reported issues.

**Acceptance Criteria:**

**Given** an authenticated customer with complaints
**When** GET `/api/v1/complaints`
**Then** 200 paginated list of own complaints only (ComplaintListResource)

**Given** an authenticated customer
**When** GET `/api/v1/complaints/{complaint}`
**Then** 200 full complaint detail including status (ComplaintResource)

**Given** an authenticated customer
**When** GET `/api/v1/complaints/{complaint}/timeline`
**Then** 200 response with follow-up timeline (progress history) for that complaint

**Given** a customer trying to access another customer's complaint
**When** any complaints endpoint
**Then** 403 Forbidden via ComplaintPolicy
**And** NIK values masked in all responses
**And** `scopeOwnedBy` filters all list queries

## Epic 6: Master Data & Admin Visibility

Customer dapat mengakses data referensi (program, wilayah, tipe pengaduan). Admin dapat memfilter data berdasarkan source channel.

### Story 6.1: Master Data Endpoints

As a customer,
I want to retrieve reference data like programs, regions, and complaint types,
So that I can populate form dropdowns in the mobile app.

**Acceptance Criteria:**

**Given** an authenticated customer
**When** GET `/api/v1/master-data/programs`
**Then** 200 response with list of active programs (ProgramResource)

**Given** an authenticated customer
**When** GET `/api/v1/master-data/provinces`
**Then** 200 response with provinces filtered to `is_selectable = true`

**Given** a selected province ID
**When** GET `/api/v1/master-data/regencies?province_id={id}`
**Then** 200 response with regencies for that province

**Given** a selected regency ID
**When** GET `/api/v1/master-data/districts?regency_id={id}`
**Then** 200 response with districts for that regency

**Given** a selected district ID
**When** GET `/api/v1/master-data/villages?district_id={id}`
**Then** 200 response with villages for that district

**Given** an authenticated customer
**When** GET `/api/v1/master-data/complaint-types`
**Then** 200 response with active complaint types
**And** all master data responses are cached for 24 hours
**And** responses use appropriate API Resource classes

### Story 6.2: Admin Source Filter & Visibility

As an admin,
I want to see mobile-submitted data in Filament and filter by source,
So that I can distinguish and process data from different channels.

**Acceptance Criteria:**

**Given** a mobile-submitted registration exists
**When** admin views registration list in Filament
**Then** the registration appears in the existing list without any workflow changes

**Given** a mobile-submitted complaint exists
**When** admin views complaint list in Filament
**Then** the complaint appears in the existing list without any workflow changes

**Given** registrations from both sources exist
**When** admin uses source filter in Filament
**Then** can filter by `manual` or `mobile` source

**Given** complaints from both sources exist
**When** admin uses source filter in Filament
**Then** can filter by `manual` or `mobile` source
**And** admin processes mobile-submitted data using exact same workflow as manual data
**And** no existing Filament code is modified (only additive filter)
