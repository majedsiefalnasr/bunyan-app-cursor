# Research — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Created:** 2026-04-11T00:00:00Z

## Laravel Sanctum Token Authentication

### How It Works

- Sanctum provides a lightweight API token system using `personal_access_tokens` table
- `HasApiTokens` trait on User model enables `createToken()`, `tokens()`, and `currentAccessToken()`
- Tokens are SHA-256 hashed in DB; plain text returned only on creation
- Middleware `auth:sanctum` validates Bearer token on each request
- Token abilities/scopes available but not needed for this stage (RBAC handles permissions)

### Token Lifecycle

1. **Create:** `$user->createToken('api_token')` → returns `NewAccessToken` with `plainTextToken`
2. **Validate:** `auth:sanctum` middleware checks `Authorization: Bearer <token>` header
3. **Revoke:** `$user->tokens()->delete()` revokes all tokens; `$user->currentAccessToken()->delete()` revokes current only
4. **Expire:** Configure `expiration` in `config/sanctum.php` (minutes)

### Configuration (sanctum.php)

```php
return [
    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', '')),
    'guard' => ['web'],
    'expiration' => 1440, // 24 hours in minutes
    'token_prefix' => '',
    'middleware' => ['authenticate_session', 'encrypt_cookies', 'csrf_token'],
];
```

## Laravel Password Reset Flow

### Built-in Components

- `Password` facade: `Password::sendResetLink()`, `Password::reset()`
- `password_reset_tokens` table: `email`, `token` (hashed), `created_at`
- `ResetPassword` notification: customizable email template
- Token expiration configured in `config/auth.php` → `passwords.users.expire` (60 min default)

### Custom API Implementation

Since we're API-only (no Blade views), we implement custom endpoints:

1. **Forgot Password:** Receive email → generate token → send notification email with frontend URL containing token
2. **Reset Password:** Receive email + token + new password → validate → update password → revoke all tokens

### Password Broker Configuration (auth.php)

```php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
    ],
],
```

## Laravel Email Verification (API)

### Built-in: MustVerifyEmail Interface

- Implement `MustVerifyEmail` on User model
- Provides `hasVerifiedEmail()`, `markEmailAsVerified()`, `sendEmailVerificationNotification()`
- Verification uses signed URLs with expiration

### API Implementation

1. **On Registration:** Send `VerifyEmail` notification with signed URL pointing to frontend
2. **Verify:** Frontend sends request to backend with signed URL parameters (id, hash)
3. **Resend:** Authenticated endpoint to resend verification email

## Existing Codebase Analysis

### Architecture Violations Found

| Area                   | Issue                                                                               | Impact                                     |
| ---------------------- | ----------------------------------------------------------------------------------- | ------------------------------------------ |
| UserController         | Business logic in controller (login/register)                                       | MEDIUM — violates thin controller pattern  |
| AuthService            | Exists but not used by controller                                                   | HIGH — dead code / confusion               |
| RegisterRequest        | Allows role selection (customer, contractor, supervising_architect, field_engineer) | HIGH — role escalation risk                |
| LoginRequest           | Password min:6 instead of min:8                                                     | LOW — inconsistent with RegisterRequest    |
| UserResource           | Missing `email_verified_at` field                                                   | LOW — needed for verification feature      |
| ErrorCode              | Missing `AUTH_ACCOUNT_INACTIVE` error code                                          | MEDIUM — needed for inactive user handling |
| Frontend types/auth.ts | Role enum mismatch (`architect` vs `supervising_architect`)                         | HIGH — breaks role checks                  |
| useApi.ts              | Redirect paths missing `/ar` prefix                                                 | MEDIUM — inconsistent with middleware      |

### Existing Infrastructure to Leverage

- `ApiResponse` trait (sendSuccess/sendError) — well-structured, keep as-is
- `ErrorCode` enum with `httpStatus()`, `severity()`, `description()` — extend, don't replace
- `BaseController` with notFound/unauthorized/forbidden helpers — keep as-is
- Auth layout (`auth.vue`) with `UCard` — good foundation, enhance with design system
- Auth store with token/user state — enhance with API actions
- Auth/role middleware — fix redirect paths, keep logic

### What Needs Creation

| Item                             | Type         | Path                                                |
| -------------------------------- | ------------ | --------------------------------------------------- |
| password_reset_tokens migration  | Migration    | `backend/database/migrations/`                      |
| sanctum.php config               | Config       | `backend/config/sanctum.php`                        |
| ForgotPasswordRequest            | Form Request | `backend/app/Http/Requests/Api/V1/`                 |
| ResetPasswordRequest             | Form Request | `backend/app/Http/Requests/Api/V1/`                 |
| UserRepository                   | Repository   | `backend/app/Repositories/`                         |
| AUTH_ACCOUNT_INACTIVE error code | Enum case    | `backend/app/Enums/ErrorCode.php`                   |
| AUTH_PASSWORD_RESET_SENT         | Enum case    | `backend/app/Enums/ErrorCode.php`                   |
| AUTH_EMAIL_ALREADY_VERIFIED      | Enum case    | `backend/app/Enums/ErrorCode.php`                   |
| Auth translation file            | Translations | `backend/resources/lang/ar/auth.php`, `en/auth.php` |
| Register page                    | Vue page     | `frontend/pages/auth/register.vue`                  |
| Forgot password page             | Vue page     | `frontend/pages/auth/forgot-password.vue`           |
| Reset password page              | Vue page     | `frontend/pages/auth/reset-password.vue`            |
| Email verification page          | Vue page     | `frontend/pages/auth/verify-email.vue`              |
| app.config.ts                    | Config       | `frontend/app.config.ts`                            |

### What Needs Modification

| Item               | Type     | Change                                               |
| ------------------ | -------- | ---------------------------------------------------- |
| UserController     | Refactor | Delegate to AuthService                              |
| AuthService        | Enhance  | Add forgotPassword, resetPassword, verifyEmail, etc. |
| RegisterRequest    | Fix      | Remove `role` field, add `password_confirmation`     |
| LoginRequest       | Fix      | Change password min to 8                             |
| UserResource       | Enhance  | Add `email_verified_at`                              |
| ErrorCode          | Extend   | Add auth-specific error codes                        |
| User model         | Enhance  | Implement MustVerifyEmail                            |
| api.php routes     | Extend   | Add password reset, email verification routes        |
| auth.ts store      | Enhance  | Add API actions, cookie persistence                  |
| useAuth.ts         | Fix      | Use consistent redirect paths                        |
| useApi.ts          | Fix      | Add `/ar` prefix to redirects                        |
| types/auth.ts      | Fix      | Match backend role enum values                       |
| login.vue          | Rewrite  | Nuxt UI integration, API calls, Arabic               |
| auth.vue layout    | Enhance  | Design system refinements                            |
| errors.php (ar/en) | Extend   | Add new error code translations                      |
