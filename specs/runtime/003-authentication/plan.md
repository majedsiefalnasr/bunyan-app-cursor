# Plan — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Created:** 2026-04-11T00:00:00Z

## Implementation Strategy

The implementation follows a **backend-first, refactor-before-extend** approach:

1. **Phase A — Backend Refactor:** Fix architecture violations first (controller → service delegation, error codes, translations)
2. **Phase B — Backend Extend:** Add missing features (password reset, email verification, rate limiting)
3. **Phase C — Frontend Fix:** Resolve type mismatches, redirect inconsistencies
4. **Phase D — Frontend Build:** Build all auth pages with Nuxt UI
5. **Phase E — Testing:** Comprehensive backend + frontend tests

This ordering ensures each phase builds on a stable foundation.

---

## Phase A — Backend Architecture Refactor

### A1. Create UserRepository

**File:** `backend/app/Repositories/UserRepository.php`

```php
class UserRepository
{
    public function findByEmail(string $email): ?User
    public function create(array $data): User
    public function update(User $user, array $data): User
}
```

Encapsulates all Eloquent queries for users. AuthService injects this.

### A2. Refactor AuthService

**File:** `backend/app/Services/AuthService.php`

Inject `UserRepository`. Migrate logic from `UserController`:

| Method                                   | Source             | Description                                                  |
| ---------------------------------------- | ------------------ | ------------------------------------------------------------ |
| `login(string $email, string $password)` | Existing (enhance) | Find user, verify password, check active, create token       |
| `register(array $data)`                  | Existing (enhance) | Create user (role=customer), create token, send verification |
| `logout(User $user)`                     | Existing (keep)    | Revoke all tokens                                            |
| `forgotPassword(string $email)`          | New                | Send password reset notification                             |
| `resetPassword(array $data)`             | New                | Validate token, update password, revoke tokens               |
| `verifyEmail(int $id, string $hash)`     | New                | Mark email as verified                                       |
| `resendVerification(User $user)`         | New                | Resend verification notification                             |

### A3. Refactor UserController to Thin Controller

**File:** `backend/app/Http/Controllers/Api/V1/UserController.php`

Every method becomes: validate → delegate to service → return response.

```php
public function login(LoginRequest $request): JsonResponse
{
    $result = $this->authService->login($request->email, $request->password);
    // Handle null (failed) vs array (success) vs 'inactive' (blocked)
}
```

### A4. Add Error Codes to ErrorCode Enum

**File:** `backend/app/Enums/ErrorCode.php`

Add cases:

- `AUTH_ACCOUNT_INACTIVE` → 403
- `AUTH_INVALID_RESET_TOKEN` → 422
- `AUTH_EMAIL_ALREADY_VERIFIED` → 422
- `AUTH_EMAIL_NOT_VERIFIED` → 422

### A5. Add Auth Translation Files

**Files:** `backend/resources/lang/ar/auth.php`, `backend/resources/lang/en/auth.php`

```php
// ar/auth.php
return [
    'login_success' => 'تم تسجيل الدخول بنجاح',
    'register_success' => 'تم إنشاء الحساب بنجاح',
    'logout_success' => 'تم تسجيل الخروج بنجاح',
    'password_reset_sent' => 'تم إرسال رابط إعادة تعيين كلمة المرور',
    'password_reset_success' => 'تم إعادة تعيين كلمة المرور بنجاح',
    'email_verified' => 'تم التحقق من البريد الإلكتروني بنجاح',
    'verification_sent' => 'تم إرسال رابط التحقق',
    'profile_updated' => 'تم تحديث الملف الشخصي بنجاح',
    'account_inactive' => 'الحساب غير مفعل',
    'email_already_verified' => 'البريد الإلكتروني محقق بالفعل',
];
```

### A6. Update Error Translations

**Files:** `backend/resources/lang/ar/errors.php`, `en/errors.php`

Add translations for new error codes.

### A7. Update UserResource

**File:** `backend/app/Http/Resources/Api/V1/UserResource.php`

Add `email_verified_at` to response array.

### A8. Fix Form Requests

- **LoginRequest:** Change password min from 6 to 8, use translation keys for messages
- **RegisterRequest:** Remove `role` field, add `password_confirmation`, use translation keys

---

## Phase B — Backend Feature Extension

### B1. Create password_reset_tokens Migration

**File:** `backend/database/migrations/YYYY_MM_DD_HHMMSS_create_password_reset_tokens_table.php`

Standard Laravel password reset tokens table.

### B2. Publish sanctum.php Config

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
```

Set `expiration` to 1440 (24 hours).

### B3. Implement MustVerifyEmail on User Model

**File:** `backend/app/Models/User.php`

Add `implements MustVerifyEmail` to class declaration. The `email_verified_at` column already exists.

### B4. Create ForgotPasswordRequest

**File:** `backend/app/Http/Requests/Api/V1/ForgotPasswordRequest.php`

Validate: `email` (required, email).

### B5. Create ResetPasswordRequest

**File:** `backend/app/Http/Requests/Api/V1/ResetPasswordRequest.php`

Validate: `token` (required, string), `email` (required, email), `password` (required, min:8, confirmed, mixedCase, numbers).

### B6. Add Routes

**File:** `backend/routes/api.php`

```php
// Public auth routes (add to existing)
Route::post('auth/forgot-password', [UserController::class, 'forgotPassword'])
    ->middleware('throttle:3,1')
    ->name('password.email');
Route::post('auth/reset-password', [UserController::class, 'resetPassword'])
    ->middleware('throttle:3,1')
    ->name('password.update');

// Add throttle to existing public routes
Route::post('auth/login', ...)->middleware('throttle:5,1');
Route::post('auth/register', ...)->middleware('throttle:5,1');

// Protected routes (add to sanctum group)
Route::get('auth/email/verify/{id}/{hash}', [UserController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verification.verify');
Route::post('auth/email/resend', [UserController::class, 'resendVerification'])
    ->middleware('throttle:1,1')
    ->name('verification.send');
```

### B7. Add Structured Logging

Add `Log::info()` / `Log::warning()` calls in `AuthService` for:

- Login success (user_id, ip)
- Login failure (email, ip)
- Registration (user_id, ip)
- Logout (user_id)
- Password reset request (email)
- Password reset completion (user_id)
- Email verification (user_id)

---

## Phase C — Frontend Fixes

### C1. Fix UserRole Type

**File:** `frontend/types/auth.ts`

```typescript
export type UserRole =
  | "customer"
  | "contractor"
  | "supervising_architect"
  | "field_engineer"
  | "admin";
```

Add `email_verified_at` and `phone` to `UserProfile`.

### C2. Fix useApi.ts Redirect Paths

**File:** `frontend/composables/useApi.ts`

Change `/auth/login` → `/ar/auth/login`, `/dashboard` → `/ar/dashboard`.

### C3. Enhance Auth Store

**File:** `frontend/stores/auth.ts`

Add:

- `useCookie()` for token persistence
- `login(email, password)` action → API call + store token + fetch user
- `register(data)` action → API call + store token
- `fetchUser()` action → GET /api/v1/auth/profile
- `logout()` action → POST /api/v1/auth/logout + clear state

### C4. Enhance useAuth Composable

**File:** `frontend/composables/useAuth.ts`

Add:

- `login()`, `register()` methods that delegate to store
- Use consistent `/ar` prefix for all redirects

---

## Phase D — Frontend Pages

### D1. Auth Layout Enhancement

**File:** `frontend/layouts/auth.vue`

Already exists with good structure. Enhance with:

- RTL-aware centering
- Design system typography (Geist fonts)
- Shadow-as-border on card

### D2. Login Page Rewrite

**File:** `frontend/pages/auth/login.vue`

- Replace raw HTML with Nuxt UI (`UForm`, `UFormField`, `UInput`, `UButton`)
- Zod validation schema
- API integration via auth store
- Arabic labels and error messages
- Loading state on submit
- Link to register, forgot password

### D3. Register Page

**File:** `frontend/pages/auth/register.vue`

- Nuxt UI form with fields: name, email, password, password confirmation, phone
- Zod validation with Arabic messages
- API integration
- Redirect to verify-email page on success

### D4. Forgot Password Page

**File:** `frontend/pages/auth/forgot-password.vue`

- Email input + submit
- Success message after submission
- Link back to login

### D5. Reset Password Page

**File:** `frontend/pages/auth/reset-password.vue`

- Read token and email from URL query params
- Password + confirmation inputs
- API integration
- Redirect to login on success

### D6. Email Verification Page

**File:** `frontend/pages/auth/verify-email.vue`

- Status display (verified / pending)
- Resend button with cooldown
- Auto-verify if URL contains verification params

### D7. Create app.config.ts

**File:** `frontend/app.config.ts`

Nuxt UI color configuration (achromatic palette per DESIGN.md).

---

## Phase E — Testing

### E1. Backend Unit Tests

**File:** `backend/tests/Unit/Services/AuthServiceTest.php`

Test all AuthService methods with mocked UserRepository.

### E2. Backend Feature Tests

**File:** `backend/tests/Feature/Api/V1/AuthenticationTest.php`

Test all API endpoints:

- Login (success, wrong password, wrong email, inactive user, rate limit)
- Register (success, duplicate email, weak password, validation)
- Logout (success, unauthenticated)
- Forgot password (success, invalid email, rate limit)
- Reset password (success, invalid token, expired token)
- Email verification (success, already verified, invalid signature)
- Resend verification (success, already verified, rate limit)
- Profile get/update (success, unauthenticated)

### E3. Frontend Tests

**File:** `frontend/tests/unit/stores/auth.test.ts`

Test auth store actions with mocked API.

**File:** `frontend/tests/unit/middleware/auth.test.ts`

Test auth middleware redirect behavior.

---

## Dependency DAG

```
A1 (UserRepository)
 └─→ A2 (AuthService refactor)
      └─→ A3 (UserController refactor)
           └─→ A4 (Error codes)
                └─→ A5 + A6 (Translations)
                     └─→ A7 (UserResource)
                          └─→ A8 (Form Requests)

B1 (Migration) ──────────────┐
B2 (sanctum.php) ────────────┤
B3 (MustVerifyEmail) ────────┤
B4 + B5 (Form Requests) ─────┤
                              └─→ B6 (Routes)
                                   └─→ B7 (Logging)

C1 (Fix types) ──────────────┐
C2 (Fix redirects) ──────────┤
                              └─→ C3 (Auth store)
                                   └─→ C4 (useAuth)

D1 (Layout) ─────────────────┐
D7 (app.config.ts) ──────────┤
                              └─→ D2 (Login) ─→ D3 (Register) ─→ D4 (Forgot) ─→ D5 (Reset) ─→ D6 (Verify)

E1 (Unit tests) ─────────────┐
E2 (Feature tests) ──────────┤
E3 (Frontend tests) ─────────┘
```

## Risk Mitigation

| Risk                                    | Mitigation                                     |
| --------------------------------------- | ---------------------------------------------- |
| Breaking existing tests during refactor | Run `php artisan test` after each Phase A task |
| Email delivery config missing           | Use `log` mail driver for development/testing  |
| Token cookie security                   | HttpOnly, Secure, SameSite=Lax flags           |
| Password reset token leakage            | Token in email only, not in API responses      |
| Rate limit false positives              | Configurable thresholds, clear error messages  |
