# API Contract — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Created:** 2026-04-11T00:00:00Z

## Endpoints Overview

| Method | Route                                 | Auth       | Rate Limit     | Description            |
| ------ | ------------------------------------- | ---------- | -------------- | ---------------------- |
| POST   | /api/v1/auth/register                 | Public     | 5/min per IP   | User registration      |
| POST   | /api/v1/auth/login                    | Public     | 5/min per IP   | User login             |
| POST   | /api/v1/auth/logout                   | Sanctum    | —              | User logout            |
| POST   | /api/v1/auth/forgot-password          | Public     | 3/min per IP   | Request password reset |
| POST   | /api/v1/auth/reset-password           | Public     | 3/min per IP   | Reset password         |
| GET    | /api/v1/auth/email/verify/{id}/{hash} | Signed URL | —              | Verify email           |
| POST   | /api/v1/auth/email/resend             | Sanctum    | 1/min per user | Resend verification    |
| GET    | /api/v1/auth/profile                  | Sanctum    | —              | Get profile            |
| PUT    | /api/v1/auth/profile                  | Sanctum    | —              | Update profile         |

---

## POST /api/v1/auth/register

### Request

```json
{
  "name": "أحمد محمد",
  "email": "ahmed@example.com",
  "password": "SecureP@ss1",
  "password_confirmation": "SecureP@ss1",
  "phone": "+966501234567"
}
```

### Validation Rules

| Field    | Rules                                          |
| -------- | ---------------------------------------------- |
| name     | required, string, max:255                      |
| email    | required, email, unique:users                  |
| password | required, min:8, confirmed, mixedCase, numbers |
| phone    | nullable, string, max:20                       |

### Response (201)

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "email": "ahmed@example.com",
      "role": "customer",
      "phone": "+966501234567",
      "active": true,
      "email_verified_at": null,
      "created_at": "2026-04-11T00:00:00+00:00",
      "updated_at": "2026-04-11T00:00:00+00:00"
    },
    "token": "1|abc..."
  },
  "message": null,
  "errors": [],
  "error": null
}
```

### Error Responses

- **422** — Validation errors (duplicate email, weak password)
- **429** — Rate limit exceeded

---

## POST /api/v1/auth/login

### Request

```json
{
  "email": "ahmed@example.com",
  "password": "SecureP@ss1"
}
```

### Validation Rules

| Field    | Rules                   |
| -------- | ----------------------- |
| email    | required, email         |
| password | required, string, min:8 |

### Response (200)

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "أحمد محمد",
      "email": "ahmed@example.com",
      "role": "customer",
      "phone": "+966501234567",
      "active": true,
      "email_verified_at": "2026-04-11T00:00:00+00:00",
      "created_at": "...",
      "updated_at": "..."
    },
    "token": "2|xyz..."
  },
  "message": null,
  "errors": [],
  "error": null
}
```

### Error Responses

- **401** — `AUTH_INVALID_CREDENTIALS` (wrong email/password)
- **403** — `AUTH_ACCOUNT_INACTIVE` (user deactivated)
- **422** — Validation errors
- **429** — Rate limit exceeded

---

## POST /api/v1/auth/logout

### Headers

```
Authorization: Bearer {token}
```

### Response (200)

```json
{
  "success": true,
  "data": null,
  "message": null,
  "errors": [],
  "error": null
}
```

### Error Responses

- **401** — `AUTH_UNAUTHORIZED` (invalid/expired token)

---

## POST /api/v1/auth/forgot-password

### Request

```json
{
  "email": "ahmed@example.com"
}
```

### Response (200) — Always returns success (prevents user enumeration)

```json
{
  "success": true,
  "data": null,
  "message": null,
  "errors": [],
  "error": null
}
```

### Error Responses

- **422** — Validation error (invalid email format)
- **429** — Rate limit exceeded

---

## POST /api/v1/auth/reset-password

### Request

```json
{
  "email": "ahmed@example.com",
  "token": "abc123...",
  "password": "NewSecureP@ss1",
  "password_confirmation": "NewSecureP@ss1"
}
```

### Validation Rules

| Field    | Rules                                          |
| -------- | ---------------------------------------------- |
| email    | required, email                                |
| token    | required, string                               |
| password | required, min:8, confirmed, mixedCase, numbers |

### Response (200)

```json
{
  "success": true,
  "data": null,
  "message": null,
  "errors": [],
  "error": null
}
```

### Error Responses

- **422** — `AUTH_INVALID_RESET_TOKEN` (expired/invalid token)
- **429** — Rate limit exceeded

---

## GET /api/v1/auth/email/verify/{id}/{hash}

Signed URL — no Authorization header needed, signature validated.

### Response (200)

```json
{
  "success": true,
  "data": null,
  "message": null,
  "errors": [],
  "error": null
}
```

### Error Responses

- **403** — Invalid signature
- **422** — `AUTH_EMAIL_ALREADY_VERIFIED`

---

## POST /api/v1/auth/email/resend

### Headers

```
Authorization: Bearer {token}
```

### Response (200)

```json
{
  "success": true,
  "data": null,
  "message": null,
  "errors": [],
  "error": null
}
```

### Error Responses

- **401** — `AUTH_UNAUTHORIZED`
- **422** — `AUTH_EMAIL_ALREADY_VERIFIED`
- **429** — Rate limit exceeded (1 per minute)

---

## GET /api/v1/auth/profile

### Headers

```
Authorization: Bearer {token}
```

### Response (200)

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "أحمد محمد",
    "email": "ahmed@example.com",
    "role": "customer",
    "phone": "+966501234567",
    "active": true,
    "email_verified_at": "2026-04-11T00:00:00+00:00",
    "created_at": "2026-04-11T00:00:00+00:00",
    "updated_at": "2026-04-11T00:00:00+00:00"
  },
  "message": null,
  "errors": [],
  "error": null
}
```

---

## PUT /api/v1/auth/profile

### Headers

```
Authorization: Bearer {token}
```

### Request

```json
{
  "name": "أحمد علي محمد",
  "phone": "+966509876543"
}
```

### Validation Rules

| Field | Rules                      |
| ----- | -------------------------- |
| name  | sometimes, string, max:255 |
| phone | sometimes, string, max:20  |

### Response (200)

```json
{
  "success": true,
  "data": { "id": 1, "name": "أحمد علي محمد", "...": "..." },
  "message": null,
  "errors": [],
  "error": null
}
```
