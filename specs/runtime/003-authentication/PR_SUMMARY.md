# PR — Authentication

## Summary

**Stage:** Authentication
**Phase:** 01_PLATFORM_FOUNDATION
**Branch:** `spec/003-authentication` → `develop`
**Tasks:** 34 / 34 completed

Implements a complete authentication system for the Bunyan platform using Laravel Sanctum with API token-based auth. Includes backend refactoring to comply with Bunyan architecture governance (thin controllers, service delegation, repository pattern), password reset flow, email verification, all frontend auth pages with Nuxt UI + Arabic/RTL support, and 50+ automated tests.

## What Changed

### Backend

- **Refactored** `UserController` to thin controller pattern — all business logic delegated to `AuthService`
- **Enhanced** `AuthService` with 9 methods: login (with active check), register (customer-only), logout, forgotPassword, resetPassword, verifyEmail, resendVerification, refreshToken, validateToken
- **Created** `UserRepository` for data access layer
- **Added** `MustVerifyEmail` interface to `User` model
- **Created** `ForgotPasswordRequest` and `ResetPasswordRequest` form requests
- **Fixed** `RegisterRequest` — removed role field (prevents escalation), added password confirmation
- **Fixed** `LoginRequest` — password min changed from 6 to 8
- **Added** `email_verified_at` to `UserResource`
- **Added** 4 error codes: `AUTH_ACCOUNT_INACTIVE`, `AUTH_INVALID_RESET_TOKEN`, `AUTH_EMAIL_ALREADY_VERIFIED`, `AUTH_EMAIL_NOT_VERIFIED`
- **Added** rate limiting: 5/min on login/register, 3/min on forgot/reset password, 1/min on resend verification
- **Added** structured logging for all auth events
- **Created** auth translations (Arabic + English)
- **Published** `sanctum.php` config with 24-hour token expiration

### Frontend

- **Rewrote** login page with Nuxt UI (`UForm`, `UInput`, `UButton`), Zod validation, API integration
- **Created** register, forgot-password, reset-password, verify-email pages
- **Enhanced** auth store with cookie-based token persistence and API actions
- **Fixed** `UserRole` type to match backend enum (`supervising_architect`, `field_engineer`)
- **Fixed** redirect paths — standardized to `/ar` prefix
- **Added** 28 auth i18n keys (Arabic + English)
- **Created** `app.config.ts` for Nuxt UI achromatic palette

### Database

- **Created** `password_reset_tokens` migration (email PK, token, created_at)

## Breaking Changes

- `RegisterRequest` no longer accepts `role` field — all registrations create `customer` role
- `LoginRequest` password minimum changed from 6 to 8 characters
- Frontend `UserRole` type values changed: `architect` → `supervising_architect`, `engineer` → `field_engineer`
- Frontend `UserProfile` interface expanded with new fields

## Testing

- [x] Unit tests pass — 21 tests in `AuthServiceTest`
- [x] Feature tests pass — 29 tests in `AuthenticationTest`
- [x] Lint passes (Laravel Pint + ESLint)
- [x] Type check passes (PHPStan + Nuxt typecheck)

## Checklist

- [x] `auth:sanctum` middleware on all protected routes
- [x] Form Request validation on all endpoints
- [x] Arabic/RTL support verified on all auth pages
- [x] Error contract followed on all responses
- [x] No N+1 queries (single user fetch per operation)
- [x] Rate limiting on all sensitive endpoints
- [x] Migration includes `down()` method
- [x] Structured logging on all auth events
- [x] Translation keys replace all hardcoded strings

## Related

- Stage File: `specs/phases/01_PLATFORM_FOUNDATION/STAGE_03_AUTHENTICATION.md`
- Testing Guide: `specs/runtime/003-authentication/guides/TESTING_GUIDE.md`
- API Contract: `specs/runtime/003-authentication/contracts/auth-api.md`
- Closure Report: `specs/runtime/003-authentication/reports/CLOSURE_REPORT.md`
