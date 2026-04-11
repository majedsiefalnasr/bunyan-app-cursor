# Requirements Checklist — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Created:** 2026-04-11T00:00:00Z

## Architecture Compliance

- [ ] `UserController` is thin — delegates ALL logic to `AuthService`
- [ ] `AuthService` contains all authentication business logic
- [ ] `UserRepository` handles all Eloquent queries for user data
- [ ] Form Request validation for all user inputs (Login, Register, ForgotPassword, ResetPassword, UpdateProfile)
- [ ] Error contract followed (`success`/`data`/`message`/`errors` format)
- [ ] No business logic in controllers or models
- [ ] No hardcoded strings in controllers — translation keys only

## Security

- [ ] `auth:sanctum` middleware on all protected endpoints (profile, update, logout, email resend)
- [ ] Public endpoints: login, register, forgot-password, reset-password, verify-email
- [ ] Input sanitization via Form Requests on all endpoints
- [ ] SQL injection prevention (Eloquent parameterized queries only)
- [ ] XSS prevention (API Resources sanitize output)
- [ ] Rate limiting: 5 req/min on login/register, 3 req/min on forgot-password, 1 req/min on resend-verification
- [ ] Sensitive data never exposed in API responses (password, remember_token, tokens)
- [ ] Password hashed with bcrypt (Laravel `Hash` facade)
- [ ] Password reset tokens expire after 60 minutes
- [ ] Inactive users (`active = false`) blocked from login with `AUTH_ACCOUNT_INACTIVE` error code
- [ ] Failed login attempts logged with IP address for security monitoring

## Database

- [ ] No new migrations required (users, personal_access_tokens, password_reset_tokens already exist)
- [ ] Existing indexes verified: `users.email` (unique), `users.role`, `users.active`
- [ ] Foreign keys maintained from Stage 02
- [ ] Soft deletes on users table (already configured)
- [ ] Timestamps present on all relevant tables

## API Quality

- [ ] RESTful endpoint naming: `/api/v1/auth/*`
- [ ] Proper HTTP status codes: 200 (success), 201 (created), 401 (unauthorized), 422 (validation), 429 (rate limited)
- [ ] API Resources for response formatting (`UserResource`)
- [ ] Consistent error response format on all endpoints
- [ ] Password reset token validated server-side
- [ ] Email verification uses signed URLs

## Frontend

- [ ] Arabic/RTL layout on all auth pages (verified visually)
- [ ] Mobile responsive design (375px–1440px+)
- [ ] Loading states on all form submissions (button loading indicator)
- [ ] Error handling: inline field errors + toast notifications
- [ ] Form validation with Zod schemas and Arabic error messages
- [ ] Accessible: ARIA attributes, keyboard navigation, focus management
- [ ] Nuxt UI components used throughout (`UForm`, `UInput`, `UButton`, `UCard`)
- [ ] Design system compliance: Geist fonts, shadow-as-border, achromatic palette
- [ ] Token persisted in cookie for SSR compatibility
- [ ] `UserRole` type matches backend enum values exactly

## Testing

- [ ] Unit tests for `AuthService` (login, register, logout, forgotPassword, resetPassword, verifyEmail)
- [ ] Feature tests for all auth API endpoints (happy path + error scenarios)
- [ ] RBAC matrix tested: authenticated ✅ on protected routes, unauthenticated ❌, rate limiting ✅
- [ ] Frontend Vitest tests for auth store (login, register, logout, fetchUser)
- [ ] Frontend Vitest tests for auth middleware and role middleware
- [ ] Edge cases: expired tokens, invalid tokens, duplicate email registration, inactive user login

## Performance

- [ ] Login/register API response < 200ms
- [ ] No N+1 queries (single user fetch per auth operation)
- [ ] Token generation efficient (single DB insert)
- [ ] Rate limiting does not add significant latency

## i18n

- [ ] All user-facing strings use translation keys
- [ ] Arabic translations provided (`resources/lang/ar/auth.php`)
- [ ] English translations provided (`resources/lang/en/auth.php`)
- [ ] Frontend labels and errors in Arabic by default
- [ ] Validation messages in Arabic locale

## Observability

- [ ] Login success/failure logged with `user_id`, `ip`, `action`
- [ ] Registration logged with `user_id`, `action`
- [ ] Logout logged with `user_id`, `action`
- [ ] Password reset request/completion logged
- [ ] Email verification logged
- [ ] All auth events use structured logging format
