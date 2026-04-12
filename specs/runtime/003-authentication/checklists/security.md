# Security Checklist — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Created:** 2026-04-11T00:00:00Z
> **Final Validation:** 2026-04-11 — All items verified at closure

## Authentication Security

- [x] Passwords hashed with bcrypt (Laravel default, cost factor 12)
- [x] Password minimum length: 8 characters with complexity rules
- [x] API tokens expire after 24 hours (sanctum.php expiration: 1440)
- [x] All tokens revoked on logout (not just current token)
- [x] All tokens revoked on password reset
- [x] Password reset tokens expire after 60 minutes
- [x] Email verification uses signed URLs (tamper-proof)

## Rate Limiting

- [x] Login: 5 attempts per minute per IP
- [x] Registration: 5 attempts per minute per IP
- [x] Forgot password: 3 attempts per minute per IP
- [x] Resend verification: 1 per minute per user
- [x] Rate limit responses return 429 with `Retry-After` header

## Input Validation

- [x] Email validated (format, uniqueness on register)
- [x] Password validated (min 8, confirmed)
- [x] Name validated (required, max 255, string)
- [x] Phone validated (optional, format check)
- [x] No role escalation — registration always creates `customer`
- [x] Reset password token validated against database

## Data Protection

- [x] Password never returned in API responses
- [x] `remember_token` never returned in API responses
- [x] Token hashes stored in DB (Sanctum default), plain text returned only once on creation
- [x] `email_verified_at` exposed only to the authenticated user
- [x] User enumeration mitigated on forgot-password (always return success message)

## Frontend Security

- [x] Token stored in cookie (not localStorage)
- [x] Secure flag on cookie in production
- [x] SameSite=Lax on auth cookie
- [x] No token exposed in URL query parameters
- [x] CSRF protection via Sanctum token mechanism

## Logging & Monitoring

- [x] Failed login attempts logged with IP for brute-force detection
- [x] Successful logins logged
- [x] Password reset requests logged
- [x] Account deactivation attempts logged
- [x] No sensitive data (passwords, tokens) in log entries
