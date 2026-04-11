# Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Stage File:** `specs/phases/01_PLATFORM_FOUNDATION/STAGE_03_AUTHENTICATION.md` > **Branch:** `spec/003-authentication` > **Created:** 2026-04-11T00:00:00Z

## Objective

Deliver a complete, production-grade authentication system for the Bunyan platform using Laravel Sanctum (API token-based). This stage refactors existing skeleton auth code to comply with Bunyan architecture governance (thin controllers, service delegation, repository pattern), adds missing flows (password reset, email verification), builds all frontend auth pages with Nuxt UI + RTL/Arabic support, and resolves known inconsistencies (role enum mismatch, redirect path mismatches, hardcoded strings).

## Scope

### In Scope

- **Backend refactor:** Delegate all business logic from `UserController` to `AuthService` (architecture compliance)
- **Backend:** Password reset flow — forgot-password (send email) + reset-password (validate token, update password)
- **Backend:** Email verification — send verification email on registration, verify endpoint
- **Backend:** Rate limiting on sensitive auth endpoints (login, register, forgot-password)
- **Backend:** Structured logging for all auth events (login success/fail, registration, logout, password reset)
- **Backend:** Auth-related unit tests (AuthService) and feature tests (API endpoints, RBAC matrix)
- **Frontend:** Login page — full API integration, Nuxt UI components, Arabic/RTL, design system compliance
- **Frontend:** Registration page — form validation (Zod + VeeValidate), Nuxt UI, Arabic/RTL
- **Frontend:** Forgot password page — email submission flow
- **Frontend:** Reset password page — token-based password reset form
- **Frontend:** Email verification page — verification status and resend
- **Frontend:** Auth store enhancement — token persistence (cookie-based for SSR compatibility), login/register/logout actions with API calls
- **Frontend:** Fix `UserRole` type to match backend enum values (`supervising_architect`, `field_engineer`)
- **Frontend:** Fix redirect path inconsistencies (`/ar` prefix alignment between middleware and composables)
- **Frontend:** Auth layout for all auth pages
- **Frontend:** Vitest tests for auth store, middleware, and composables

### Out of Scope

- Social/OAuth login (future stage)
- Two-factor authentication (future stage)
- User management (admin CRUD on users — separate RBAC stage)
- Role/permission CRUD (covered by RBAC stage)
- Session-based auth (Sanctum SPA cookie auth — API tokens only for now)
- Avatar upload (profile media — separate stage)

## User Stories

### US1 — User Registration

**As a** new user, **I want** to register an account with my name, email, password, and phone number, **so that** I can access the Bunyan platform.

**Acceptance Criteria:**

- [ ] Registration form validates: name (required, max 255), email (required, unique, valid format), password (required, min 8, confirmed), phone (optional, valid format), role (optional, defaults to `customer`)
- [ ] On success: account created, verification email sent, token returned, user redirected to email verification page
- [ ] On failure: Arabic validation error messages displayed inline
- [ ] Rate limited to 5 requests per minute per IP
- [ ] Registration event logged with structured context

### US2 — User Login

**As a** registered user, **I want** to log in with my email and password, **so that** I can access my dashboard.

**Acceptance Criteria:**

- [ ] Login form validates: email (required, valid format), password (required)
- [ ] On success: API token issued, user profile loaded, redirected to role-appropriate dashboard
- [ ] On failure: Arabic error message "بيانات الاعتماد غير صحيحة" displayed
- [ ] Inactive users (`active = false`) cannot log in
- [ ] Rate limited to 5 requests per minute per IP
- [ ] Failed login attempts logged with IP address

### US3 — User Logout

**As an** authenticated user, **I want** to log out, **so that** my session is securely terminated.

**Acceptance Criteria:**

- [ ] All API tokens for the user are revoked server-side
- [ ] Frontend clears token and user state from store and cookie
- [ ] User redirected to login page
- [ ] Logout event logged

### US4 — Password Reset

**As a** user who forgot my password, **I want** to reset it via email, **so that** I can regain access to my account.

**Acceptance Criteria:**

- [ ] Forgot password form accepts email, sends reset link via email
- [ ] Reset link contains time-limited token (60 minutes)
- [ ] Reset form validates: token, email, password (min 8, confirmed)
- [ ] On success: password updated, all existing tokens revoked, user redirected to login
- [ ] On failure: Arabic error messages displayed
- [ ] Rate limited to 3 requests per minute per email
- [ ] Password reset events logged

### US5 — Email Verification

**As a** newly registered user, **I want** to verify my email address, **so that** my account is fully activated.

**Acceptance Criteria:**

- [ ] Verification email sent automatically on registration
- [ ] Verification link contains signed URL with expiration
- [ ] On successful verification: `email_verified_at` updated, user redirected to dashboard
- [ ] Resend verification endpoint available (rate limited to 1 per minute)
- [ ] Unverified users can still log in but see a banner prompting verification

### US6 — Get/Update Profile

**As an** authenticated user, **I want** to view and update my profile, **so that** my information stays current.

**Acceptance Criteria:**

- [ ] GET profile returns: id, name, email, role, phone, active, email_verified_at, created_at
- [ ] PUT profile allows updating: name, phone, password (with current password confirmation)
- [ ] Email changes trigger re-verification
- [ ] Profile update event logged

### US7 — Protected Route Access

**As a** visitor, **I want** to be redirected to the login page when accessing protected routes, **so that** unauthenticated access is prevented.

**Acceptance Criteria:**

- [ ] Frontend middleware checks auth token before allowing access to protected pages
- [ ] Role middleware checks user role against page requirements
- [ ] 401 API responses trigger automatic logout and redirect
- [ ] Token persisted across page refreshes (cookie-based)

## Technical Requirements

### Backend (Laravel)

- [ ] Refactor `UserController` to delegate ALL business logic to `AuthService` (thin controller pattern)
- [ ] `AuthService` methods: `login()`, `register()`, `logout()`, `forgotPassword()`, `resetPassword()`, `verifyEmail()`, `resendVerification()`, `getProfile()`, `updateProfile()`
- [ ] Create `UserRepository` with Eloquent queries (findByEmail, create, update)
- [ ] Create `ForgotPasswordRequest`, `ResetPasswordRequest`, `VerifyEmailRequest` Form Requests
- [ ] Add password reset routes: `POST /api/v1/auth/forgot-password`, `POST /api/v1/auth/reset-password`
- [ ] Add email verification routes: `GET /api/v1/auth/email/verify/{id}/{hash}`, `POST /api/v1/auth/email/resend`
- [ ] Apply rate limiting middleware: `throttle:5,1` on login/register, `throttle:3,1` on forgot-password
- [ ] Publish `sanctum.php` config for explicit token expiration and domain configuration
- [ ] Add structured logging for all auth events (login, register, logout, password reset, verification)
- [ ] Inactive user check in login flow (return specific error code `AUTH_ACCOUNT_INACTIVE`)
- [ ] All error responses follow Bunyan error contract (`success`, `data`, `message`, `errors`)
- [ ] Use translation keys for all response messages (no hardcoded Arabic strings in controllers/services)
- [ ] Unit tests for `AuthService` (all methods, edge cases)
- [ ] Feature tests for all auth API endpoints (happy path, validation, RBAC, rate limiting)

### Frontend (Nuxt.js)

- [ ] Login page (`pages/auth/login.vue`): Nuxt UI `UForm` + `UInput` + `UButton`, Arabic labels, RTL, design system compliant
- [ ] Register page (`pages/auth/register.vue`): Nuxt UI form, Zod validation schema, Arabic labels
- [ ] Forgot password page (`pages/auth/forgot-password.vue`): Email input, success message
- [ ] Reset password page (`pages/auth/reset-password.vue`): Token from URL query, new password + confirm
- [ ] Email verification page (`pages/auth/verify-email.vue`): Status display, resend button
- [ ] Auth layout (`layouts/auth.vue`): Centered card, Bunyan branding, design system shadow-as-border
- [ ] Enhance `auth.ts` store: add `login()`, `register()`, `fetchUser()` actions with API calls
- [ ] Token persistence via `useCookie()` for SSR-compatible token storage
- [ ] Fix `UserRole` type: change `'architect'` → `'supervising_architect'`, `'engineer'` → `'field_engineer'`
- [ ] Fix `useApi.ts` redirect paths: align with middleware paths (add `/ar` prefix or use i18n-aware routing)
- [ ] Add `UserProfile` field: `email_verified_at` (nullable timestamp)
- [ ] Vitest tests for auth store actions, auth middleware, role middleware

## Dependencies

- **Upstream:** STAGE_02_DATABASE_SCHEMA (users table, personal_access_tokens table, password_reset_tokens table)
- **Downstream:** All authenticated features (projects, phases, tasks, reports, e-commerce, admin)

## Non-Functional Requirements

- [ ] Login/register API response time < 200ms
- [ ] Arabic/RTL layout on all auth pages (verified visually)
- [ ] RBAC enforcement: auth endpoints properly gated (`auth:sanctum` on protected, public on login/register)
- [ ] Error contract compliance on all API responses
- [ ] Mobile responsive auth pages (works on 375px–1440px+)
- [ ] Rate limiting prevents brute force on login (5 attempts/min/IP)
- [ ] Token expiration configured (default 24 hours, configurable)
- [ ] Password hashed with bcrypt (Laravel default)
- [ ] No sensitive data in API responses (password, remember_token never exposed)
- [ ] All auth events have structured log entries for observability

## Architecture Violations to Resolve

### AV1 — Controller Business Logic Duplication

**Current:** `UserController@login` and `@register` contain full business logic (DB queries, Hash checks, token creation) instead of delegating to `AuthService`.

**Fix:** Controller calls `AuthService` methods; service returns DTOs or arrays; controller returns `UserResource`.

### AV2 — Frontend Role Enum Mismatch

**Current:** Frontend `UserRole` type uses `'architect'` and `'engineer'` while backend `UserRole` enum uses `'supervising_architect'` and `'field_engineer'`.

**Fix:** Update frontend `types/auth.ts` to match backend enum values exactly.

### AV3 — Redirect Path Inconsistency

**Current:** `useApi.ts` redirects to `/auth/login` and `/dashboard` (no locale prefix), but `middleware/auth.ts` and `useAuth.ts` redirect to `/ar/auth/login` and `/ar/dashboard`.

**Fix:** Standardize all redirect paths to use locale-aware routing via `useLocalePath()` or consistent `/ar` prefix.

### AV4 — Hardcoded Arabic Strings

**Current:** `UserController` has hardcoded Arabic strings: `'تم تسجيل الدخول بنجاح'`, `'تم إنشاء الحساب بنجاح'`, etc.

**Fix:** Use Laravel translation keys: `__('auth.login_success')`, `__('auth.register_success')`.

## Open Questions

- None at this time. All requirements are clearly defined from the stage file and codebase analysis.
