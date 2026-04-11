# Tasks — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Created:** 2026-04-11T00:00:00Z

## Phase A — Backend Architecture Refactor

- [x] T001 [US2,US1] Create `backend/app/Repositories/UserRepository.php` with `findByEmail()`, `create()`, `update()` methods
- [x] T002 [US2,US1,US3] Refactor `backend/app/Services/AuthService.php` — inject `UserRepository`, enhance `login()` with active check, enhance `register()` to force customer role, keep `logout()`
- [x] T003 [US2,US1,US3,US6] Refactor `backend/app/Http/Controllers/Api/V1/UserController.php` — delegate all logic to `AuthService`, thin controller pattern
- [x] T004 Add new error codes to `backend/app/Enums/ErrorCode.php`: `AUTH_ACCOUNT_INACTIVE`, `AUTH_INVALID_RESET_TOKEN`, `AUTH_EMAIL_ALREADY_VERIFIED`, `AUTH_EMAIL_NOT_VERIFIED`
- [x] T005 Create `backend/resources/lang/ar/auth.php` with Arabic auth messages (login_success, register_success, logout_success, etc.)
- [x] T006 Create `backend/resources/lang/en/auth.php` with English auth messages
- [x] T007 Update `backend/resources/lang/ar/errors.php` and `backend/resources/lang/en/errors.php` with new error code translations
- [x] T008 [US6] Update `backend/app/Http/Resources/Api/V1/UserResource.php` — add `email_verified_at` field
- [x] T009 [US1] Fix `backend/app/Http/Requests/Api/V1/RegisterRequest.php` — remove `role` field, add `password_confirmation` rule
- [x] T010 [US2] Fix `backend/app/Http/Requests/Api/V1/LoginRequest.php` — change password min from 6 to 8, use translation keys

## Phase B — Backend Feature Extension

- [x] T011 Create migration `backend/database/migrations/YYYY_MM_DD_HHMMSS_create_password_reset_tokens_table.php`
- [x] T012 Publish `backend/config/sanctum.php` and set `expiration` to 1440 (24 hours)
- [x] T013 [US5] Add `MustVerifyEmail` interface to `backend/app/Models/User.php`
- [x] T014 [US4] Create `backend/app/Http/Requests/Api/V1/ForgotPasswordRequest.php` with email validation
- [x] T015 [US4] Create `backend/app/Http/Requests/Api/V1/ResetPasswordRequest.php` with token + email + password validation
- [x] T016 [US4] Add `forgotPassword()` and `resetPassword()` methods to `AuthService`
- [x] T017 [US5] Add `verifyEmail()` and `resendVerification()` methods to `AuthService`
- [x] T018 [US4,US5] Add controller methods: `forgotPassword()`, `resetPassword()`, `verifyEmail()`, `resendVerification()` to `UserController`
- [x] T019 [US2,US1,US4,US5] Update `backend/routes/api.php` — add password reset routes, email verification routes, rate limiting middleware on auth routes
- [x] T020 Add structured logging to all `AuthService` methods (login success/fail, register, logout, password reset, email verification)

## Phase C — Frontend Fixes

- [x] T021 [US7] Fix `frontend/types/auth.ts` — change `UserRole` values to match backend (`supervising_architect`, `field_engineer`), add `email_verified_at` and `phone` to `UserProfile`
- [x] T022 [US7] Fix `frontend/composables/useApi.ts` — change redirect paths to use `/ar` prefix (`/ar/auth/login`, `/ar/dashboard`)
- [x] T023 [US2,US1,US3,US7] Enhance `frontend/stores/auth.ts` — add `useCookie()` token persistence, `login()`, `register()`, `fetchUser()`, `logout()` actions with API calls
- [x] T024 [US2,US3] Update `frontend/composables/useAuth.ts` — add `login()`, `register()` delegates, fix redirect paths

## Phase D — Frontend Pages

- [x] T025 Create `frontend/app.config.ts` with Nuxt UI color configuration (achromatic palette per DESIGN.md)
- [x] T026 [P] Enhance `frontend/layouts/auth.vue` — design system refinements (Geist typography, shadow-as-border, RTL)
- [x] T027 [US2] Rewrite `frontend/pages/auth/login.vue` — Nuxt UI components (`UForm`, `UFormField`, `UInput`, `UButton`), Zod validation, API integration via auth store, Arabic labels, loading state, links to register/forgot-password
- [x] T028 [US1] Create `frontend/pages/auth/register.vue` — Nuxt UI form (name, email, password, confirm, phone), Zod validation, API integration, redirect to verify-email
- [x] T029 [US4] Create `frontend/pages/auth/forgot-password.vue` — email input, API integration, success message, link to login
- [x] T030 [US4] Create `frontend/pages/auth/reset-password.vue` — read token/email from URL, password form, API integration, redirect to login
- [x] T031 [US5] Create `frontend/pages/auth/verify-email.vue` — verification status, resend button, auto-verify from URL params

## Phase E — Testing

- [x] T032 [P] Create `backend/tests/Unit/Services/AuthServiceTest.php` — unit tests for all AuthService methods (login, register, logout, forgotPassword, resetPassword, verifyEmail)
- [x] T033 [P] Create `backend/tests/Feature/Api/V1/AuthenticationTest.php` — feature tests for all auth endpoints (happy path, errors, rate limits, RBAC matrix)
- [x] T034 [P] Create `frontend/tests/unit/stores/auth.test.ts` and `frontend/tests/unit/middleware/auth.test.ts` — Vitest tests for auth store and middleware

## Task Summary

| Phase                | Tasks  | IDs       |
| -------------------- | ------ | --------- |
| A — Backend Refactor | 10     | T001–T010 |
| B — Backend Extend   | 10     | T011–T020 |
| C — Frontend Fix     | 4      | T021–T024 |
| D — Frontend Pages   | 7      | T025–T031 |
| E — Testing          | 3      | T032–T034 |
| **Total**            | **34** |           |

## Dependency Order

```
T001 → T002 → T003 (repository → service → controller refactor)
T004, T005, T006, T007 (parallel — error codes + translations)
T008, T009, T010 (parallel — resource + request fixes)
T011, T012, T013 (parallel — migration + config + model)
T014, T015 → T016 → T017 → T018 → T019 (requests → service → controller → routes)
T020 (logging — after all service methods exist)
T021, T022 (parallel — frontend type + redirect fixes)
T023 → T024 (store → composable)
T025, T026 (parallel — config + layout)
T027, T028, T029, T030, T031 (sequential — pages)
T032, T033, T034 (parallel — all tests)
```
