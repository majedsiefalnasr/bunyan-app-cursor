# STAGE_03 — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION
> **Status:** NOT STARTED
> **Scope:** User auth (Sanctum), registration, login, password reset
> **Risk Level:** HIGH

## Stage Status

Status: BACKEND CLOSED
Step: implement
Implementation: COMPLETE
Tasks: 34 / 34 completed
Risk Level: HIGH
Last Updated: 2026-04-11T00:00:00Z

Scope Defined:

- Sanctum API token auth (register, login, logout)
- Password reset flow (forgot + reset)
- Email verification (send, verify, resend)
- Profile get/update
- Backend architecture refactor (controller → service delegation)
- All frontend auth pages (Nuxt UI, Arabic/RTL)
- 4 architecture violations resolved

Deferred Scope:

- Social/OAuth login
- Two-factor authentication
- Admin user management CRUD

Architecture Governance Compliance:

- Drift analysis PASSED — all criteria satisfied
- All guardian verdicts: PASS
- Implementation AUTHORIZED

Notes:
All 34 tasks analyzed. No drift detected. Implementation authorized.

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
