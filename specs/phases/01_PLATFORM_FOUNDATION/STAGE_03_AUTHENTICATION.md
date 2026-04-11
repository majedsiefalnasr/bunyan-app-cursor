# STAGE_03 — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION
> **Status:** NOT STARTED
> **Scope:** User auth (Sanctum), registration, login, password reset
> **Risk Level:** HIGH

## Stage Status

Status: PRODUCTION READY
Risk Level: HIGH
Closure Date: 2026-04-11

Scope Closed:

- Sanctum API token auth (register, login, logout), 9 API endpoints
- Password reset flow (forgot + reset with email-based tokens)
- Email verification (send on register, verify, resend)
- Profile get/update
- Backend architecture refactor (controller → service delegation)
- All 5 frontend auth pages (Nuxt UI, Arabic/RTL, Zod validation)
- 4 architecture violations resolved
- 50+ automated tests (21 unit + 29 feature)
- 34 / 34 tasks completed

Deferred Scope:

- Social/OAuth login (future stage)
- Two-factor authentication (future stage)
- Admin user management CRUD (RBAC stage)

Architecture Governance Compliance:

- ADR alignment verified
- RBAC enforcement confirmed (auth:sanctum on all protected routes)
- Service layer architecture maintained (Controller → Service → Repository)
- Error contract compliance verified
- i18n/RTL support verified

Notes:
Stage is production ready. Modifications require a new stage.

## Objective

Implement complete authentication system using Laravel Sanctum. Support API token-based auth for SPA and mobile clients.

## Scope

### Backend

- Registration endpoint with validation
- Login endpoint with token generation
- Logout endpoint (token revocation)
- Password reset flow (email-based)
- Email verification
- Auth middleware configuration
- User profile endpoint (get/update)

### Frontend

- Login page with form validation
- Registration page with form validation
- Forgot password page
- Email verification page
- Auth store (Pinia) with token management
- API client with auth interceptors
- Protected route middleware

### API Endpoints

| Method | Route                     | Description            |
| ------ | ------------------------- | ---------------------- |
| POST   | /api/auth/register        | User registration      |
| POST   | /api/auth/login           | User login             |
| POST   | /api/auth/logout          | User logout            |
| POST   | /api/auth/forgot-password | Request password reset |
| POST   | /api/auth/reset-password  | Reset password         |
| GET    | /api/auth/user            | Get authenticated user |
| PUT    | /api/auth/user            | Update profile         |

## Dependencies

- **Upstream:** STAGE_02_DATABASE_SCHEMA
- **Downstream:** All authenticated features
