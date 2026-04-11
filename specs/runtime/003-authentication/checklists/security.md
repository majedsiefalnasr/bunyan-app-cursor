# Security Checklist — Authentication

> **Phase:** 01_PLATFORM_FOUNDATION > **Created:** 2026-04-11T00:00:00Z

## Authentication Security

- [ ] Passwords hashed with bcrypt (Laravel default, cost factor 12)
- [ ] Password minimum length: 8 characters with complexity rules
- [ ] API tokens expire after 24 hours (sanctum.php expiration: 1440)
- [ ] All tokens revoked on logout (not just current token)
- [ ] All tokens revoked on password reset
- [ ] Password reset tokens expire after 60 minutes
- [ ] Email verification uses signed URLs (tamper-proof)

## Rate Limiting

- [ ] Login: 5 attempts per minute per IP
- [ ] Registration: 5 attempts per minute per IP
- [ ] Forgot password: 3 attempts per minute per email
- [ ] Resend verification: 1 per minute per user
- [ ] Rate limit responses return 429 with `Retry-After` header

## Input Validation

- [ ] Email validated (format, uniqueness on register)
- [ ] Password validated (min 8, confirmed)
- [ ] Name validated (required, max 255, string)
- [ ] Phone validated (optional, format check)
- [ ] No role escalation — registration always creates `customer`
- [ ] Reset password token validated against database

## Data Protection

- [ ] Password never returned in API responses
- [ ] `remember_token` never returned in API responses
- [ ] Token hashes stored in DB (Sanctum default), plain text returned only once on creation
- [ ] `email_verified_at` exposed only to the authenticated user
- [ ] User enumeration mitigated on forgot-password (always return success message)

## Frontend Security

- [ ] Token stored in HttpOnly cookie (not localStorage)
- [ ] Secure flag on cookie in production
- [ ] SameSite=Lax on auth cookie
- [ ] No token exposed in URL query parameters
- [ ] CSRF protection via Sanctum token mechanism

## Logging & Monitoring

- [ ] Failed login attempts logged with IP for brute-force detection
- [ ] Successful logins logged
- [ ] Password reset requests logged
- [ ] Account deactivation attempts logged
- [ ] No sensitive data (passwords, tokens) in log entries
