# Story 1.2: Customer Registration

Status: done

## Story

As a customer,
I want to create an account with my name, email, and password,
So that I can access mobile services.

## Acceptance Criteria

1. **Given** valid name, email, and password **When** POST `/api/v1/auth/register` **Then** account is created with `customer` role, 201 response with user data in API Resource format
2. **Given** an email that already exists **When** POST `/api/v1/auth/register` **Then** 422 response with validation error on email field
3. **Given** a password less than 8 characters **When** POST `/api/v1/auth/register` **Then** 422 response with validation error on password field
4. **Given** successful registration **When** checking session state **Then** user is automatically logged in with session cookie set **And** user has `customer` role assigned via spatie/permission **And** `canAccessPanel()` returns false for customer role users

## Tasks / Subtasks

- [x] Task 1: Create RegisterRequest Form Request (AC: #1, #2, #3)
  - [x] Create `app/Http/Requests/Api/V1/Auth/RegisterRequest.php`
  - [x] Rules: `name` required|string|max:255, `email` required|string|email|max:255|unique:users, `password` required|string|min:8|confirmed
  - [x] Use `authorize()` returning `true` (guest endpoint)
- [x] Task 2: Create UserResource API Resource (AC: #1)
  - [x] Create `app/Http/Resources/Api/V1/Auth/UserResource.php`
  - [x] Fields: `id`, `name`, `email`, `created_at` (ISO 8601)
  - [x] Do NOT expose: `password`, `remember_token`, `role`, `email_verified_at`
- [x] Task 3: Create AuthController with register method (AC: #1, #4)
  - [x] Create `app/Http/Controllers/Api/V1/AuthController.php`
  - [x] Method `register(RegisterRequest $request): JsonResponse`
  - [x] Use `$request->validated()` — NEVER `$request->all()`
  - [x] Create user via `User::create()` — password auto-hashed via model cast
  - [x] Assign customer role: `$user->assignRole('customer')`
  - [x] Auto-login: `Auth::login($user)`
  - [x] Return 201 with `new UserResource($user)` wrapped in `data` key
- [x] Task 4: Wire up route in api_v1.php (AC: #1)
  - [x] Uncomment/replace the register placeholder in guest route group
  - [x] `Route::post('/auth/register', [AuthController::class, 'register'])->name('api.v1.auth.register')`
- [x] Task 5: Write feature tests (AC: #1, #2, #3, #4)
  - [x] Create `tests/Feature/Api/V1/Auth/RegisterTest.php`
  - [x] Test: successful registration returns 201 with user data
  - [x] Test: response contains `data.id`, `data.name`, `data.email`, `data.created_at`
  - [x] Test: user has `customer` role after registration
  - [x] Test: user is authenticated (session) after registration
  - [x] Test: `canAccessPanel()` returns false for registered customer
  - [x] Test: duplicate email returns 422 with `errors.email`
  - [x] Test: short password (<8 chars) returns 422 with `errors.password`
  - [x] Test: missing required fields return 422
  - [x] Test: password_confirmation mismatch returns 422
- [x] Task 6: Verify & lint
  - [x] Run `php artisan route:list --path=api/v1/auth` — verify register route appears
  - [x] Run `./vendor/bin/pint`
  - [x] Run `php artisan test` — all new tests pass, no regressions

### Review Findings
- [x] [Review][Decision→Patch] Add auth guard to registration — return 403 if already authenticated [AuthController.php:20-22]
- [x] [Review][Patch] Wrap User::create + assignRole in DB::transaction — prevents orphaned accounts [AuthController.php:24-35]
- [x] [Review][Patch] Use Role::findOrCreate defensively in controller — prevents 500 if seeder not run [AuthController.php:33]
- [x] [Review][Patch] Catch UniqueConstraintViolationException — return 422 instead of 500 on race condition [AuthController.php:27-30]
- [x] [Review][Defer] RefreshDatabaseCompat hardcodes migration name — deferred, pre-existing workaround

## Dev Notes

### Critical Existing Code Context

**`app/Models/User.php` current state:**
- Traits: `HasFactory`, `HasRoles`, `Notifiable`
- Implements: `FilamentUser`
- `$fillable`: `['name', 'email', 'password']` — sufficient for registration (name, email, password)
- `$hidden`: `['password', 'remember_token']`
- Casts: `email_verified_at → datetime`, `password → hashed` — password is auto-hashed, do NOT hash manually
- `canAccessPanel()`: returns `!$this->hasRole('customer')` — already implemented in Story 1.1
- No relationships defined yet

**`UserRoleEnum` (`app/UserRoleEnum.php`):** Has `ADMIN = 'admin'` and `USER = 'user'`. The `customer` role is managed via spatie's `roles` table — NOT this enum. Do NOT add 'customer' to UserRoleEnum.

**`routes/api_v1.php` current state:**
- Guest group: `Route::middleware('throttle:guest')->group(...)` with commented placeholders for register/login
- Auth group: `Route::middleware(['auth:sanctum', 'customer', 'throttle:api'])->group(...)` with commented placeholders
- Lines 19-20 have the register/login placeholders to uncomment/replace

**`bootstrap/app.php`:** API routes registered via `then:` callback with `middleware('web')`. Rate limiters in `->booted()`. Middleware alias `'customer' => EnsureCustomerRole::class`.

**`config/sanctum.php`:** Guard is `['web']`, stateful domains from env.

### Sanctum SPA Registration Flow

The PWA registration flow:
1. PWA calls `GET /sanctum/csrf-cookie` → XSRF-TOKEN cookie set
2. PWA sends `POST /api/v1/auth/register` with JSON body + XSRF-TOKEN header
3. Server validates, creates user, assigns role, starts session
4. Response: 201 + session cookie automatically set by Sanctum/Laravel

**CRITICAL:** Routes use `middleware('web')` — this is correct for Sanctum SPA cookie auth. Do NOT change to `middleware('api')`.

### Controller Pattern

Architecture mandates a single `AuthController` for all auth actions (register, login, logout). Story 1.2 only implements `register()`. Login/logout methods will be added in Story 1.3.

```php
// Pattern from architecture doc
public function register(RegisterRequest $request): JsonResponse
{
    $user = User::create($request->validated());
    $user->assignRole('customer');
    Auth::login($user);
    return (new UserResource($user))
        ->response()
        ->setStatusCode(201);
}
```

### Response Format

Success (201):
```json
{
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "created_at": "2026-04-12T10:30:00.000000Z"
  }
}
```

Validation Error (422) — Laravel auto-generated from Form Request:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email has already been taken."],
    "password": ["The password field must be at least 8 characters."]
  }
}
```

### File Organization (Architecture-mandated)

```
app/Http/Controllers/Api/V1/AuthController.php       ← NEW (register method only)
app/Http/Requests/Api/V1/Auth/RegisterRequest.php     ← NEW
app/Http/Resources/Api/V1/Auth/UserResource.php       ← NEW
tests/Feature/Api/V1/Auth/RegisterTest.php            ← NEW
routes/api_v1.php                                     ← MODIFY (uncomment register route)
```

### Testing Approach

Use `RefreshDatabase` trait. Each test should:
1. Call `GET /sanctum/csrf-cookie` first (or use `$this->postJson()` which handles CSRF in test env)
2. POST to `/api/v1/auth/register` with JSON body
3. Assert response status and structure
4. Assert database state (user created, role assigned)
5. Assert session state (user is authenticated)

Test file location: `tests/Feature/Api/V1/Auth/RegisterTest.php`

**Existing test pattern** (from Story 1.1): Tests use `$this->postJson()`, `$this->assertAuthenticated()`, and direct model assertions.

### Email Verification — DEFERRED

Do NOT implement email verification. It is explicitly deferred to Post-MVP:
- Do NOT add `MustVerifyEmail` interface to User model
- Do NOT create verification routes or controllers
- The `email_verified_at` column exists but stays NULL for now

### Previous Story Learnings (Story 1.1)

- Password hashing: User model has `'password' => 'hashed'` cast — do NOT use `Hash::make()` manually
- Spatie role: `CustomerRoleSeeder` already seeds the `customer` role — it exists in DB
- Tests use SQLite `:memory:` — avoid MySQL-specific syntax
- 6 pre-existing test failures from `complaint_follow_ups` migration (MySQL `CHANGE` syntax) — ignore these
- Pint must pass: run `./vendor/bin/pint` before final verification
- Rate limiter `guest` (100/min) already applies to the register route via `throttle:guest` middleware group

### Anti-Patterns (FORBIDDEN)

- ❌ `Hash::make($password)` — model cast handles hashing
- ❌ `$request->all()` — always use `$request->validated()`
- ❌ Inline validation in controller — use Form Request class
- ❌ `HasApiTokens` trait — this is SPA cookie auth
- ❌ `Auth::attempt()` for registration — use `Auth::login($user)` after create
- ❌ Adding 'customer' to `UserRoleEnum` — spatie manages this role separately
- ❌ `MustVerifyEmail` — deferred to post-MVP
- ❌ Business logic in controller — keep controller thin, though registration is simple enough for direct model operations
- ❌ `env()` direct — use `config()` if needed
- ❌ Returning `password` or `remember_token` in API Resource

### Project Structure Notes

- All new files follow architecture-mandated paths exactly
- No conflicts with existing Filament admin code
- Controller namespace: `App\Http\Controllers\Api\V1`
- Request namespace: `App\Http\Requests\Api\V1\Auth`
- Resource namespace: `App\Http\Resources\Api\V1\Auth`

### References

- [Source: _bmad-output/planning-artifacts/epics.md#Story 1.2 — acceptance criteria, BDD scenarios]
- [Source: _bmad-output/planning-artifacts/architecture.md#Auth Domain — controller pattern, file organization]
- [Source: _bmad-output/planning-artifacts/architecture.md#API Design Rules — response format, error codes]
- [Source: _bmad-output/planning-artifacts/architecture.md#Enforcement Guidelines — anti-patterns, validation rules]
- [Source: _bmad-output/planning-artifacts/prd.md#FR1,FR7 — registration requirements]
- [Source: _bmad-output/planning-artifacts/prd.md#Auth Domain endpoints — POST /api/v1/register]
- [Source: _bmad-output/planning-artifacts/prd.md#NFR6,7,10,13,22 — security, session, CSRF]
- [Source: _bmad-output/implementation-artifacts/1-1-project-setup-api-infrastructure.md — learnings, review findings]

## Dev Agent Record

### Agent Model Used
Claude Opus 4.6 (claude-opus-4.6)

### Debug Log References
- Created RefreshDatabaseCompat trait to handle pre-existing MySQL CHANGE syntax migration failure on SQLite :memory: test environment
- Pre-existing 4 test failures unchanged (2 MySQL CHANGE syntax in CustomerRegistrationProcessTest, 2 missing MaterialAndService factory)

### Completion Notes List
- Created AuthController with register method — thin controller, delegates to User::create() + spatie role assignment
- RegisterRequest validates name (required, string, max:255), email (required, unique), password (required, min:8, confirmed)
- UserResource exposes only id, name, email, created_at — no sensitive fields
- Route wired at POST /api/v1/auth/register with throttle:guest middleware
- Auto-login via Auth::login() sets session cookie (Sanctum SPA)
- 9 feature tests all passing (30 assertions)
- Created RefreshDatabaseCompat trait — catches SQLite CHANGE syntax error, marks migration as run, continues remaining migrations
- Pint passes (331 files, 0 issues)

### Change Log
- Story created: 2026-04-12 — ready-for-dev
- Implementation complete: 2026-04-12 — all 6 tasks done, 9 tests passing, status → review

### File List
- app/Http/Controllers/Api/V1/AuthController.php (new — register method)
- app/Http/Requests/Api/V1/Auth/RegisterRequest.php (new — validation rules)
- app/Http/Resources/Api/V1/Auth/UserResource.php (new — API resource)
- routes/api_v1.php (modified — uncommented register route, added use statement)
- tests/Feature/Api/V1/Auth/RegisterTest.php (new — 9 tests)
- tests/Concerns/RefreshDatabaseCompat.php (new — SQLite migration compat trait)
