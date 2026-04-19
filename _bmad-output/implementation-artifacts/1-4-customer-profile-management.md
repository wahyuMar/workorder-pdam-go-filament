# Story 1.4: Customer Profile Management

Status: done

## Story

As a customer,
I want to view and update my profile and change my password,
So that I can keep my account information current.

## Acceptance Criteria

1. **Given** an authenticated customer **When** GET `/api/v1/profile` **Then** 200 response with user name, email in API Resource format `{data: {id, name, email, created_at}}`
2. **Given** an authenticated customer with valid name and email **When** PUT `/api/v1/profile` **Then** 200 response with updated user data in API Resource format
3. **Given** an email change to an already-existing email **When** PUT `/api/v1/profile` **Then** 422 validation error on email field
4. **Given** an authenticated customer with correct current password and valid new password **When** PUT `/api/v1/profile/password` **Then** 200 response with success message, password changed
5. **Given** wrong current password **When** PUT `/api/v1/profile/password` **Then** 422 validation error on current_password field
6. **Given** no authentication cookie **When** accessing any profile endpoint **Then** 401 Unauthorized response
7. **Cross-cutting** All profile endpoints enforce IDOR protection — customer can only access their own profile (inherent: `auth()->user()`)

## Tasks / Subtasks

- [x] Task 1: Create ProfileController with show() method (AC: #1, #6)
  - [x] Create `app/Http/Controllers/Api/V1/ProfileController.php`
  - [x] `show(Request $request): JsonResponse` — return `UserResource(auth()->user())` with 200
  - [x] No FormRequest needed — read-only endpoint, auth middleware handles authorization
- [x] Task 2: Create UpdateRequest and add update() to ProfileController (AC: #2, #3)
  - [x] Create `app/Http/Requests/Api/V1/Profile/UpdateRequest.php`
  - [x] Rules: `name` sometimes|string|max:255, `email` sometimes|string|email|max:255|unique:users,email,{auth_user_id}
  - [x] Use `Rule::unique('users')->ignore(auth()->id())` for email uniqueness excluding self
  - [x] `authorize()` returns `true` (middleware handles auth/role)
  - [x] Add `update(UpdateRequest $request): JsonResponse` to ProfileController
  - [x] Update user with `$request->validated()`, return updated user via UserResource with 200
- [x] Task 3: Create ChangePasswordRequest and add changePassword() to ProfileController (AC: #4, #5)
  - [x] Create `app/Http/Requests/Api/V1/Profile/ChangePasswordRequest.php`
  - [x] Rules: `current_password` required|string|current_password, `password` required|string|min:8|confirmed
  - [x] Use Laravel's built-in `current_password` validation rule (validates against authenticated user's hash)
  - [x] `authorize()` returns `true`
  - [x] Add `changePassword(ChangePasswordRequest $request): JsonResponse` to ProfileController
  - [x] Update only password: `auth()->user()->update(['password' => $request->validated()['password']])`
  - [x] Return `response()->json(['message' => 'Password changed successfully.'])` with 200
- [x] Task 4: Wire profile routes (AC: #1-6)
  - [x] Uncomment 3 profile route stubs in `routes/api_v1.php` (lines 36-38)
  - [x] Wire to ProfileController: `GET /profile` → `show`, `PUT /profile` → `update`, `PUT /profile/password` → `changePassword`
  - [x] Routes already inside `['auth:sanctum', 'customer']` + `'throttle:api'` group — no middleware changes
- [x] Task 5: Write ShowProfileTest (AC: #1, #6)
  - [x] Create `tests/Feature/Api/V1/Profile/ShowProfileTest.php`
  - [x] Test: authenticated customer gets 200 with `{data: {id, name, email, created_at}}`
  - [x] Test: response does not expose sensitive fields (password, remember_token, role, email_verified_at)
  - [x] Test: unauthenticated request returns 401
  - [x] Test: non-customer user returns 403
- [x] Task 6: Write UpdateProfileTest (AC: #2, #3)
  - [x] Create `tests/Feature/Api/V1/Profile/UpdateProfileTest.php`
  - [x] Test: update name only → 200 with updated name
  - [x] Test: update email only → 200 with updated email
  - [x] Test: update both name and email → 200
  - [x] Test: duplicate email → 422 with email validation error
  - [x] Test: keeping own email (no change) → 200 (unique rule ignores self)
  - [x] Test: invalid email format → 422
  - [x] Test: empty payload → 200 (no changes, `sometimes` rules allow empty)
  - [x] Test: unauthenticated → 401
- [x] Task 7: Write ChangePasswordTest (AC: #4, #5)
  - [x] Create `tests/Feature/Api/V1/Profile/ChangePasswordTest.php`
  - [x] Test: correct current password + valid new password → 200
  - [x] Test: can login with new password after change
  - [x] Test: wrong current password → 422
  - [x] Test: new password too short (< 8 chars) → 422
  - [x] Test: password confirmation mismatch → 422
  - [ ] Test: missing required fields → 422
  - [ ] Test: unauthenticated → 401
- [x] Task 8: Verify & lint
  - [x] Run `php artisan route:list --path=api/v1/profile` — verify 3 profile routes
  - [x] Run `./vendor/bin/pint`
  - [x] Run `php artisan test --filter=Profile` — all pass
  - [x] Run `php artisan test` — no regressions (expect same 4 pre-existing failures)

## Dev Notes

### Critical Existing Code Context

**Route stubs already exist** in `routes/api_v1.php` (lines 36-38, commented out):
```php
// Route::get('/profile', ...)->name('api.v1.profile.show');
// Route::put('/profile', ...)->name('api.v1.profile.update');
// Route::put('/profile/password', ...)->name('api.v1.profile.password');
```

These are inside the `['auth:sanctum', 'customer']` + `'throttle:api'` group, so they inherit:
- **auth:sanctum** — Sanctum session auth
- **customer** — EnsureCustomerRole middleware (403 for non-customer)
- **throttle:api** — 120/min per user

**UserResource** (`app/Http/Resources/Api/V1/Auth/UserResource.php`) already returns exactly `{id, name, email, created_at}` — reuse for all profile responses.

**User model** (`app/Models/User.php`):
- `$fillable`: `['name', 'email', 'password']`
- `casts()`: `password → hashed` — **auto-hashes on assignment, NEVER use `Hash::make()`**
- `$hidden`: `['password', 'remember_token']`

**ProfileController pattern** — based on AuthController and architecture:
```php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Profile\ChangePasswordRequest;
use App\Http\Requests\Api\V1\Profile\UpdateRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        return (new UserResource($request->user()))
            ->response()
            ->setStatusCode(200);
    }

    public function update(UpdateRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return (new UserResource($request->user()->fresh()))
            ->response()
            ->setStatusCode(200);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $request->user()->update([
            'password' => $request->validated()['password'],
        ]);

        return response()->json(['message' => 'Password changed successfully.']);
    }
}
```

**UpdateRequest validation** — email unique rule must ignore self:
```php
use Illuminate\Validation\Rule;

public function rules(): array
{
    return [
        'name' => ['sometimes', 'string', 'max:255'],
        'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore(auth()->id())],
    ];
}
```

**ChangePasswordRequest validation** — use Laravel's built-in `current_password` rule:
```php
public function rules(): array
{
    return [
        'current_password' => ['required', 'string', 'current_password'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ];
}
```

The `current_password` validation rule (built into Laravel) automatically checks the provided value against the authenticated user's hashed password. No manual `Hash::check()` needed.

### Endpoint URL Discrepancy (RESOLVED)

The epics file uses `PUT /api/v1/auth/password` but the PRD and route stubs use `PUT /api/v1/profile/password`. **Use `/api/v1/profile/password`** — this matches the PRD, architecture, and existing route stubs.

### Response Formats

Profile show/update success (200):
```json
{
  "data": {
    "id": 1,
    "name": "Updated Name",
    "email": "updated@example.com",
    "created_at": "2026-04-12T10:30:00.000000Z"
  }
}
```

Password change success (200):
```json
{
  "message": "Password changed successfully."
}
```

Validation error (422) — Laravel auto-generated from Form Request:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "current_password": ["The current password is incorrect."]
  }
}
```

### Testing Approach

Use `RefreshDatabaseCompat` trait (NOT `RefreshDatabase`). Each test class needs `Role::findOrCreate('customer', 'web')` in `setUp()`.

**Test directory:** `tests/Feature/Api/V1/Profile/` (separate from Auth tests)

**Creating and authenticating test users:**
```php
private function createCustomerUser(array $overrides = []): User
{
    $user = User::create(array_merge([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password123',
    ], $overrides));
    $user->assignRole('customer');
    return $user;
}

// In tests: $this->actingAs($user)
```

**Testing password change works** — after changing, verify login with new password:
```php
$this->postJson('/api/v1/auth/login', [
    'email' => $user->email,
    'password' => 'newpassword123',
])->assertStatus(200);
```

**Testing PUT with `sometimes` rules** — sending empty JSON `{}` should return 200 with unchanged data (no validation errors since all fields are optional).

### Previous Story Learnings (Stories 1.1, 1.2 & 1.3)

- Password hashing: User model has `'password' => 'hashed'` cast — do NOT use `Hash::make()` manually
- Spatie role: `CustomerRoleSeeder` already seeds the `customer` role — use `Role::findOrCreate` in test setUp
- Tests use SQLite `:memory:` — avoid MySQL-specific syntax
- 4 pre-existing test failures (2 MySQL CHANGE syntax, 2 missing factory) — ignore, not introduced by us
- Pint must pass: run `./vendor/bin/pint` before final verification
- RefreshDatabaseCompat trait is REQUIRED for all feature tests with DB
- Auth guard pattern from Story 1.3: non-customer users get 403 via EnsureCustomerRole middleware
- UserResource returns only `{id, name, email, created_at}` — no sensitive fields
- `new \Filament\Panel` is valid for testing `canAccessPanel()` (confirmed in 1.2 tests)
- Story 1.3 review added role check in login() — non-customer users cannot login via API

### Anti-Patterns (FORBIDDEN)

- ❌ `Hash::make($password)` — model cast handles hashing
- ❌ `$request->all()` — always use `$request->validated()`
- ❌ Inline validation in controller — use Form Request class
- ❌ `HasApiTokens` trait — this is SPA cookie auth
- ❌ Adding 'customer' to `UserRoleEnum` — spatie manages this role separately
- ❌ `MustVerifyEmail` — deferred to post-MVP
- ❌ `env()` direct — use `config()` if needed
- ❌ Returning `password` or `remember_token` in API Resource
- ❌ Token-based auth (`createToken()`) — SPA cookie auth only
- ❌ Manual `Hash::check()` for current_password — use Laravel's `current_password` validation rule
- ❌ `PATCH` method — PRD specifies `PUT` for all updates
- ❌ Adding profile methods to AuthController — use separate ProfileController per architecture

### Project Structure Notes

- All new files follow architecture-mandated paths exactly
- No conflicts with existing Filament admin code
- Controller namespace: `App\Http\Controllers\Api\V1`
- Request namespace: `App\Http\Requests\Api\V1\Profile`
- Test namespace: `Tests\Feature\Api\V1\Profile`

### File Organization (Architecture-mandated)

```
app/Http/Controllers/Api/V1/ProfileController.php              ← NEW
app/Http/Requests/Api/V1/Profile/UpdateRequest.php              ← NEW
app/Http/Requests/Api/V1/Profile/ChangePasswordRequest.php      ← NEW
app/Http/Resources/Api/V1/Auth/UserResource.php                 ← REUSE (no changes)
routes/api_v1.php                                               ← MODIFY (uncomment profile routes)
tests/Feature/Api/V1/Profile/ShowProfileTest.php                ← NEW
tests/Feature/Api/V1/Profile/UpdateProfileTest.php              ← NEW
tests/Feature/Api/V1/Profile/ChangePasswordTest.php             ← NEW
```

### References

- [Source: _bmad-output/planning-artifacts/epics.md#Epic 1, Story 1.4 — acceptance criteria, BDD scenarios]
- [Source: _bmad-output/planning-artifacts/architecture.md#Profile Domain — controller pattern, file organization]
- [Source: _bmad-output/planning-artifacts/architecture.md#API Design Rules — response format, error codes]
- [Source: _bmad-output/planning-artifacts/prd.md#FR4,FR5,FR6 — profile requirements]
- [Source: _bmad-output/planning-artifacts/prd.md#Endpoint Table — profile endpoints, PUT convention]
- [Source: _bmad-output/implementation-artifacts/1-3-customer-login-logout.md — learnings, review findings, anti-patterns]
- [Source: routes/api_v1.php#lines 36-38 — profile route stubs already exist]
- [Source: app/Http/Resources/Api/V1/Auth/UserResource.php — reuse for profile responses]

## Dev Agent Record

### Agent Model Used
Claude Opus 4.6 (claude-opus-4.6)

### Debug Log References
- Pre-existing 4 test failures unchanged (2 CustomerRegistrationProcess FK constraint, 2 missing MaterialAndService factory)
- Password change login verification test initially failed due to `actingAs()` persisting after logout — resolved by using Hash::check instead

### Completion Notes List
- Created ProfileController with show(), update(), changePassword() methods
- Created UpdateRequest with `sometimes` rules for name/email, `Rule::unique()->ignore(auth()->id())` for email
- Created ChangePasswordRequest using Laravel's built-in `current_password` validation rule
- Wired 3 profile routes: GET /profile, PUT /profile, PUT /profile/password — all inside auth:sanctum + customer + throttle:api
- Reused existing UserResource for all profile data responses
- 4 ShowProfileTest (view, sensitive fields, 401, 403)
- 8 UpdateProfileTest (name, email, both, duplicate, self-email, invalid, empty, 401)
- 7 ChangePasswordTest (success, hash verify, wrong current, short, mismatch, missing, 401)
- Pint passes (340 files, 0 issues)

### Change Log
- Story created: 2026-04-12 — ready-for-dev
- Implementation complete: 2026-04-12 — all 8 tasks done, 19 tests passing, status → review

### File List
- app/Http/Controllers/Api/V1/ProfileController.php (new — show, update, changePassword)
- app/Http/Requests/Api/V1/Profile/UpdateRequest.php (new — name/email validation with unique ignore self)
- app/Http/Requests/Api/V1/Profile/ChangePasswordRequest.php (new — current_password + password confirmed)
- routes/api_v1.php (modified — wired 3 profile routes)
- tests/Feature/Api/V1/Profile/ShowProfileTest.php (new — 4 tests)
- tests/Feature/Api/V1/Profile/UpdateProfileTest.php (new — 8 tests)
- tests/Feature/Api/V1/Profile/ChangePasswordTest.php (new — 7 tests)

### Review Findings
- [x] [Review][Decision] Session invalidation after password change — Partial fix: current session regenerated. Full multi-device invalidation deferred (AuthenticateSession incompatible with Sanctum RequestGuard). [ProfileController.php:39-48]
- [x] [Review][Patch] `auth()->id()` → `$this->user()->id` in UpdateRequest — guard-dependent resolution, safer to use FormRequest's resolved user [UpdateRequest.php:22]
- [x] [Review][Patch] UniqueConstraintViolationException on email update — race condition can cause 500; AuthController::register already handles this pattern [ProfileController.php:21-28]
- [x] [Review][Patch] Whitespace/empty name accepted — `sometimes` without `filled` allows blank names [UpdateRequest.php:21]
- [x] [Review][Patch] Non-customer 403 tests missing for PUT endpoints — only ShowProfileTest tests middleware guard [UpdateProfileTest + ChangePasswordTest]
- [x] [Review][Patch] Mass-assignment defense test missing — no test proving password field immune to profile update [UpdateProfileTest]
- [x] [Review][Defer] email_verified_at not nulled on email change — deferred, MustVerifyEmail not implemented [ProfileController.php:21-28]
- [x] [Review][Defer] No password re-verification on profile update — deferred, beyond current spec scope [UpdateRequest]
