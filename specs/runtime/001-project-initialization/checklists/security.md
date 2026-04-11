# STAGE_01: Project Initialization — Security Hardening Checklist

**Phase:** 01_PLATFORM_FOUNDATION  
**Generated:** 2026-04-10  
**Status:** SPECIFYING

---

## Executive Summary

This checklist ensures Bunyan Platform meets OWASP Top 10 and industry-standard security controls from Day 1. All items are mandatory for STAGE_01 completion.

---

## 1. Authentication & Session Security

### 1.1 Laravel Sanctum Configuration

- [ ] Sanctum configured with token-based API auth (no session-based cookies for API)
- [ ] Token expiration set to **7 days** (configurable via `SANCTUM_EXPIRATION` env)
- [ ] Token refresh endpoint available: `POST /api/v1/auth/refresh`
- [ ] Tokens stored securely in database with hashed value
- [ ] CSRF protection: SPA sends `X-CSRF-Token` header (optional for stateless API)
- [ ] Stateful domains configured: `SANCTUM_STATEFUL_DOMAINS=localhost:3000,yourdomain.com`
- [ ] Token leak mitigation: rotate on logout

### 1.2 Password Security

- [ ] All passwords hashed with **bcrypt** (Laravel default Hash::make())
- [ ] Minimum password length: **8 characters**
- [ ] Password validation rules in `Auth/RegisterRequest`:
  - [ ] `password` — required, min:8, confirmed, regex:/[A-Z]/ (at least one uppercase)
  - [ ] `password_confirmation` — required, same:password
- [ ] Prevent password reuse: check previous hashes (implement in `AuthService`)
- [ ] Password reset via email with **time-limited tokens** (15 minutes)
- [ ] Rate limiting on password reset endpoint: **max 3 requests per hour per email**

### 1.3 Session & Token Hijacking Prevention

- [ ] Token binding to user-agent: store `User-Agent` hash in token metadata
- [ ] Token binding to IP (optional, may break mobile apps—use with caution)
- [ ] Logout revokes all active tokens: `Auth::guard('sanctum')->revoke()`
- [ ] Suspicious login detection: log failed attempts, alert on 5+ failures in 15 min
- [ ] Force re-authentication on: password change, role change, critical operations

### 1.4 Multi-Factor Authentication (MFA) Placeholder

- [ ] Create `backend/app/Services/MfaService.php` (stub for Phase 02)
- [ ] Note: MFA implementation deferred to Phase 02
- [ ] Document MFA strategy in `docs/architecture/ADR/mfa-strategy.md`

---

## 2. Authorization & Access Control (RBAC)

### 2.1 Policy Enforcement

- [ ] **All protected routes require `can:` middleware** (no exceptions)
  - Example: `Route::post('/projects', [ProjectController::class, 'store'])->middleware('can:create,App\Models\Project')`
- [ ] All policies implement `viewAny()`, `view()`, `create()`, `update()`, `delete()` methods
- [ ] Policies use `$user->role` enum to determine permissions (not string comparison)
- [ ] Implicit policy behavior: unauthorized returns **403 Forbidden** (not error message)
- [ ] Policy deny/allow logged for audit trail

### 2.2 Role-Based Access Control (RBAC)

- [ ] Five roles enforced: `Customer`, `Contractor`, `SupervisingArchitect`, `FieldEngineer`, `Admin`
- [ ] Role assignment at registration time (user selects role, admin confirms)
- [ ] Role changes logged with approval workflow (admin only)
- [ ] Role-based dashboard: each role has dedicated dashboard path
  - [ ] Customer: `/dashboard/customer`
  - [ ] Contractor: `/dashboard/contractor`
  - [ ] Supervising Architect: `/dashboard/architect`
  - [ ] Field Engineer: `/dashboard/field-engineer`
  - [ ] Admin: `/dashboard/admin`
- [ ] Frontend middleware redirects unauthenticated to login, unauthorized to 403 page

### 2.3 Cross-Tenant Data Isolation

- [ ] Projects filtered by customer ownership:
  - Customers see only their own projects
  - Contractors see assigned projects
  - Architects see supervised projects
  - Admins see all
- [ ] Phases/Tasks inherit project visibility (no cross-project data leaks)
- [ ] Reports visible only to: creator, assigned contractor, supervising architect, admin
- [ ] Transactions visible only to: owner, admin

### 2.4 Privilege Escalation Prevention

- [ ] Users cannot self-assign higher roles (admin-only)
- [ ] Role validation on every request: `auth:sanctum` middleware re-verifies role
- [ ] Token claims checked against current user role (detect role revocation mid-session)
- [ ] Admin endpoints protected by explicit admin policy check

---

## 3. Input Validation & Sanitization

### 3.1 Form Request Validation (Server-Side)

- [ ] **All user inputs validated server-side** (never trust frontend validation alone)
- [ ] Form Requests use Laravel Validation facade with Zod/Spatie rules
- [ ] Input types enforced:
  - [ ] Emails: `email` rule + DNS validation
  - [ ] URLs: `url` rule
  - [ ] Numbers: `numeric`, `integer`, `min`, `max`
  - [ ] Dates: `date`, `date_format:Y-m-d`
  - [ ] Enums: `in:value1,value2` or Enum::cases()
- [ ] String inputs trimmed and HTML-escaped before storage
- [ ] File uploads validated: type, size, dimensions (images)

### 3.2 File Upload Security

- [ ] Allowed file types: `image/jpeg`, `image/png`, `image/webp`, `video/mp4`, `video/quicktime` only
- [ ] File size limits enforced: **5 MB images, 50 MB videos**
- [ ] Uploaded files renamed to random UUIDs (prevent directory traversal)
- [ ] Files stored outside webroot or behind signed URLs
- [ ] File MIME type verified server-side (not by extension)
- [ ] Images re-encoded on upload (strip embedded EXIF/metadata)
- [ ] Video files NOT auto-played (user must click play)
- [ ] Antivirus scanning placeholder: integrate ClamAV or equivalent in Phase 02

### 3.3 SQL Injection Prevention

- [ ] Only Eloquent ORM used (no raw SQL queries)
- [ ] Parameterized queries enforced via Eloquent `->where()` bindings
- [ ] No user input concatenated into queries
- [ ] Repositories enforce prepared statements

### 3.4 Cross-Site Scripting (XSS) Prevention

- [ ] User input HTML-escaped on output: `{{ $variable }}` in Blade (auto-escapes)
- [ ] Frontend Vue templates auto-escape: `{{ user.name }}`
- [ ] Rich text inputs sanitized via `purify()` (HTML Purifier)
- [ ] No `{!! !!}` (unescaped) output without review
- [ ] Content Security Policy (CSP) header set: `Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'`

### 3.5 Cross-Site Request Forgery (CSRF) Protection

- [ ] CSRF tokens generated per session
- [ ] All state-changing requests (POST, PATCH, DELETE) require CSRF token or `Authorization` header
- [ ] Laravel VerifyCsrfToken middleware enforced (excluded for API routes if using token auth)
- [ ] SPA sends `X-CSRF-Token` header for form submissions (not covered in API since we use Sanctum tokens)

---

## 4. Data Protection & Privacy

### 4.1 Encryption at Rest

- [ ] Sensitive fields encrypted in database:
  - [ ] `users.email` (optional, for GDPR compliance)
  - [ ] Transaction payment methods (if PCI-DSS required)
- [ ] Use Laravel `Crypt` facade or `app/Casts/Encrypted.php` attribute casts
- [ ] Encryption key: `APP_KEY` env variable (generated via `php artisan key:generate`)
- [ ] Key rotation strategy documented (Phase 02)

### 4.2 Encryption in Transit

- [ ] HTTPS enforced in production: `APP_URL=https://`
- [ ] HSTS header set: `Strict-Transport-Security: max-age=31536000`
- [ ] No mixed content (HTTP assets on HTTPS pages)
- [ ] TLS 1.2+ required

### 4.3 Personal Data Handling (GDPR/LGPD Compliance)

- [ ] User consent collected for data processing (at registration)
- [ ] Privacy policy linked on registration page
- [ ] Data export endpoint: `GET /api/v1/me/export` (GDPR right to access)
- [ ] Account deletion endpoint: `DELETE /api/v1/me` (GDPR right to be forgotten)
- [ ] Deletion includes: user record, projects (soft delete), orders, transactions (anonymized)
- [ ] Audit log retention: **2 years** (configurable)
- [ ] PII never logged in application logs

### 4.4 Backup & Disaster Recovery

- [ ] Database backups daily (automated)
- [ ] Backups encrypted at rest
- [ ] Backup restoration tested monthly
- [ ] Backup retention: **30 days**

---

## 5. Rate Limiting & DoS Protection

### 5.1 API Rate Limiting

- [ ] Rate limiter middleware: Laravel `Throttle` middleware
- [ ] Public endpoints (auth): **5 requests per minute per IP**
- [ ] Protected endpoints (authenticated): **60 requests per minute per user**
- [ ] Admin endpoints: **100 requests per minute per user**
- [ ] Rate limit headers in response: `X-RateLimit-Limit`, `X-RateLimit-Remaining`, `X-RateLimit-Reset`
- [ ] Adaptive rate limiting: increase for verified users, decrease for suspicious activity

### 5.2 Brute Force Protection

- [ ] Login endpoint rate limited: **5 failed attempts = 15 minute lockout**
- [ ] Lockout notification emailed to user
- [ ] Admin unlock endpoint: `POST /api/v1/admin/users/{id}/unlock`

### 5.3 DDoS Mitigation

- [ ] CloudFlare or CDN integration (Phase 02)
- [ ] WAF rules: block suspicious patterns
- [ ] Connection limits per IP
- [ ] Queue jobs for heavy operations (reports, exports)

---

## 6. Logging, Monitoring & Audit Trail

### 6.1 Security Event Logging

- [ ] All authentication events logged:
  - [ ] Successful login: user ID, IP, timestamp
  - [ ] Failed login: email, IP, timestamp
  - [ ] Logout: user ID, timestamp
  - [ ] Token refresh: user ID, old token ID, new token ID
- [ ] All authorization events logged:
  - [ ] Permission denied: user ID, action, resource, timestamp
  - [ ] Role change: admin ID, affected user ID, old role, new role
- [ ] All data modification events logged:
  - [ ] Project created/updated/deleted: user ID, changes
  - [ ] Payment processed: user ID, amount, method
  - [ ] Report submitted: user ID, task ID, file count
- [ ] Log retention: **1 year** (configurable)
- [ ] Logs stored in database table `audit_logs` with indexed columns: `user_id`, `entity_type`, `action`, `created_at`

### 6.2 Activity Logging (Non-Repudiation)

- [ ] Each audit log includes:
  - [ ] `user_id` — who performed action
  - [ ] `entity_type` — what was modified (Project, Phase, Task, etc.)
  - [ ] `entity_id` — specific resource ID
  - [ ] `action` — created, updated, deleted, approved
  - [ ] `changes` — JSON diff of old vs. new values
  - [ ] `ip_address` — source IP
  - [ ] `user_agent` — browser/client info
  - [ ] `timestamp` — UTC created_at
- [ ] Immutable audit log (no modifications after creation)

### 6.3 Error Logging

- [ ] All exceptions logged (not returned to client for security)
- [ ] Sensitive info (passwords, tokens, API keys) excluded from error logs
- [ ] Error logs include stack trace, request context, user ID
- [ ] Errors monitored in real-time via Sentry/similar tool (Phase 02)

### 6.4 Compliance Reporting

- [ ] Generate audit report: `GET /api/v1/admin/audit-log?from=date&to=date`
- [ ] Export audit log to CSV for compliance reviews
- [ ] Compliance dashboard: login heatmap, failed auth attempts, role changes

---

## 7. Infrastructure & Deployment Security

### 7.1 Environment Configuration

- [ ] `APP_KEY` set and unique per environment
- [ ] `APP_DEBUG=false` in production
- [ ] Database credentials never committed (use `.env` or secrets manager)
- [ ] API keys (external services) stored in `.env` (not in code)
- [ ] `.env.example` includes only safe placeholder values

### 7.2 Database Security

- [ ] Database user has minimal required permissions (not `SUPER`)
- [ ] Separate database users for read/write operations (Phase 02)
- [ ] Database backups encrypted
- [ ] SQL injection testing included in security audit checklist

### 7.3 Server Configuration

- [ ] PHP security settings:
  - [ ] `expose_php=Off` (don't expose version)
  - [ ] `error_reporting=E_ALL & ~E_DEPRECATED & ~E_STRICT`
  - [ ] `display_errors=Off` (log, don't show)
  - [ ] `max_file_uploads=10`
- [ ] Web server security:
  - [ ] Nginx: `server_tokens off`, limit request size, disable directory listing
  - [ ] Apache: disable modules (mod_dir, mod_autoindex), hide version

### 7.4 Dependencies & Patch Management

- [ ] `composer.json` locked to specific versions (use `composer.lock`)
- [ ] Dependencies scanned for vulnerabilities: `composer audit`
- [ ] npm dependencies locked: `package-lock.json` committed
- [ ] npm security scan: `npm audit`
- [ ] Update schedule: monthly security patches, quarterly feature updates

---

## 8. API Security Headers

### 8.1 Security Response Headers

- [ ] `X-Content-Type-Options: nosniff` (prevent MIME sniffing)
- [ ] `X-Frame-Options: DENY` (prevent clickjacking)
- [ ] `X-XSS-Protection: 1; mode=block` (XSS filter)
- [ ] `Referrer-Policy: strict-origin-when-cross-origin`
- [ ] `Permissions-Policy: geolocation=(), microphone=(), camera=()` (disable dangerous features)
- [ ] `Content-Security-Policy: default-src 'self'` (restrict script/style sources)

### 8.2 CORS Configuration

- [ ] CORS allowed origins: **only known frontend domains**
  - Development: `http://localhost:3000`
  - Production: `https://yourdomain.com`
- [ ] Allowed methods: `GET, POST, PATCH, DELETE, OPTIONS`
- [ ] Allowed headers: `Content-Type, Authorization, X-CSRF-Token`
- [ ] Credentials: `true` (cookies/tokens sent)
- [ ] Max age: `3600` seconds

---

## 9. API Documentation & Security Audit

### 9.1 API Documentation

- [ ] OpenAPI/Swagger spec generated: `docs/api/openapi.yaml`
- [ ] Security schemes documented: `type: http, scheme: bearer, bearerFormat: JWT` (or Sanctum token)
- [ ] Rate limiting documented
- [ ] Example requests/responses include auth headers

### 9.2 Security Testing

- [ ] OWASP Top 10 checklist completed (documented in testing report)
- [ ] Dependency vulnerability scan: `composer audit`, `npm audit`
- [ ] Static analysis: `phpstan analyse` with strict rules
- [ ] SQL injection tests: attempt malicious input on all query endpoints
- [ ] XSS tests: attempt script injection on user input fields
- [ ] CSRF tests: attempt requests without CSRF token

### 9.3 Security Audit Report

- [ ] Document findings in `specs/runtime/001-project-initialization/reports/SECURITY_AUDIT.md`
- [ ] Include: vulnerabilities found, mitigations applied, remaining risks
- [ ] Risk rating: Critical/High/Medium/Low
- [ ] Remediation plan with timelines

---

## 10. Checklist Completion Summary

**All items are mandatory for STAGE_01 completion:**

- [ ] **Authentication & Session:** 5 items (Sanctum, passwords, token security, MFA placeholder)
- [ ] **Authorization & RBAC:** 4 items (policies, roles, data isolation, privilege escalation)
- [ ] **Input Validation:** 5 items (form requests, file uploads, SQL injection, XSS, CSRF)
- [ ] **Data Protection:** 4 items (encryption at rest/in-transit, GDPR, backup)
- [ ] **Rate Limiting & DoS:** 3 items (API rate limiting, brute force, DDoS)
- [ ] **Logging & Audit:** 4 items (security events, activity logging, error logging, compliance)
- [ ] **Infrastructure:** 4 items (environment config, database, server, dependencies)
- [ ] **API Security:** 2 items (headers, CORS)
- [ ] **Documentation & Testing:** 3 items (API docs, security testing, audit report)

**Total Security Checklist Items: 34**

---

**Generated by:** CLARIFY Step  
**Date:** 2026-04-10  
**Status:** ACTIVE (Ready for PLAN → IMPLEMENT)
