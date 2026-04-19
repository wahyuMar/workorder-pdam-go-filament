# Story 1.3: Customer Login & Logout

Status: done

## Story

As a customer,
I want to log in and log out of my account,
So that I can securely access and leave the system.

## Acceptance Criteria

1. **Given** valid email and password credentials **When** POST `/api/v1/auth/login` **Then** 200 response with user data, session cookie set
2. **Given** invalid credentials **When** POST `/api/v1/auth/login` **Then** 401 response with error message
3. **Given** 5 failed login attempts within 1 minute from same IP **When** POST `/api/v1/auth/login` again **Then** 429 Too Many Requests response
4. **Given** an authenticated session **When** POST `/api/v1/auth/logout` **Then** 200 response, session destroyed, cookie invalidated
5. **Given** no authentication cookie **When** accessing any protected endpoint **Then** 401 Unauthorized response
6. **Given** customer wants to make state-changing request **When** GET `/sanctum/csrf-cookie` is called first **Then** XSRF-TOKEN cookie is set and valid for subsequent requests

## Tasks / Subtasks

- [x] Task 1: Create LoginRequest Form Request (AC: #1, #2)
  - [x] Create `app/Http/Requests/Api/V1/Auth/LoginRequest.php`
  - [x] Rules: `email` required|string|email, `password` required|string
  - [x] `authorize()` returns `true` (guest endpoint)
- [x] Task 2: Add login() method to AuthController (AC: #1, #2)
  - [x] Add `login(LoginRequest $request): JsonResponse` method
  - [x] Guard: return 403 if `Auth::check()` (already authenticated — same pattern as register)
  - [x] Use `Auth::attempt($request->validated())` — returns bool, sets session if true
  - [x] On failure: return 401 `{"message": "The provided credentials are incorrect."}`
  - [x] On success: `$request->session()->regenerate()` then return 200 with `UserResource`
- [x] Task 3: Add logout() method to AuthController (AC: #4)
  - [x] Add `logout(Request $request): JsonResponse` method
  - [x] Call `Auth::guard('web')->logout()`
  - [x] Call `$request->session()->invalidate()`
  - [x] Call `$request->session()->regenerateToken()`
  - [x] Return 200 `{"message": "Logged out successfully."}`
- [x] Task 4: Wire login route with throttle:login (AC: #1, #3)
  - [x] Uncomment login placeholder in guest group (line 21 of api_v1.php)
  - [x] `Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('api.v1.auth.login')`
  - [x] The `throttle:login` rate limiter (5/min/IP) is already defined in `bootstrap/app.php:40-41`
  - [x] Route inherits `throttle:guest` from group AND gets `throttle:login` — the stricter 5/min applies
- [x] Task 5: Wire logout route in authenticated group (AC: #4)
  - [x] Uncomment logout placeholder in `['auth:sanctum', 'customer']` group (line 32 of api_v1.php)
  - [x] `Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout')`
- [x] Task 6: Write LoginTest feature tests (AC: #1, #2, #3)
  - [x] Create `tests/Feature/Api/V1/Auth/LoginTest.php`
  - [x] Test: successful login returns 200 with user data in `{data: {id, name, email, created_at}}` format
  - [x] Test: response does not expose sensitive fields (password, remember_token, role, email_verified_at)
  - [x] Test: user is authenticated after login (`assertAuthenticated('web')`)
  - [x] Test: invalid password returns 401
  - [x] Test: non-existent email returns 401
  - [x] Test: missing required fields returns 422
  - [x] Test: already-authenticated user returns 403
  - [x] Test: rate limiting returns 429 after 5 failed attempts
- [x] Task 7: Write LogoutTest feature tests (AC: #4, #5, #6)
  - [x] Create `tests/Feature/Api/V1/Auth/LogoutTest.php`
  - [x] Test: successful logout returns 200 with message
  - [x] Test: user is guest after logout (`assertGuest('web')`)
  - [x] Test: unauthenticated logout returns 401
  - [x] Test: CSRF cookie endpoint returns XSRF-TOKEN (AC6 — Sanctum built-in, smoke test)
- [x] Task 8: Verify & lint
  - [x] Run `php artisan route:list --path=api/v1/auth` — verify login+logout routes appear
  - [x] Run `./vendor/bin/pint`
  - [x] Run `php artisan test --filter=LoginTest` — all pass
  - [x] Run `php artisan test --filter=LogoutTest` — all pass
  - [x] Run `php artisan test` — no regressions (expect same 4 pre-existing failures)

## Dev Notes

### Critical Existing Code Context

**`app/Http/Controllers/Api/V1/AuthController.php` current state:**
- Already has `register()` method with auth guard (`Auth::check()` → 403), DB::transaction, and Role::findOrCreate
- Already imports: `Auth`, `DB`, `User`, `UserResource`, `RegisterRequest`, `Role`, `ValidationException`, `UniqueConstraintViolationException`
- Login method adds: `LoginRequest` import, `Auth::attempt()` usage
- Logout method adds: `Illuminate\Http\Request` import

**Login method pattern:**
```php
public function login(LoginRequest $request): JsonResponse
{
    if (Auth::check()) {
        return response()->json(['message' => 'Already authenticated.'], 403);
    }

    if (! Auth::attempt($request->validated())) {
        return response()->json(['message' => 'The provided credentials are incorrect.'], 401);
    }

    $request->session()->regenerate();

    return (new UserResource(Auth::user()))
        ->response()
        ->setStatusCode(200);
}
```

**Logout method pattern:**
```php
public function logout(Request $request): JsonResponse
{
    Auth::guard('web')->logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return response()->json(['message' => 'Logged out successfully.']);
}
```

### Sanctum SPA Auth Flow

The PWA login flow:
1. PWA calls `GET /sanctum/csrf-cookie` → XSRF-TOKEN cookie set
2. PWA sends `POST /api/v1/auth/login` with JSON body + XSRF-TOKEN header
3. Server validates credentials via `Auth::attempt()`, creates session
4. Response: 200 + session cookie automatically set by Sanctum/Laravel

**CRITICAL:** Routes use `middleware('web')` — this is correct for Sanctum SPA cookie auth. Do NOT change to `middleware('api')`.

### Rate Limiting for Login

The `login` rate limiter is **already defined** in `bootstrap/app.php` (lines 40-41):
```php
RateLimiter::for('login', fn(Request $request) =>
    Limit::perMinute(5)->by($request->ip()));
```

Apply via `->middleware('throttle:login')` on the login route. This is in addition to the group-level `throttle:guest` (100/min). The stricter 5/min per IP effectively controls login attempts.

### Response Format

Login success (200):
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

Login failure — invalid credentials (401):
```json
{
  "message": "The provided credentials are incorrect."
}
```

Login failure — validation error (422) — Laravel auto-generated from Form Request:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The email field is required."],
    "password": ["The password field is required."]
  }
}
```

Login failure — rate limited (429):
```json
{
  "message": "Too Many Requests"
}
```

Logout success (200):
```json
{
  "message": "Logged out successfully."
}
```

Unauthenticated (401) — Laravel auto-generated by `auth:sanctum`:
```json
{
  "message": "Unauthenticated."
}
```

### Route Wiring Detail

```php
// Guest routes — login uses throttle:login (5/min) ON TOP of throttle:guest (100/min)
Route::middleware('throttle:guest')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
    Route::post('/auth/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('api.v1.auth.login');
});

// Authenticated customer routes — logout inside throttle:api group
Route::middleware(['auth:sanctum', 'customer'])->group(function () {
    Route::middleware('throttle:api')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
    });
});
```

### Session Regeneration

**Login:** Call `$request->session()->regenerate()` AFTER `Auth::attempt()` succeeds. This prevents session fixation attacks by generating a new session ID.

**Logout:** Call `$request->session()->invalidate()` to destroy the session, then `$request->session()->regenerateToken()` to regenerate the CSRF token. Use `Auth::guard('web')->logout()` to clear the auth state.

### Testing Approach

Use `RefreshDatabaseCompat` trait (NOT `RefreshDatabase`). Each test class needs `Role::findOrCreate('customer', 'web')` in `setUp()`.

**Creating test users:**
```php
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'password123', // auto-hashed by model cast
]);
$user->assignRole('customer');
```

**Testing rate limits:** Use a loop sending 5 failed attempts, then assert 6th returns 429. Rate limit key is by IP, so no need for different users.

**Testing authenticated state:**
- After login: `$this->assertAuthenticated('web')`
- After logout: `$this->assertGuest('web')`

**Testing logout requires auth:** Use `$this->postJson('/api/v1/auth/logout')` without `actingAs()` — expect 401.

### Previous Story Learnings (Stories 1.1 & 1.2)

- Password hashing: User model has `'password' => 'hashed'` cast — do NOT use `Hash::make()` manually
- Spatie role: `CustomerRoleSeeder` already seeds the `customer` role — use `Role::findOrCreate` in test setUp
- Tests use SQLite `:memory:` — avoid MySQL-specific syntax
- 4 pre-existing test failures (2 MySQL CHANGE syntax, 2 missing factory) — ignore, not introduced by us
- Pint must pass: run `./vendor/bin/pint` before final verification
- RefreshDatabaseCompat trait is REQUIRED for all feature tests with DB
- Auth guard pattern: return 403 if `Auth::check()` — apply to login (same as register)
- DB::transaction not needed for login/logout (no record creation)
- `new \Filament\Panel` is valid for testing `canAccessPanel()` (confirmed in 1.2 tests)

### Anti-Patterns (FORBIDDEN)

- ❌ `Hash::make($password)` — model cast handles hashing
- ❌ `$request->all()` — always use `$request->validated()`
- ❌ Inline validation in controller — use Form Request class
- ❌ `HasApiTokens` trait — this is SPA cookie auth
- ❌ `Auth::login($user)` for login — use `Auth::attempt($credentials)` which validates credentials
- ❌ Adding 'customer' to `UserRoleEnum` — spatie manages this role separately
- ❌ `MustVerifyEmail` — deferred to post-MVP
- ❌ `env()` direct — use `config()` if needed
- ❌ Returning `password` or `remember_token` in API Resource
- ❌ Checking customer role during login — role enforcement is handled by `EnsureCustomerRole` middleware on protected routes, NOT at login
- ❌ Using `Auth::guard('web')->attempt()` — just use `Auth::attempt()` since `web` is the default guard
- ❌ Token-based auth (Sanctum tokens, `createToken()`) — this is SPA cookie auth only

### Project Structure Notes

- All new files follow architecture-mandated paths exactly
- No conflicts with existing Filament admin code
- Controller namespace: `App\Http\Controllers\Api\V1`
- Request namespace: `App\Http\Requests\Api\V1\Auth`
- Test namespace: `Tests\Feature\Api\V1\Auth`

### File Organization (Architecture-mandated)

```
app/Http/Controllers/Api/V1/AuthController.php       ← MODIFY (add login + logout methods)
app/Http/Requests/Api/V1/Auth/LoginRequest.php        ← NEW
routes/api_v1.php                                     ← MODIFY (uncomment login + logout routes)
tests/Feature/Api/V1/Auth/LoginTest.php               ← NEW
tests/Feature/Api/V1/Auth/LogoutTest.php              ← NEW
```

### References

- [Source: _bmad-output/planning-artifacts/epics.md#Story 1.3 — acceptance criteria, BDD scenarios]
- [Source: _bmad-output/planning-artifacts/architecture.md#Auth Domain — controller pattern, file organization]
- [Source: _bmad-output/planning-artifacts/architecture.md#API Design Rules — response format, error codes]
- [Source: _bmad-output/planning-artifacts/architecture.md#Rate Limiting — login 5/min per IP]
- [Source: _bmad-output/planning-artifacts/prd.md#FR2,FR3 — login/logout requirements]
- [Source: _bmad-output/planning-artifacts/prd.md#NFR7,10,13 — session cookies, rate limiting, CSRF]
- [Source: _bmad-output/implementation-artifacts/1-2-customer-registration.md — learnings, review findings, anti-patterns]
- [Source: bootstrap/app.php#lines 40-41 — login rate limiter already defined]

### Review Findings

- [x] [Review][Decision] Non-customer users can login via customer API — Admin/staff with valid credentials can POST `/api/v1/auth/login`, receive 200 with their data (id, name, email) leaked, and then cannot logout (403 from `customer` middleware on logout route = dangling session). Story anti-pattern says "❌ Checking customer role during login" but this creates a real vulnerability. [AuthController.php:48-63, routes/api_v1.php:29-33] — **RESOLVED: Added role check in login(), return 401 for non-customer + test added**
- [x] [Review][Patch] Add max:255 to email and password in LoginRequest — Inconsistent with RegisterRequest which has max:255. Defense-in-depth against oversized payloads. [LoginRequest.php:18-20] — **FIXED**
- [x] [Review][Patch] Strengthen CSRF cookie test to assert XSRF-TOKEN cookie presence — AC6 says "XSRF-TOKEN cookie is set" but test only asserts 204 status, not that the cookie is actually present. [LogoutTest.php:66-71] — **FIXED**
- [x] [Review][Defer] Per-email rate limiting for distributed brute-force — Login rate limiter keys only on IP (5/min). A botnet from many IPs can target one email. Not in spec (NFR10 says "5/min per IP"). — deferred, beyond MVP scope
- [x] [Review][Defer] SESSION_SECURE_COOKIE defaults to null — Pre-existing config, not introduced by this diff. Session cookies may be sent over plain HTTP if env var not set. — deferred, pre-existing

## Dev Agent Record

### Agent Model Used
Claude Opus 4.6 (claude-opus-4.6)

### Debug Log References
- Pre-existing 4 test failures unchanged (2 CustomerRegistrationProcess FK constraint, 2 missing MaterialAndService factory)
- Rate limit test requires 5 failed attempts before asserting 429 — takes ~1s due to Auth::attempt bcrypt hashing

### Completion Notes List
- Created LoginRequest with email (required, string, email) and password (required, string) validation
- Added login() method to AuthController — Auth::attempt for credentials, session regeneration, 403 guard for already-authenticated
- Added logout() method to AuthController — Auth::guard('web')->logout(), session invalidate + regenerateToken
- Login route wired at POST /api/v1/auth/login with throttle:login (5/min/IP) + throttle:guest (100/min)
- Logout route wired at POST /api/v1/auth/logout inside auth:sanctum + customer middleware group
- 8 LoginTest feature tests (28 assertions) — covers all 3 login ACs
- 4 LogoutTest feature tests (5 assertions) — covers logout, unauthenticated, CSRF cookie ACs
- Pint passes (136 files, 0 issues)

### Change Log
- Story created: 2026-04-12 — ready-for-dev
- Implementation complete: 2026-04-12 — all 8 tasks done, 12 tests passing, status → review

### File List
- app/Http/Controllers/Api/V1/AuthController.php (modified — added login + logout methods)
- app/Http/Requests/Api/V1/Auth/LoginRequest.php (new — validation rules)
- routes/api_v1.php (modified — wired login + logout routes)
- tests/Feature/Api/V1/Auth/LoginTest.php (new — 8 tests)
- tests/Feature/Api/V1/Auth/LogoutTest.php (new — 4 tests)
