# STAGE_05: Error Handling & Logging — Security Checklist

**Phase:** 01_PLATFORM_FOUNDATION  
**Generated:** 2026-04-11  
**Status:** SPECIFYING  
**Purpose:** Validate RBAC error filtering, data sanitization, and credential protection

---

## 1. RBAC Error Detail Filtering

**Objective:** Ensure error responses don't leak information based on user role.

### 1.1 Role-Based Visibility Matrix

- [ ] **Customer Role:**

  - [ ] Can see: error code, human message, validation details, resource type (generic)
  - [ ] Cannot see: stack trace, internal error cause, database details, full request context
  - [ ] Test: POST /api/v1/projects with invalid data as Customer → sees field errors only
  - [ ] Test: Try accessing admin-only resource → sees generic 403, not admin paths

- [ ] **Contractor Role:**

  - [ ] Can see: error code, human message, validation details, project context (own projects)
  - [ ] Cannot see: stack trace, other contractor's data, payment details not owned
  - [ ] Test: POST payment without funds → sees "payment failed", not internal reason
  - [ ] Test: Try accessing other contractor's project → sees generic 404, not "contractor_id mismatch"

- [ ] **Supervising Architect Role:**

  - [ ] Can see: error code, human message, validation details, project oversight context
  - [ ] Cannot see: stack trace, database internals, raw Eloquent exceptions
  - [ ] Test: Invalid workflow transition → sees "WORKFLOW_INVALID_TRANSITION", allowed transitions
  - [ ] Test: Database error → sees generic "SERVER_ERROR"

- [ ] **Field Engineer Role:**

  - [ ] Can see: error code, human message, validation details
  - [ ] Cannot see: stack trace, financial details, approval workflows
  - [ ] Test: Submit invalid field report → sees field validation messages only

- [ ] **Admin Role:**
  - [ ] In development/staging: can see stack trace, exception class, full context
  - [ ] In production: cannot see stack trace (same as other roles)
  - [ ] Test: Admin in dev sees "UserModel::find() failed, line 42", other roles don't
  - [ ] Test: Admin in prod sees generic "SERVER_ERROR" like other roles

### 1.2 Error Code Visibility

- [ ] VALIDATION_ERROR → all roles see field details
- [ ] AUTH_INVALID_CREDENTIALS → all roles see message (username not exposed)
- [ ] AUTH_TOKEN_EXPIRED → all roles see message (no internal token format)
- [ ] AUTH_UNAUTHORIZED → all roles see message (no missing header details)
- [ ] RBAC_ROLE_DENIED → all roles see code + message (no system roles exposed)
- [ ] RESOURCE_NOT_FOUND → all roles see generic message (no resource exists confirmation)
- [ ] WORKFLOW_INVALID_TRANSITION → role-specific allowed_transitions visibility
- [ ] WORKFLOW_PREREQUISITES_UNMET → role-specific prerequisites visibility
- [ ] PAYMENT_FAILED → all roles see message (payment method NOT exposed)
- [ ] RATE_LIMIT_EXCEEDED → all roles see Retry-After header
- [ ] SERVER_ERROR → all non-admin see generic message (admin sees details in dev only)
- [ ] SERVICE_UNAVAILABLE → all roles see generic message (no internal service names)

### 1.3 Blocked Information

- [ ] Stack traces never in production response (even for admin)
- [ ] Database table names never exposed
- [ ] Database column names never exposed
- [ ] Query details never exposed (SQL, parameters)
- [ ] Internal service names never exposed
- [ ] File paths never exposed
- [ ] Lambda/function names never exposed (if serverless)
- [ ] Third-party API details never exposed
- [ ] Environment variables never exposed
- [ ] API keys/secrets never exposed
- [ ] User emails not confirmed via 404 response (no "user not found")

---

## 2. Data Sanitization in Error Responses

**Objective:** Ensure error details don't contain unsanitized user input.

### 2.1 Validation Error Details

- [ ] Field names sanitized (no HTML/JavaScript):

  - [ ] Test: Input field name `<script>alert(1)</script>` → sanitized in error
  - [ ] Test: Input field name with unicode → properly encoded in JSON

- [ ] Field error messages sanitized:

  - [ ] Test: Validation message with user input interpolation → HTML-encoded
  - [ ] Example: "Field name must not contain <script>" → should be escaped in JSON response

- [ ] Nested object field paths sanitized:
  - [ ] Test: Path like `user.profile.avatar_url` → properly escaped in JSON
  - [ ] Test: Unicode in field paths → properly encoded

### 2.2 Resource Details in 404 Errors

- [ ] Resource identifier properly escaped:

  - [ ] Test: 404 response with `"id": "123<script>alert(1)</script>"` → ID escaped
  - [ ] Test: Resource type properly escaped

- [ ] Resource metadata not exposing sensitive info:
  - [ ] Test: 404 doesn't include "soft_deleted_at" timestamp (if resource is soft-deleted)
  - [ ] Test: 404 doesn't include user ownership details

### 2.3 RBAC Details in 403 Errors

- [ ] Role names properly formatted:

  - [ ] Test: Role enum values used (not raw user input)
  - [ ] Test: "required_role" field contains predefined enum value only

- [ ] Details don't leak other users' roles:
  - [ ] Test: 403 shows only "your_role" and "required_role" (not other users' roles)

---

## 3. Token & Credential Protection

**Objective:** Ensure auth tokens, passwords, and API keys never logged or exposed.

### 3.1 Authentication Error Handling

- [ ] AUTH_INVALID_CREDENTIALS error doesn't expose:

  - [ ] Which credential was wrong (username vs password)
  - [ ] Whether user exists in system
  - [ ] Password hash format
  - [ ] Attempt count/lockout details

- [ ] AUTH_TOKEN_EXPIRED error doesn't expose:

  - [ ] Token format or structure
  - [ ] Token expiration time
  - [ ] Refresh token details

- [ ] AUTH_UNAUTHORIZED error doesn't expose:
  - [ ] Missing header name in response
  - [ ] Token format expectations

### 3.2 Logging Credential Safety

- [ ] Authorization header never logged:

  - [ ] Request logging middleware (section 3.4) skips `Authorization` header
  - [ ] Test: Check logs don't contain `Bearer <token>`

- [ ] Request body passwords never logged:

  - [ ] Test: POST /api/v1/auth/login with password → logs show `password: [REDACTED]`
  - [ ] Test: POST /api/v1/users with password → logs show `password: [REDACTED]`

- [ ] API keys never logged:
  - [ ] Payment service API keys never in logs
  - [ ] Third-party credentials never in logs

### 3.3 Error Log Sanitization

- [ ] Exception messages don't expose tokens:

  - [ ] Test: If exception thrown with token in message → message sanitized
  - [ ] Example: "Token abc123... invalid" → logged as "Token [REDACTED] invalid"

- [ ] Stack traces don't expose credentials:
  - [ ] Stack traces only visible in dev/staging
  - [ ] If credentials in variable names, sanitized in dev traces too

---

## 4. Sensitive Data Masking

**Objective:** Mask or redact sensitive data in error responses and logs.

### 4.1 Payment Information

- [ ] Credit card numbers never exposed:

  - [ ] PAYMENT_FAILED error shows only last 4 digits
  - [ ] Test: Payment error response shows `card_last_4: "1234"`, not full number

- [ ] Payment method details masked:

  - [ ] Test: "visa" or "mastercard" OK, full number NOT OK
  - [ ] Test: Expiry month/year NOT exposed

- [ ] Payment gateway responses sanitized:
  - [ ] Stripe/PayPal error messages sanitized before returning to client
  - [ ] Internal transaction IDs may be exposed (system-generated, not user-input)

### 4.2 Personal Information

- [ ] Email addresses partially masked:

  - [ ] In error logs: `user@example.com` → `us...@example.com`
  - [ ] In error responses: email NOT included unless necessary

- [ ] Phone numbers partially masked:

  - [ ] In logs: `+966500000000` → `+966...0000`
  - [ ] In error responses: phone NOT included unless necessary

- [ ] SSN/ID numbers fully redacted:
  - [ ] Never in error responses
  - [ ] Never in error logs
  - [ ] Never in stack traces

### 4.3 User Context Masking

- [ ] User IDs can be exposed (system-generated):

  - [ ] Test: Error logs include `user_id: 42` (OK)

- [ ] User roles can be exposed:

  - [ ] Test: Error logs include `user_role: "contractor"` (OK)

- [ ] User emails NOT exposed in error responses:
  - [ ] Test: VALIDATION_ERROR response doesn't include user's email
  - [ ] Test: 403 response doesn't include user's email

---

## 5. CSRF & XSS Protection

**Objective:** Prevent error responses from enabling CSRF or XSS attacks.

### 5.1 JSON Response Escaping

- [ ] All error messages properly JSON-encoded:

  - [ ] Test: Error message with special chars → properly escaped in JSON
  - [ ] Example: `"message": "Field \"name\" required"` → escaped quotes

- [ ] HTML special chars escaped:
  - [ ] Test: Validation error with `<`, `>`, `&`, `"`, `'` → properly escaped in JSON
  - [ ] Frontend should NOT use `v-html` for error messages (use `{{ message }}`)

### 5.2 CSRF Token Handling

- [ ] CSRF errors don't expose token format:

  - [ ] CSRF validation fails → generic 419 or 400 error (not "invalid token")
  - [ ] Error doesn't expose CSRF mechanism details

- [ ] New CSRF tokens issued after error:
  - [ ] If CSRF validation fails → client gets fresh token in response header
  - [ ] Client can retry with new token

### 5.3 XSS Prevention in Error UI

- [ ] Error boundary component (section 4.3) doesn't use `v-html`:

  - [ ] Test: Component uses `{{ message }}`, not `v-html="message"`
  - [ ] Test: Error page doesn't allow HTML in error messages

- [ ] Toast notifications escape content:
  - [ ] Test: Error message with HTML tags → rendered as text, not HTML
  - [ ] Toast library (Nuxt UI) should escape by default

---

## 6. Rate Limiting & DoS Protection

**Objective:** Ensure rate limiting errors don't enable abuse.

### 6.1 Rate Limit Errors

- [ ] RATE_LIMIT_EXCEEDED (429) returned correctly:

  - [ ] After too many requests → 429 response
  - [ ] Retry-After header present (section 3.2, line 330)
  - [ ] Error message doesn't expose rate limit threshold

- [ ] Rate limit details not leaked:
  - [ ] Test: Error doesn't say "limit is 100 requests/hour"
  - [ ] Test: Error doesn't show current request count
  - [ ] Error shows only "Retry-After" time (e.g., 60s)

### 6.2 DoS Prevention

- [ ] Error responses don't generate database queries:

  - [ ] 404 response doesn't query database to confirm non-existence
  - [ ] Validation errors don't execute additional queries
  - [ ] Error logging async (no blocking on slow database)

- [ ] Error response size reasonable:
  - [ ] Error responses < 10KB (no bloat attacks)
  - [ ] Validation errors cap details at reasonable size
  - [ ] Stack traces never included (would balloon response)

---

## 7. Audit Trail & Compliance

**Objective:** Ensure error handling complies with audit and compliance requirements.

### 7.1 Security Event Logging

- [ ] Authentication failures logged:

  - [ ] Failed login attempts logged with user (if exists) and IP
  - [ ] Invalid token attempts logged with IP
  - [ ] Test: Login 3x with wrong password → logs show 3 failed attempts

- [ ] Authorization failures logged:

  - [ ] RBAC_ROLE_DENIED logged with user_id, role, resource_id
  - [ ] Test: Contractor tries to delete customer's project → logged

- [ ] Sensitive operation errors logged:
  - [ ] Payment failures logged with transaction_id, amount, method
  - [ ] Password reset errors logged (without password)
  - [ ] API key access errors logged

### 7.2 Data Breach Response

- [ ] Error logs retained for investigation:

  - [ ] CRITICAL/ERROR logs retained for 180 days (section 5.2, line 1023)
  - [ ] Older logs archived/deleted per retention policy

- [ ] Error response correlation:
  - [ ] Each error response includes correlation_id (section 3.3)
  - [ ] Logs linked by same correlation_id
  - [ ] Support team can trace user's session from error

---

## 8. Third-Party Integration Safety

**Objective:** Ensure third-party error responses don't expose secrets.

### 8.1 Payment Gateway Errors

- [ ] Stripe errors sanitized:

  - [ ] Stripe error responses don't expose API keys
  - [ ] Stripe error codes mapped to generic Bunyan codes
  - [ ] Stripe customer IDs NOT exposed to client

- [ ] PayPal errors sanitized:
  - [ ] PayPal error responses don't expose credentials
  - [ ] PayPal error details filtered before client response

### 8.2 Email Service Errors

- [ ] Email service API errors sanitized:
  - [ ] SendGrid/Mailgun errors don't expose API keys
  - [ ] Email sending failures show generic error
  - [ ] Recipient list NOT exposed if send fails

### 8.3 SMS Service Errors

- [ ] Twilio/vonage errors sanitized:
  - [ ] SMS sending errors don't expose API credentials
  - [ ] Phone numbers partially masked in errors
  - [ ] Account SIDs NOT exposed

---

## 9. Completion Criteria

**All security checklist items must pass before STAGE_05 security audit:**

- [ ] All role-based visibility rules enforced
- [ ] No stack traces in production
- [ ] No credentials logged or exposed
- [ ] All sensitive data masked/redacted
- [ ] No CSRF/XSS vectors in error responses
- [ ] Rate limit information not leaked
- [ ] Security events auditable
- [ ] Third-party errors sanitized
- [ ] RBAC filtering working for all error types
- [ ] Validation details don't leak system internals

---

## 10. Testing Commands

```bash
# Run security-focused error tests
composer run test -- tests/Feature/Errors/SecurityTest.php

# Check logs for sensitive data leakage
grep -r "password\|token\|Authorization\|credit_card" storage/logs/

# Audit error responses in development
curl -X POST http://localhost:8000/api/v1/projects \
  -H "Content-Type: application/json" \
  -d '{"invalid": "payload"}' | jq '.error'

# Verify RBAC filtering (as different roles)
curl -X GET http://localhost:8000/api/v1/projects/999 \
  -H "Authorization: Bearer {admin_token}" | jq '.error'
```

---

**Last Updated:** 2026-04-11  
**Owner:** Platform Engineering  
**Related:** STAGE_05_ERROR_HANDLING.md, STAGE_05_ERROR_HANDLING/spec.md
