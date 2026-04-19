# Story 1.1: Project Setup & API Infrastructure

Status: done

## Story

As a developer,
I want the API foundation installed and configured,
so that all subsequent stories have the infrastructure they need.

## Acceptance Criteria

1. **Given** a fresh project state **When** packages are installed **Then** `laravel/sanctum`, `spatie/laravel-permission`, and `knuckleswtf/scribe` are available in `composer.json`
2. **Given** packages are installed **When** Sanctum config is published **Then** `config/sanctum.php` exists with `stateful` domains configured
3. **Given** Sanctum is configured **When** `routes/api_v1.php` is created **Then** it is registered in `bootstrap/app.php` with `/api/v1` prefix
4. **Given** routes exist **When** `EnsureCustomerRole` middleware is created **Then** it checks `auth:sanctum` and customer role via spatie/permission
5. **Given** permission package installed **When** `customer` role is seeded **Then** role exists in permissions table
6. **Given** migrations run **When** checking schema **Then** `customer_numbers` table created, `uploads` table created, `user_id` nullable column added to `customer_registrations` and `complaints`, `source` column added to `customer_registrations`
7. **Given** config changes **When** logging config updated **Then** `api` channel exists in `config/logging.php` **And** rate limiters (120 auth/100 guest/5 login per IP) are registered in `bootstrap/app.php`

## Tasks / Subtasks

- [x] Task 1: Install packages (AC: #1)
  - [x] `composer require laravel/sanctum spatie/laravel-permission knuckleswtf/scribe`
  - [x] `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`
  - [x] `php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"`
  - [x] Run `php artisan migrate` for Sanctum + Spatie tables
- [x] Task 2: Configure Sanctum for SPA auth (AC: #2)
  - [x] Edit `config/sanctum.php`: set `stateful` domains from env
  - [x] Add to `.env`: `SANCTUM_STATEFUL_DOMAINS`, `SESSION_DOMAIN`, `SESSION_LIFETIME=43200`
  - [x] Ensure Sanctum middleware is applied to API routes (via `bootstrap/app.php`)
- [x] Task 3: Create route file and register (AC: #3)
  - [x] Create `routes/api_v1.php` with placeholder route groups for all 7 domains
  - [x] Register in `bootstrap/app.php` via `withRouting()` — use `then:` callback with `Route::prefix('api/v1')->middleware('web')->group()`
  - [x] Add commented placeholder routes for password reset (deferred post-MVP)
- [x] Task 4: Create EnsureCustomerRole middleware (AC: #4)
  - [x] Create `app/Http/Middleware/EnsureCustomerRole.php`
  - [x] Check user has `customer` role via spatie `hasRole('customer')`
  - [x] Return 403 JSON response if role check fails
  - [x] Register as named middleware alias in `bootstrap/app.php`
- [x] Task 5: Create database migrations (AC: #6)
  - [x] Create `create_customer_numbers_table` migration: `id`, `user_id` (FK, indexed), `no_sambungan` (string, indexed), `verified_at` (timestamp, nullable), `timestamps`
  - [x] Create `create_uploads_table` migration: `id`, `user_id` (FK, indexed), `original_name` (string), `stored_path` (string), `mime_type` (string), `size` (integer), `timestamps`
  - [x] Create `add_user_id_to_customer_registrations_table` migration: `user_id` nullable FK
  - [x] Create `add_user_id_and_source_to_complaints_table` migration: `user_id` nullable FK. Note: `sumber` column already exists on complaints — do NOT add duplicate `source` column
  - [x] Create `add_source_to_customer_registrations_table` migration: `source` string nullable, default `'manual'`
  - [x] Run `php artisan migrate`
- [x] Task 6: Update User model for API support (AC: #4, #5)
  - [x] Add `use Spatie\Permission\Traits\HasRoles;` trait to `app/Models/User.php`
  - [x] Add `canAccessPanel()` method that returns `false` for customer role
  - [x] Do NOT add `HasApiTokens` — we use cookie-based SPA auth, not token auth
  - [x] Do NOT modify `$fillable` for role — spatie manages roles in separate tables
- [x] Task 7: Create seeder for customer role (AC: #5)
  - [x] Create seeder or add to existing: `Role::findOrCreate('customer', 'web')`
  - [x] Run seeder
- [x] Task 8: Configure logging and rate limiters (AC: #7)
  - [x] Add `api` channel to `config/logging.php` — driver: `daily`, path: `storage/logs/api.log`
  - [x] Register rate limiters in `bootstrap/app.php`: `api` (120/min per user), `guest` (100/min per IP), `login` (5/min per IP)
- [x] Task 9: Verify and lint
  - [x] Run `php artisan migrate:fresh --seed` (verify all migrations work)
  - [x] Run `php artisan route:list` (verify api_v1 routes appear)
  - [x] Run `./vendor/bin/pint` (ensure PSR-12 compliance)
  - [x] Run `php artisan test` (ensure no regressions)

### Review Findings

- [x] [Review][Decision] Move `knuckleswtf/scribe` from `require` to `require-dev` — ✅ Fixed
- [x] [Review][Patch] Remove redundant `$table->index('user_id')` — ✅ Fixed
- [x] [Review][Patch] Update `.env.example` with Sanctum/session config — ✅ Fixed
- [x] [Review][Defer] Add `user_id` to `Complaint` model `$fillable` — deferred, will be done in Story 5.1 (Complaints API)
- [x] [Review][Defer] Add `user_id`/`source` to `CustomerRegistration` model `$fillable` — deferred, will be done in Story 4.1 (SR Registration API)
- [x] [Review][Defer] Add `user()` BelongsTo relationship on Complaint/CustomerRegistration — deferred, will be added in respective API stories
- [x] [Review][Defer] Document dual role system (legacy `role` column + Spatie roles) — deferred, add to architecture notes in future

## Dev Notes

### Critical Existing Code Context

**`app/Models/User.php` current state:**
- Traits: `HasFactory`, `Notifiable` — NO Sanctum traits, NO spatie traits
- Fillable: `['name', 'email', 'password']`
- Casts: `email_verified_at → datetime`, `password → hashed`
- No relationships defined
- An existing migration `2026_01_15_111438_add_role_column_in_user_table.php` already adds a `role` string column with `UserRoleEnum` (`admin`, `user`). The spatie/permission package uses separate `roles`/`model_has_roles` tables — these are complementary, NOT conflicting

**`app/UserRoleEnum.php`:** Has `ADMIN = 'admin'` and `USER = 'user'`. The spatie `customer` role is separate from this enum — spatie manages roles in its own tables via the `HasRoles` trait

**`app/Models/Complaint.php`:** Already has `sumber` (source) field in `$fillable`. This is the existing source discriminator. Do NOT create a new `source` column on complaints — either reuse `sumber` or verify with the team. The migration in AC #6 says "add source to complaints" but `sumber` already exists. **Decision: Use `sumber` as-is for complaints, only add `source` to `customer_registrations`**

**`app/Models/CustomerRegistration.php`:** 43 fillable fields. No `user_id`, no `source`. Both need new migration columns

**`bootstrap/app.php` current state:**
```php
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', commands: __DIR__.'/../routes/console.php', health: '/up')
    ->withMiddleware(function (Middleware $middleware): void { })
    ->withExceptions(function (Exceptions $exceptions): void { })->create();
```
No middleware, no rate limiters, no API routes registered

**`bootstrap/providers.php`:** `AppServiceProvider` + `AdminPanelProvider` — empty AppServiceProvider

**`config/sanctum.php`:** Does NOT exist. Package not installed

**`routes/`:** Only `web.php` (welcome view) and `console.php` (inspire command). No API routes

### Route Registration Pattern

Laravel 12 Streamlined Structure does NOT use `Kernel.php`. Route registration for api_v1 in `bootstrap/app.php`:

```php
->withRouting(
    web: __DIR__.'/../routes/web.php',
    commands: __DIR__.'/../routes/console.php',
    health: '/up',
    then: function () {
        Route::prefix('api/v1')
            ->middleware('web')
            ->group(base_path('routes/api_v1.php'));
    },
)
```

**Why `middleware('web')` not `middleware('api')`:** Sanctum SPA auth requires `web` middleware group for session/cookie handling. This is critical — using `api` middleware will break cookie auth.

### Rate Limiter Registration

In `bootstrap/app.php` `withMiddleware` callback or in `AppServiceProvider::boot()`:

```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(120)->by($request->user()?->id ?: $request->ip());
});
RateLimiter::for('guest', function (Request $request) {
    return Limit::perMinute(100)->by($request->ip());
});
RateLimiter::for('login', function (Request $request) {
    return Limit::perMinute(5)->by($request->ip());
});
```

### Migration Naming Convention

Existing pattern: `YYYY_MM_DD_HHMMSS_descriptive_snake_case.php`
Use `php artisan make:migration` to auto-generate timestamps.

### Sanctum SPA vs Token Auth

**CRITICAL:** This project uses **Sanctum SPA authentication (cookie-based)**, NOT token-based auth. Do NOT:
- Add `HasApiTokens` trait to User model
- Create `personal_access_tokens` table
- Issue tokens via `createToken()`
- Use `Authorization: Bearer` headers

Instead: Sanctum SPA uses session cookies via `web` middleware. The PWA calls `/sanctum/csrf-cookie` first, then uses standard session cookies for auth.

### EnsureCustomerRole Middleware Pattern

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCustomerRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->hasRole('customer')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }
        return $next($request);
    }
}
```

### canAccessPanel on User Model

```php
public function canAccessPanel(\Filament\Panel $panel): bool
{
    return !$this->hasRole('customer');
}
```

This ensures customers are blocked from Filament admin, while existing admin users remain unaffected.

### Anti-Patterns (FORBIDDEN)

- ❌ Raw `DB::` queries — use Eloquent exclusively
- ❌ `env()` direct — always use `config()`
- ❌ Editing existing migration files — create NEW migration files only
- ❌ Adding `HasApiTokens` — this is SPA cookie auth, not token auth
- ❌ Using `middleware('api')` for Sanctum SPA routes — must use `middleware('web')`
- ❌ Hardcoding domain values — use `SANCTUM_STATEFUL_DOMAINS` env variable
- ❌ NIK in logs — never log NIK values

### Project Structure Notes

All new files align with architecture document:
- `app/Http/Middleware/EnsureCustomerRole.php` — new
- `routes/api_v1.php` — new
- `config/logging.php` — modify (add `api` channel)
- `config/sanctum.php` — auto-published by Sanctum
- `database/migrations/` — 5 new migration files (not 6 — complaints already has `sumber`)
- `database/seeders/` — customer role seeder

Existing files to modify (additive only):
- `app/Models/User.php` — add `HasRoles` trait + `canAccessPanel()`
- `bootstrap/app.php` — add routes, middleware, rate limiters

### References

- [Source: _bmad-output/planning-artifacts/architecture.md#Starter Template Evaluation — packages list]
- [Source: _bmad-output/planning-artifacts/architecture.md#Core Architectural Decisions — auth, middleware, rate limiting]
- [Source: _bmad-output/planning-artifacts/architecture.md#Implementation Patterns — naming, structure, anti-patterns]
- [Source: _bmad-output/planning-artifacts/architecture.md#Project Structure & Boundaries — file tree]
- [Source: _bmad-output/planning-artifacts/prd.md#API Backend Specific Requirements — 27 endpoints, auth model]
- [Source: _bmad-output/planning-artifacts/prd.md#Non-Functional Requirements — NFR6,7,9,10,13,22,24]
- [Source: _bmad-output/planning-artifacts/epics.md#Story 1.1 — acceptance criteria]

## Dev Agent Record

### Agent Model Used
Claude Opus 4.6 (claude-opus-4.6)

### Debug Log References
- Removed published `personal_access_tokens` migration — not needed for SPA cookie auth
- Pre-existing test failures (6) in MySQL CHANGE syntax on SQLite — unrelated to this story
- Rate limiter registration uses `->booted()` callback in bootstrap/app.php for proper timing

### Completion Notes List
- Installed sanctum v4.3.1, spatie/laravel-permission v7.3.0, scribe v5.9.0
- Sanctum configured for SPA cookie-based auth (web middleware, NOT api middleware)
- 5 new migrations created and run successfully (customer_numbers, uploads, user_id on 2 tables, source on registrations)
- EnsureCustomerRole middleware created with spatie hasRole check, registered as 'customer' alias
- User model updated: HasRoles trait + FilamentUser interface + canAccessPanel() blocks customers
- CustomerRoleSeeder created and integrated into DatabaseSeeder
- 4 rate limiters configured: api (120/min), guest (100/min), login (5/min), upload (20/min)
- API log channel added to config/logging.php (daily driver)
- 13 new tests all passing (3 middleware unit + 10 infrastructure feature)
- Pint passes with 0 issues

### Change Log
- Story created: 2026-04-11 — ready-for-dev
- Implementation complete: 2026-04-11 — all 9 tasks done, 13 tests passing, status → review

### File List
- composer.json (modified — added sanctum, spatie/permission, scribe)
- composer.lock (modified — updated dependencies)
- .env (modified — added SANCTUM_STATEFUL_DOMAINS, SESSION_DOMAIN, SESSION_LIFETIME=43200)
- bootstrap/app.php (modified — added API routes, middleware alias, rate limiters)
- app/Models/User.php (modified — added HasRoles trait, FilamentUser interface, canAccessPanel)
- app/Http/Middleware/EnsureCustomerRole.php (new)
- routes/api_v1.php (new — placeholder route groups for all 7 API domains)
- config/sanctum.php (new — published by Sanctum)
- config/permission.php (new — published by spatie/permission)
- config/logging.php (modified — added 'api' daily channel)
- database/migrations/2026_04_11_175130_create_permission_tables.php (new — spatie published)
- database/migrations/2026_04_11_175325_create_customer_numbers_table.php (new)
- database/migrations/2026_04_11_175325_create_uploads_table.php (new)
- database/migrations/2026_04_11_175326_add_user_id_to_customer_registrations_table.php (new)
- database/migrations/2026_04_11_175326_add_user_id_to_complaints_table.php (new)
- database/migrations/2026_04_11_175327_add_source_to_customer_registrations_table.php (new)
- database/seeders/CustomerRoleSeeder.php (new)
- database/seeders/DatabaseSeeder.php (modified — calls CustomerRoleSeeder)
- tests/Unit/EnsureCustomerRoleMiddlewareTest.php (new — 3 tests)
- tests/Feature/Api/V1/ApiInfrastructureTest.php (new — 10 tests)
