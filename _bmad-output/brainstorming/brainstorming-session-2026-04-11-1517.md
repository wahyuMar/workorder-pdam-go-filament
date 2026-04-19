---
stepsCompleted: [1, 2, 3, 4]
inputDocuments: []
session_topic: 'Mobile API Backend Development for PDAM Work Order System'
session_goals: 'Design Sanctum SPA Auth for customer role, API for new SR registration, API for customer number management with external billing integration, Complaint API'
selected_approach: 'ai-recommended'
techniques_used: ['Question Storming', 'Morphological Analysis', 'Role Playing']
ideas_generated: 60
technique_execution_complete: true
session_continued: true
continuation_date: '2026-04-11T15:53'
session_active: false
workflow_completed: true
context_file: ''
---

# Brainstorming Session Results

**Facilitator:** wong
**Date:** 2026-04-11T15:17

## Session Overview

**Topic:** Mobile API Backend Development for PDAM Work Order System
**Goals:** Design comprehensive mobile API layer covering JWT authentication (customer role), SR registration endpoint, and customer number management with NIK verification & external billing API integration.

### Context Guidance

_Existing Laravel 12 + Filament 4 project with admin web auth. Need to extend backend with mobile-facing REST APIs. External billing system accessible via BILLING_API_BASE_URI. Domain chain: Master Data → Registration → Survey → Budgeting._

### Session Setup

_Brainstorming session initialized to explore API architecture, authentication strategy, data flow, UX considerations, security implications, and integration patterns for the PDAM mobile backend._

## Technique Selection

**Approach:** AI-Recommended Techniques
**Analysis Context:** Mobile API Backend Development with focus on JWT Auth, SR Registration API, Customer Number Management + Billing Integration

**Recommended Techniques:**

- **Question Storming:** Foundation setting — uncover all critical questions before designing APIs
- **Morphological Analysis:** Systematic exploration of all API parameter combinations across domains
- **Role Playing:** Multi-stakeholder validation from Customer, Admin, Billing System, Security Auditor perspectives

**AI Rationale:** Complex multi-domain technical topic (auth + API design + external integration + DB design) benefits from question-first approach, systematic parameter exploration, and multi-perspective validation to ensure comprehensive coverage.

## Technique Execution Results

### Technique 1: Question Storming 🔍

**Interactive Focus:** Uncovering all critical questions across Auth, SR Registration, Billing/Customer Number, and Complaint domains before any design decisions.

**Key Breakthroughs:**

- **Auth Strategy Pivot:** Initially planned JWT token-based, discovered Sanctum SPA Auth (cookie-based) is optimal for same-domain PWA
- **Billing Verification Flow:** Discovered 2-step verification pattern (verify nomor → confirm NIK) from analyzing existing billing project
- **Complaint Domain Addition:** Originally not in scope, added complete Complaint/Pengaduan API after discovering existing `CustomerLookupService` could be reused
- **Reference Architecture:** Analyzed `pdam-billing-laravel-improvement` project to extract proven patterns (JWT config, rate limiting, data hiding, API versioning)

**User Creative Strengths:** Decisive, pragmatic "prepare-first, implement-later" approach. Strong domain knowledge of PDAM workflows.

**Energy Level:** High and consistent — clear answers with strategic thinking.

### Technique 2: Morphological Analysis 🧩

**Building on Previous:** Used Question Storming decisions to systematically map ALL 27 API endpoints across 7 domains with method, auth level, request format, and response type.

**New Insights — 14 Blind Spots Discovered:**

1. **Customer User Model** → Same `users` table + role column (not separate table)
2. **Complaint ↔ Customer Number Link** → Only verified nomor sambungan allowed
3. **CORS Configuration** → Same domain PWA, minimal config needed
4. **File Upload Strategy** → Separate upload endpoint for PWA reliability
5. **Status Tracking** → Both SR Registration AND Complaints
6. **Data Scoping** → `user_id` nullable + scoped queries (IDOR protection)
7. **Permission System** → `spatie/laravel-permission`, customer role only, don't touch existing admin
8. **CORS Domain** → Same domain for now
9. **Master Data Caching** → Cache 24h for rarely-changing data
10. **Notifications** → Infrastructure prepared, implement later
11. **Migration Strategy** → Standard Laravel migrations, backward compatible
12. **API Documentation** → Swagger/OpenAPI + Markdown
13. **Testing** → Manual testing first
14. **Source Tracking** → `source` field: 'manual' (Filament) / 'mobile' (API)

**Developed Ideas:** Complete endpoint matrix with auth levels, request formats, and response types for all 27 endpoints.

### Technique 3: Role Playing 🎭

**Building on Previous:** Validated entire architecture from 4 stakeholder perspectives to find edge cases.

**Perspectives & Edge Cases Found:**

**🧑 Customer (Ibu Siti):**
- Register payload: nama + email + password (simple)
- No draft accepted as UX trade-off
- NIK retry anytime (no lock)
- Dropdown select from verified nomor sambungan for complaints
- Complaint tracking: list → detail → timeline progress

**👨‍💼 Admin (Pak Budi):**
- Mobile data immediately visible in Filament (no approval queue)
- Same complaint processing flow regardless of source
- No spam protection for now
- Status updates visible to customer per existing flow

**🖥️ Billing System:**
- No significant traffic increase concern
- NIK data in billing must be mandatory (wong to coordinate with billing team)

**🔒 Security Auditor:**
- Email uniqueness enforced
- Server-side file type validation
- IDOR protection via scoped queries
- **Token Strategy Pivot:** Changed from 60-min token to Sanctum SPA cookie-based auth with 1-month session lifetime (more secure for PWA)

### Creative Facilitation Narrative

_This session was a masterclass in pragmatic API design. Wong demonstrated exceptional decision-making clarity — every answer was decisive with clear rationale. The "prepare-first, implement-later" pattern emerged as a core philosophy, allowing the architecture to be modular and future-proof without over-engineering the initial implementation. The pivotal moment was discovering that Sanctum SPA Auth (cookie-based) is superior to token-based auth for a same-domain PWA, which simplified the entire security model. The addition of Complaint API mid-session showed Wong's ability to expand scope strategically when value is clear._

### Session Highlights

**User Creative Strengths:** Decisive, domain-expert, pragmatic architect
**AI Facilitation Approach:** Reference-driven (analyzed existing billing project), systematic blind spot hunting
**Breakthrough Moments:** Auth strategy pivot to Sanctum SPA, 2-step billing verification, Complaint API scope expansion
**Energy Flow:** Consistently high — Wong maintained sharp decision-making throughout all 3 techniques

---

## Idea Organization and Prioritization

### Theme 1: 🔐 Authentication & Authorization

| # | Decision | Detail |
|---|----------|--------|
| 1 | Auth method | **Sanctum SPA Auth (cookie-based)** — optimal for same-domain PWA |
| 2 | Session lifetime | **1 month** (`SESSION_LIFETIME=43200`), HttpOnly cookie |
| 3 | Register payload | nama + email + password |
| 4 | Email uniqueness | ✅ Enforced at registration |
| 5 | Email verification | Prepared (toggle config), not active initially |
| 6 | Forgot password | OTP via email — prepared, implement later |
| 7 | Multi-device | ✅ Allowed (concurrent sessions) |
| 8 | Permission system | `spatie/laravel-permission` — customer role only |
| 9 | Filament guard | `canAccessPanel()` rejects customer role |
| 10 | Rate limiting | 120 req/min (auth), 100 req/min (guest) |

### Theme 2: 🏗️ API Architecture & Infrastructure

| # | Decision | Detail |
|---|----------|--------|
| 1 | Versioning | `/api/v1/` URL prefix, separate `routes/api_v1.php` file |
| 2 | Response format | **API Resources** (`JsonResource`) classes |
| 3 | Sensitive data | **NIK masking** (e.g., `3275****0003`) |
| 4 | Error format | Laravel standard: `{message, errors}` (422), `{message, error}` (4xx/5xx) |
| 5 | CORS | Same domain — minimal/default Laravel config |
| 6 | Master data cache | ✅ Cache 24 hours |
| 7 | Documentation | **Swagger/OpenAPI** + Markdown |
| 8 | Testing | Manual testing first |
| 9 | File upload | **Separate upload endpoint** (`POST /api/v1/uploads`) |
| 10 | Security | Server-side file validation + IDOR protection (scoped queries) |

### Theme 3: 🗃️ Data Model Evolution

| # | Decision | Detail |
|---|----------|--------|
| 1 | User model | Same `users` table + `role` column (nullable, backward compat) |
| 2 | Customer numbers | New **`customer_numbers`** pivot table (`user_id`, `no_sambungan`, `verified_at`) |
| 3 | Registration ownership | Add `user_id` nullable to `customer_registrations` |
| 4 | Complaint ownership | Add `user_id` nullable to `complaints` |
| 5 | Source tracking | Add `source` field: `'manual'` (Filament default) / `'mobile'` (API) |
| 6 | Uploads | New `uploads` table for separate upload endpoint |

**Migrations Required (new files only — never edit existing):**
1. `add_role_to_users_table`
2. `create_customer_numbers_table`
3. `add_user_id_to_customer_registrations_table`
4. `add_user_id_and_source_to_complaints_table`
5. `create_uploads_table`
6. `add_source_to_customer_registrations_table`

### Theme 4: 📋 SR Registration API

| # | Decision | Detail |
|---|----------|--------|
| 1 | Format | Multipart (via separate upload endpoint + filename references) |
| 2 | Fields | All fields identical to Filament form |
| 3 | Master data endpoints | 6 endpoints: programs, provinces, regencies, districts, villages, complaint-types |
| 4 | File specs | Max 2MB, JPG/PNG/PDF, 4 documents (KTP, KK, tagihan listrik, foto rumah) |
| 5 | Coordinates | lat/lng sent directly from mobile GPS |
| 6 | Auto-generate | `no_surat` via `CustomerRegistrationHelper::generateNoSurat()` at backend |
| 7 | Draft | ❌ Not supported |
| 8 | Validation | Identical rules to Filament form |
| 9 | Pagination | Not needed (customer typically has 1-5 registrations) |

### Theme 5: 💰 Customer Number & Billing Integration

| # | Decision | Detail |
|---|----------|--------|
| 1 | Verification flow | **2-step**: verify nomor pelanggan → confirm with NIK |
| 2 | Billing auth | HMAC-SHA256 (same as `pdam-billing-laravel-improvement`) |
| 3 | Multi-nomor | ✅ One customer can have multiple nomor sambungan |
| 4 | Management | Add/remove nomor sambungan from account |
| 5 | NIK source | Verified against billing data via external API |
| 6 | Billing API down | Show error to user (no fallback/offline mode) |
| 7 | NIK retry | ✅ Can retry anytime, no lock on nomor |
| 8 | Data from billing | Validation only (valid/invalid + NIK for matching) |

**Verification Flow:**
```
Mobile: Input nomor sambungan
  → Backend: POST /customer-numbers/verify → Billing API
  → Response: {valid: true, needs_nik: true}
Mobile: Input NIK
  → Backend: POST /customer-numbers/confirm → Compare NIK
  → Response: {verified: true, customer_number linked}
```

### Theme 6: 📢 Complaint/Pengaduan API

| # | Decision | Detail |
|---|----------|--------|
| 1 | Form fields | Same as Filament `ComplaintsForm` |
| 2 | Scope restriction | Only **verified** nomor sambungan (dropdown from account) |
| 3 | Auto-set fields | `sumber='mobile_apps'`, `status='pending'`, `priority='medium'` |
| 4 | Photos | Multiple (max 5), via separate upload endpoint |
| 5 | Auto-generate | `no_pengaduan` via `Complaint::generateNoPengaduan()` at backend |
| 6 | Tracking | List view → click → detail with follow-up timeline |
| 7 | Service reuse | `CustomerLookupService::fetchByNoSambungan()` from existing codebase |
| 8 | Master data | New endpoint: `GET /api/v1/complaint-types` (filtered `is_active=true`) |

### Theme 7: 📱 Cross-cutting & Future Readiness

| # | Decision | Detail |
|---|----------|--------|
| 1 | Profile | GET profile, UPDATE profile, change password |
| 2 | Status tracking | Both SR Registration and Complaint visible to customer |
| 3 | Notifications | Infrastructure prepared, implement later |
| 4 | Admin visibility | Mobile-submitted data immediately visible in Filament |
| 5 | Spam protection | Not now |
| 6 | Extensibility | Architecture supports adding new endpoints easily |

---

## Complete API Endpoint Map (27 Endpoints)

### 🔐 Auth (5 endpoints)

| Method | Endpoint | Auth | Request | Response | Status |
|--------|----------|------|---------|----------|--------|
| POST | `/api/v1/auth/register` | 🔓 Public | JSON | Single (user) | **Build** |
| POST | `/api/v1/auth/login` | 🔓 Public | JSON | Single (user + session) | **Build** |
| POST | `/api/v1/auth/logout` | 🔒 Auth | — | Status | **Build** |
| POST | `/api/v1/auth/forgot-password` | 🔓 Public | JSON | Status | Prepared |
| POST | `/api/v1/auth/verify-email` | 🔒 Auth | JSON | Status | Prepared |

### 📚 Master Data (6 endpoints)

| Method | Endpoint | Auth | Response | Cache |
|--------|----------|------|----------|-------|
| GET | `/api/v1/programs` | 🔒 Auth | Collection | 24h |
| GET | `/api/v1/provinces` | 🔒 Auth | Collection | 24h |
| GET | `/api/v1/regencies?province_id={id}` | 🔒 Auth | Collection | 24h |
| GET | `/api/v1/districts?regency_id={id}` | 🔒 Auth | Collection | 24h |
| GET | `/api/v1/villages?district_id={id}` | 🔒 Auth | Collection | 24h |
| GET | `/api/v1/complaint-types` | 🔒 Auth | Collection | 24h |

### 📎 Uploads (1 endpoint)

| Method | Endpoint | Auth | Request | Response |
|--------|----------|------|---------|----------|
| POST | `/api/v1/uploads` | 🔒 Auth | Multipart | Single (filename, URL) |

**Constraints:** Max 2MB, accepted: JPG, PNG, PDF. Server-side MIME validation.

### 📋 SR Registration (3 endpoints)

| Method | Endpoint | Auth | Request | Response |
|--------|----------|------|---------|----------|
| POST | `/api/v1/registrations` | 🔒 Auth | JSON (with upload refs) | Single (registration) |
| GET | `/api/v1/registrations` | 🔒 Auth | — | Collection (scoped) |
| GET | `/api/v1/registrations/{id}` | 🔒 Auth | — | Single (+ status) |

### 💰 Customer Numbers (5 endpoints)

| Method | Endpoint | Auth | Request | Response |
|--------|----------|------|---------|----------|
| POST | `/api/v1/customer-numbers/verify` | 🔒 Auth | JSON (`no_sambungan`) | Status (valid + needs NIK) |
| POST | `/api/v1/customer-numbers/confirm` | 🔒 Auth | JSON (`no_sambungan` + `nik`) | Single (linked number) |
| GET | `/api/v1/customer-numbers` | 🔒 Auth | — | Collection (user's numbers) |
| GET | `/api/v1/customer-lookup/{no_sambungan}` | 🔒 Auth | — | Single (billing data) |
| DELETE | `/api/v1/customer-numbers/{id}` | 🔒 Auth | — | Status |

### 📢 Complaints (4 endpoints)

| Method | Endpoint | Auth | Request | Response |
|--------|----------|------|---------|----------|
| POST | `/api/v1/complaints` | 🔒 Auth | JSON (with upload refs) | Single (complaint) |
| GET | `/api/v1/complaints` | 🔒 Auth | — | Collection (scoped) |
| GET | `/api/v1/complaints/{id}` | 🔒 Auth | — | Single (detail) |
| GET | `/api/v1/complaints/{id}/timeline` | 🔒 Auth | — | Collection (follow-ups) |

### 👤 Profile (3 endpoints)

| Method | Endpoint | Auth | Request | Response |
|--------|----------|------|---------|----------|
| GET | `/api/v1/profile` | 🔒 Auth | — | Single (user) |
| PUT | `/api/v1/profile` | 🔒 Auth | JSON | Single (updated user) |
| PUT | `/api/v1/profile/password` | 🔒 Auth | JSON | Status |

---

## Architectural Reference: pdam-billing-laravel-improvement

Patterns extracted from existing billing project for reuse:

| Aspect | Billing Project Pattern | Workorder Adaptation |
|--------|------------------------|---------------------|
| JWT Auth | `PHPOpenSourceSaver/JWTAuth`, TTL 60min | **Sanctum SPA Auth** (cookie-based, more secure for PWA) |
| Billing API | HMAC-SHA256 hardcoded keys | Same HMAC, but **keys in .env/config** |
| Data hiding | `makeHidden()` / `unset()` | **API Resources + NIK masking** |
| Versioning | `/api/v2/` separate file | `/api/v1/` separate file ✅ |
| Rate limiting | 120/100 per min | Same ✅ |
| Controllers | Invokable single-action | Same pattern ✅ |

---

## Session Summary and Insights

### Key Achievements

- **60+ design decisions** covering authentication, API architecture, data models, 4 API domains, and infrastructure
- **27 API endpoints** fully mapped with auth levels, request/response formats
- **14 architectural blind spots** discovered and resolved through Morphological Analysis
- **4 stakeholder perspectives** validated through Role Playing
- **Zero-conflict integration** strategy — existing Filament admin system completely untouched
- **Existing code reuse** identified: `CustomerLookupService`, `Complaint::generateNoPengaduan()`, `CustomerRegistrationHelper::generateNoSurat()`

### Critical Design Decisions

1. **Sanctum SPA Auth over JWT tokens** — More secure for same-domain PWA (HttpOnly cookies vs localStorage)
2. **2-step billing verification** — Better UX and security than single-step
3. **Separate upload endpoint** — Reliability for PWA with unstable connections
4. **Same `users` table with role** — Simplicity over separate customer table
5. **`customer_numbers` pivot** — Flexible many-to-many for multi-nomor sambungan
6. **`spatie/laravel-permission` customer-only** — Permission system without touching admin

### Safety Guardrails

- ⚠️ **Never edit existing migrations** — always create new ones
- ⚠️ **`user_id` nullable** on existing tables — backward compatible
- ⚠️ **Admin Filament access unchanged** — `canAccessPanel()` guard
- ⚠️ **Billing API keys in config/env** — not hardcoded like billing project
- ⚠️ **Server-side file validation** — prevent malicious uploads

### Session Reflections

_This brainstorming session successfully transformed a broad "build mobile API" request into a comprehensive, actionable architecture blueprint. The combination of Question Storming (establishing foundations), Morphological Analysis (systematic completeness), and Role Playing (real-world validation) ensured no critical aspect was overlooked. The "prepare-first, implement-later" philosophy enables incremental delivery while maintaining architectural integrity._
