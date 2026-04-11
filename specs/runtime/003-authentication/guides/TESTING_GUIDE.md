# Testing Guide — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Generated:** 2026-04-11T00:00:00Z

## Prerequisites

```bash
# Backend
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed

# Frontend
cd frontend
npm install
```

Ensure the `.env` file has:

```
DB_CONNECTION=mysql
DB_DATABASE=bunyan_test
MAIL_MAILER=log
SANCTUM_STATEFUL_DOMAINS=localhost,localhost:3000
```

## Running Automated Tests

### Backend Unit Tests (AuthService)

```bash
cd backend
php artisan test --testsuite=Unit --filter=AuthServiceTest
```

Tests: login (success, invalid credentials, inactive account, nonexistent email), register (success, forces customer role), logout (revokes tokens), verify email, resend verification.

### Backend Feature Tests (API Endpoints)

```bash
cd backend
php artisan test --testsuite=Feature --filter=AuthenticationTest
```

Tests: all 9 API endpoints with happy path, validation errors, RBAC checks, and rate limiting.

### All Backend Tests

```bash
cd backend
php artisan test
```

### Frontend Lint + Type Check

```bash
cd frontend
npm run lint
npm run typecheck
```

## Manual Test Scenarios

### Scenario 1 — User Registration

**Preconditions:**

- Backend server running at `http://localhost:8000`
- Database migrated and seeded
- Mail driver set to `log`

**Steps:**

1. Open `POST http://localhost:8000/api/v1/auth/register`
2. Send body:

```json
{
  "name": "أحمد محمد",
  "email": "ahmed@test.com",
  "password": "Password1",
  "password_confirmation": "Password1",
  "phone": "+966501234567"
}
```

3. Check response

**Expected Result:**

- Status: 201
- Response contains `data.user` with role `customer` and `data.token`
- User created in database with `role = customer`
- Verification email logged in `storage/logs/laravel.log`

### Scenario 2 — User Login (Success)

**Preconditions:**

- User exists with email `ahmed@test.com` and password `Password1`, `active = true`

**Steps:**

1. Send `POST /api/v1/auth/login` with `{"email": "ahmed@test.com", "password": "Password1"}`

**Expected Result:**

- Status: 200
- Response contains `data.user` and `data.token`
- Login event logged

### Scenario 3 — Login with Inactive Account

**Preconditions:**

- User exists with `active = false`

**Steps:**

1. Send `POST /api/v1/auth/login` with valid credentials

**Expected Result:**

- Status: 403
- Error code: `AUTH_ACCOUNT_INACTIVE`

### Scenario 4 — Login with Wrong Password

**Preconditions:**

- User exists in database

**Steps:**

1. Send `POST /api/v1/auth/login` with wrong password

**Expected Result:**

- Status: 401
- Error code: `AUTH_INVALID_CREDENTIALS`

### Scenario 5 — Password Reset Flow

**Preconditions:**

- User exists with email `ahmed@test.com`
- Mail driver set to `log`

**Steps:**

1. Send `POST /api/v1/auth/forgot-password` with `{"email": "ahmed@test.com"}`
2. Check `storage/logs/laravel.log` for reset token in email
3. Extract token from log
4. Send `POST /api/v1/auth/reset-password` with `{"token": "<token>", "email": "ahmed@test.com", "password": "NewPassword1", "password_confirmation": "NewPassword1"}`

**Expected Result:**

- Step 1: Status 200 (always returns success)
- Step 4: Status 200, password updated
- Old tokens revoked
- Can login with new password

### Scenario 6 — Rate Limiting

**Preconditions:**

- None

**Steps:**

1. Send `POST /api/v1/auth/login` with wrong credentials 6 times rapidly

**Expected Result:**

- First 5 requests: 401 (invalid credentials)
- 6th request: 429 (rate limited) with `Retry-After` header

### Scenario 7 — Get Profile (Authenticated)

**Preconditions:**

- User logged in with valid token

**Steps:**

1. Send `GET /api/v1/auth/profile` with `Authorization: Bearer <token>`

**Expected Result:**

- Status: 200
- Response contains user profile with `email_verified_at` field

### Scenario 8 — Get Profile (Unauthenticated)

**Steps:**

1. Send `GET /api/v1/auth/profile` without Authorization header

**Expected Result:**

- Status: 401

### Scenario 9 — Frontend Login Page

**Preconditions:**

- Frontend dev server running at `http://localhost:3000`

**Steps:**

1. Navigate to `http://localhost:3000/ar/auth/login`
2. Verify page renders in Arabic/RTL
3. Verify Nuxt UI form components (UInput, UButton)
4. Enter invalid email, verify inline validation error
5. Enter valid credentials, verify redirect to dashboard

**Expected Result:**

- Arabic labels and error messages
- RTL layout (labels right-aligned)
- Inline validation on blur
- Loading state on submit button
- Redirect to `/ar/dashboard` on success

### Scenario 10 — Frontend Registration Page

**Steps:**

1. Navigate to `http://localhost:3000/ar/auth/register`
2. Fill in name, email, password, confirm, phone
3. Submit form

**Expected Result:**

- All fields validate with Arabic messages
- Password mismatch shows error
- On success, redirects to `/ar/auth/verify-email`

## API Test Endpoints

| Method | Endpoint                              | Auth    | Rate Limit | Expected Status |
| ------ | ------------------------------------- | ------- | ---------- | --------------- |
| POST   | /api/v1/auth/register                 | Public  | 5/min      | 201 / 422       |
| POST   | /api/v1/auth/login                    | Public  | 5/min      | 200 / 401 / 403 |
| POST   | /api/v1/auth/logout                   | Sanctum | —          | 200 / 401       |
| POST   | /api/v1/auth/forgot-password          | Public  | 3/min      | 200 / 422       |
| POST   | /api/v1/auth/reset-password           | Public  | 3/min      | 200 / 422       |
| GET    | /api/v1/auth/email/verify/{id}/{hash} | Signed  | —          | 200 / 403 / 422 |
| POST   | /api/v1/auth/email/resend             | Sanctum | 1/min      | 200 / 401 / 422 |
| GET    | /api/v1/auth/profile                  | Sanctum | —          | 200 / 401       |
| PUT    | /api/v1/auth/profile                  | Sanctum | —          | 200 / 401 / 422 |

## Common Issues

| Issue                           | Cause                        | Fix                                 |
| ------------------------------- | ---------------------------- | ----------------------------------- |
| 500 on forgot-password          | SMTP not configured          | Set `MAIL_MAILER=log` in `.env`     |
| Token not persisting on refresh | Cookie not set               | Check `useCookie()` in auth store   |
| 429 Too Many Requests           | Rate limiting active         | Wait 60 seconds or use different IP |
| Migration fails                 | password_reset_tokens exists | Run `php artisan migrate:fresh`     |
| Frontend 401 redirect loop      | Expired token in cookie      | Clear cookies, re-login             |
| Arabic text not displaying      | Missing i18n keys            | Check `frontend/locales/ar.json`    |
