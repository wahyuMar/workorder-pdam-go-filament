# Deferred Work

## Deferred from: code review of story-1.1 (2026-04-12)

- Add `user_id` to `Complaint` model `$fillable` — migration adds nullable `user_id` FK but model's `$fillable` not updated. Will be needed when Story 5.1 (Complaints API) implements the endpoint that sets user_id.
- Add `user_id` and `source` to `CustomerRegistration` model `$fillable` — migrations add nullable columns but model's `$fillable` not updated. Will be needed when Story 4.1 (SR Registration API) implements the endpoint.
- Add `user()` BelongsTo relationship on `Complaint` and `CustomerRegistration` models — needed for eager loading in API responses. Will be added in respective API stories (4.1 and 5.1).
- Document dual role system (legacy `role` column + Spatie `HasRoles`) in architecture notes — the two systems are complementary by design but should be explicitly documented to prevent future confusion.

## Deferred from: code review of story-1.2 (2026-04-12)

- RefreshDatabaseCompat hardcodes migration name `2026_02_25_000001_update_complaint_follow_ups_work_order_enum` — fragile if a second MySQL-only migration is added. Currently only one such migration exists, so acceptable. Revisit if more MySQL-specific migrations appear.

## Deferred from: code review of story-1.3 (2026-04-12)

- Per-email rate limiting for distributed brute-force attacks — Login rate limiter keys only on IP (5/min/IP). A botnet from many IPs can target one email without hitting the per-IP limit. Not in spec (NFR10 says "5/min per IP"). Consider adding `Limit::perMinute(10)->by($email)` as a second limiter.
- SESSION_SECURE_COOKIE defaults to null in config/session.php — Pre-existing config, not introduced by Story 1.3. Session cookies may be sent over plain HTTP if `SESSION_SECURE_COOKIE` env var is not set in production. Should be set to `true` in production `.env`.

## Deferred from: code review of story-1.4 (2026-04-12)

- email_verified_at not nulled on email change — When user changes email via PUT /profile, the email_verified_at timestamp persists from the original email. Currently harmless (MustVerifyEmail not implemented), but creates a latent verification bypass if email verification is later enabled.
- No password re-verification on profile update — PUT /profile allows changing email without confirming current password. A stolen session could change the email. Not in current spec; consider adding password confirmation for email changes in a future hardening pass.
- Invalidate all other sessions on password change — AuthenticateSession middleware is incompatible with Sanctum RequestGuard (viaRemember not available). Current fix: regenerate current session only. Full multi-device invalidation requires switching to database sessions + custom password-hash-check middleware compatible with Sanctum SPA auth.

## Deferred from: code review of story-2.1 (2026-04-12)

- F6: Hardcoded API key default in config/services.php — The default value for BILLING_API_KEY is a real-looking key hardcoded in source. Pre-existing (diff only re-indented). Default should be '' or null, with real key in .env only.
- F7: Inconsistent timeout config across CustomerLookupService methods — fetchByNoSambungan now uses configurable timeout via Config::get(), but fetchUnits, fetchDesaByUnit, fetchRtRwByDesa, fetchWilayahByUnit, fetchJalanByWilayah all hardcode Http::timeout(10). Should migrate all methods to use the config value for consistency.

## Deferred from: code review of story-2.2 (2026-04-12)

- F6: NIK-specific rate limit on confirm endpoint — confirm endpoint shares generic throttle:api (120 req/min). A dedicated stricter throttle (e.g., 5/min per user per no_sambungan) would prevent NIK brute-force attempts. Architecture-level throttle change needed.
- F7: Billing API 4xx responses silently mapped to 404 — CustomerLookupService returns ['data' => null] for any non-5xx billing response (including 403 auth failure, 429 rate limit). Controller maps this to 404 "not found". Pre-existing service behavior; should add distinct handling for client errors from billing upstream.

## Deferred from: code review of story-2.3 (2026-04-12)

- Missing `verified_at` guard on index/billing/destroy queries — currently safe because only `confirm()` creates records with `verified_at`. Add defensive `whereNotNull('verified_at')` or `scopeVerified()` if future code introduces unverified creation paths.

## Deferred from: code review of story-3.1 (2026-04-12)

- Orphan files on user cascade delete — `uploads` FK uses `cascadeOnDelete`, which removes DB rows but leaves physical files on disk. Needs a model observer (or scheduled cleanup job) to delete files. PRD defers periodic cleanup to Growth phase.

## Deferred from: code review of story-4-1 (2026-04-12)

- `generateNoSurat()` race condition: `CustomerRegistrationHelper::generateNoSurat()` uses `max('id')` without pessimistic locking. Two concurrent requests can produce duplicate `no_surat` values. No unique DB constraint on the column. Affects both Filament and API. Fix: add DB unique index + retry or pessimistic lock.
- Location FK hierarchy not validated: API accepts mismatched province/regency/district/village combinations (e.g., Aceh province with West Java regency). Filament uses cascading UI dropdowns but DB schema does not enforce. Fix: add conditional `exists` rules with `where` clause scoping parent FK.

## Deferred from: code review of story-4-2 (2026-04-12)

- maskNik() duplicated across 3 classes (RegistrationResource, RegistrationListResource, CustomerNumberController). Extract to shared trait or helper when next touched.
- Upload paths returned as raw storage strings in API responses. Pre-existing pattern from Story 4.1 — Filament also stores raw paths. Consider wrapping with `Storage::disk('public')->url()` when URL generation is needed by mobile client.


## Deferred from: code review of story-5-1 (2026-04-12)

- `no_pengaduan` is in Complaint `$fillable`. The boot() guard only generates if empty — any code path mass-assigning a `no_pengaduan` value bypasses auto-generation. Not exploitable via API (field not in StoreRequest rules) but Filament admin forms could potentially override.
- `complaintType()` and `followUps()` relations in Complaint model lack return type declarations, inconsistent with the new `user()` method which has `BelongsTo` return type. Minor code consistency issue.

## Deferred from: code review of story-5-2 (2026-04-12)

- whenLoaded null crash in ComplaintResource.php:18: When complaintType is deleted (FK onDelete set null), the whenLoaded closure accesses complaintType->id on null, causing HTTP 500. Needs null guard.

## Deferred from: code review of story-6-1 (2026-04-12)

- Unbounded cache key proliferation: MasterDataController cascading endpoints (regencies, districts, villages) cache results keyed by parent ID. An authenticated user can enumerate arbitrary IDs, creating many cache entries in the database store. Mitigated by throttle:api (120/min) and auth requirement, but a bounded cache pattern (e.g., only cache non-empty results) would harden this.

## Deferred from: code review of story-6-2 (2026-04-12)

- Migration does not backfill existing NULL rows: The add_source_to_customer_registrations_table migration (Story 4.1) adds source as nullable with default manual. The default only applies to new inserts. Pre-existing rows get NULL. Should add a backfill UPDATE in a new migration.
