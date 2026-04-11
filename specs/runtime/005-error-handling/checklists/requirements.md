# STAGE_05: Error Handling & Logging — Requirements & Implementation Checklists

**Phase:** 01_PLATFORM_FOUNDATION  
**Generated:** 2026-04-11  
**Status:** SPECIFYING

---

## Table of Contents

1. [Error Code Registry & Contract Checklist](#1-error-code-registry--contract-checklist)
2. [Backend Exception Handler Checklist](#2-backend-exception-handler-checklist)
3. [Backend Middleware Checklist](#3-backend-middleware-checklist)
4. [Backend Services Error Handling Checklist](#4-backend-services-error-handling-checklist)
5. [Structured Logging Checklist](#5-structured-logging-checklist)
6. [Frontend API Interceptor Checklist](#6-frontend-api-interceptor-checklist)
7. [Frontend Error Notifications Checklist](#7-frontend-error-notifications-checklist)
8. [Frontend Error Boundary & Pages Checklist](#8-frontend-error-boundary--pages-checklist)
9. [Arabic/RTL Support Checklist](#9-arabicrtl-support-checklist)
10. [Testing & Validation Checklist](#10-testing--validation-checklist)

---

## 1. Error Code Registry & Contract Checklist

**Objective:** Establish a standardized, globally unique error code system with HTTP status mapping.

### 1.1 Error Code Definitions

- [ ] Create `backend/app/Enums/ErrorCode.php` PHP enum with all standardized error codes:
  - [ ] `VALIDATION_ERROR` (422)
  - [ ] `AUTH_INVALID_CREDENTIALS` (401)
  - [ ] `AUTH_TOKEN_EXPIRED` (401)
  - [ ] `AUTH_UNAUTHORIZED` (401)
  - [ ] `RBAC_ROLE_DENIED` (403)
  - [ ] `RESOURCE_NOT_FOUND` (404)
  - [ ] `WORKFLOW_INVALID_TRANSITION` (422)
  - [ ] `WORKFLOW_PREREQUISITES_UNMET` (422)
  - [ ] `PAYMENT_FAILED` (422)
  - [ ] `RATE_LIMIT_EXCEEDED` (429)
  - [ ] `SERVER_ERROR` (500)
  - [ ] `SERVICE_UNAVAILABLE` (503)

- [ ] Create `backend/app/Constants/ErrorCodeHttp.php` mapping error codes to HTTP status:
  - [ ] All 12 codes mapped with immutable properties
  - [ ] Each code includes human-readable description
  - [ ] Each code includes severity level (Warning, Error)

- [ ] Create frontend error code constants in `frontend/types/errors.ts`:
  - [ ] Export error code enum matching backend
  - [ ] Add error messages in Arabic for each code

### 1.2 Error Response Contract

- [ ] Document unified error response structure in spec.md (✓ Done)
- [ ] Document success response structure in spec.md (✓ Done)
- [ ] Create example error responses for each error type (✓ Done)
- [ ] Add error contract to `AGENTS.md` (reference existing)

### 1.3 Error Code Registry Documentation

- [ ] Create `backend/docs/ERROR_CODES.md` registry with:
  - [ ] Full table of all error codes
  - [ ] HTTP status mapping
  - [ ] Severity levels
  - [ ] Example scenarios
  - [ ] Recovery strategies (for frontend)

---

## 2. Backend Exception Handler Checklist

**Objective:** Implement Laravel exception handler that converts all exceptions to standardized error contract.

### 2.1 Custom Exception Classes

- [ ] Create exception hierarchy in `backend/app/Exceptions/`:
  - [ ] `DomainException` (base for business logic)
  - [ ] `ValidationException` (field-level validation)
  - [ ] `InvalidStateTransitionException` (workflow errors)
    - [ ] With `fromState`, `toState`, `allowedTransitions` properties
    - [ ] Implement `getErrorCode()`, `getHttpStatus()`, `getDetails()` methods
  - [ ] `InsufficientPermissionException` (authorization)
  - [ ] `ResourceNotFoundException` (404)
  - [ ] `PaymentFailedException` (payment errors)
  - [ ] `WorkflowPrerequisiteException` (workflow prerequisites)

### 2.2 Exception Handler Implementation

- [ ] Update `backend/app/Exceptions/Handler.php`:
  - [ ] Catch `ValidationException` → 422 with field details
  - [ ] Catch `AuthorizationException` → 403 with RBAC code
  - [ ] Catch `ModelNotFoundException` → 404 with resource type
  - [ ] Catch `ThrottleRequestsException` → 429 with retry-after
  - [ ] Catch custom `DomainException` subclasses → delegate to `getErrorCode()`, `getHttpStatus()`
  - [ ] Catch all other exceptions → 500 with generic message (no stack trace)
  - [ ] Log unhandled exceptions with correlation ID
  - [ ] Never expose stack traces to clients (except admin in dev)
  - [ ] Support RBAC-aware error details (filter by user role)

- [ ] Add helper methods to Handler:
  - [ ] `validationResponse()` — format validation errors
  - [ ] `authorizationResponse()` — format RBAC errors
  - [ ] `domainExceptionResponse()` — generic domain exception handler
  - [ ] `logError()` — log with correlation ID and context

### 2.3 Error Response Trait/Helper

- [ ] Create `backend/app/Http/Controllers/Api/ApiResponse.php` trait:
  - [ ] `sendSuccess($data, $message, $statusCode)` method
  - [ ] `sendError($code, $message, $details, $statusCode)` method
  - [ ] All controllers extend or use this trait

---

## 3. Backend Middleware Checklist

**Objective:** Inject correlation ID and log all API requests/responses.

### 3.1 Correlation ID Middleware

- [ ] Create `backend/app/Http/Middleware/InjectCorrelationId.php`:
  - [ ] Get `X-Correlation-ID` from request header
  - [ ] Generate UUID if not present
  - [ ] Store in `$request->attributes`
  - [ ] Register in `InjectCorrelationId::class` as first middleware in `Kernel.php`

- [ ] Test correlation ID:
  - [ ] [ ] Missing header → generates new ID
  - [ ] [ ] Provided header → uses provided ID
  - [ ] [ ] ID appears in all logs for request
  - [ ] [ ] ID returned in response header

### 3.2 Request/Response Logging Middleware

- [ ] Create `backend/app/Http/Middleware/LogApiActivity.php`:
  - [ ] Record request start time
  - [ ] After response, calculate duration in ms
  - [ ] Log with structured fields:
    - [ ] correlation_id
    - [ ] method, path
    - [ ] HTTP status code
    - [ ] duration_ms
    - [ ] user_id, user_role
    - [ ] client IP

- [ ] Register middleware in `Kernel.php`

### 3.3 Error Details Filtering Middleware (Optional)

- [ ] Create middleware to filter error details by user role:
  - [ ] Admin: full error details (stack trace in dev only)
  - [ ] Other roles: no sensitive details
  - [ ] Applied before exception handler response

---

## 4. Backend Services Error Handling Checklist

**Objective:** All business logic throws appropriate custom exceptions.

### 4.1 ProjectService Error Handling

- [ ] Throw `ValidationException` for invalid input
- [ ] Throw `InsufficientPermissionException` for role violations
- [ ] Throw `ResourceNotFoundException` for missing resources
- [ ] Throw `InvalidStateTransitionException` for invalid state changes
- [ ] All exceptions include correlation ID in context

### 4.2 WorkflowService Error Handling

- [ ] Throw `InvalidStateTransitionException` with:
  - [ ] from_state
  - [ ] to_state
  - [ ] allowed_transitions array
- [ ] Throw `WorkflowPrerequisiteException` with:
  - [ ] unmet_prerequisites array
  - [ ] completion_percentage

### 4.3 PaymentService Error Handling

- [ ] Throw `PaymentFailedException` with:
  - [ ] payment_method
  - [ ] failure_reason
  - [ ] retry_possible (boolean)

### 4.4 Generic Service Error Handling

- [ ] All services wrap database operations in try/catch
- [ ] All services use repository pattern (no direct Eloquent)
- [ ] All exceptions transformed to domain exceptions
- [ ] No HTTP concerns in service layer

---

## 5. Structured Logging Checklist

**Objective:** All logs are structured JSON with correlation IDs and context.

### 5.1 Logging Configuration

- [ ] Update `backend/config/logging.php`:
  - [ ] Add `structured` channel with JsonFormatter
  - [ ] Configure log retention (14 days daily, 90 days structured)
  - [ ] Set log level (debug/info for dev, warning+ for prod)

- [ ] Create custom formatter if needed:
  - [ ] Includes timestamp, level, message
  - [ ] Includes correlation_id from middleware
  - [ ] Includes context fields

### 5.2 Log Entry Fields

- [ ] All logs include:
  - [ ] `timestamp` (ISO 8601)
  - [ ] `level` (DEBUG, INFO, WARNING, ERROR, CRITICAL)
  - [ ] `message` (short description)
  - [ ] `correlation_id` (from middleware)

- [ ] API request logs include:
  - [ ] method, path, status, duration_ms
  - [ ] user_id, user_role
  - [ ] client IP

- [ ] Error logs include:
  - [ ] exception class name
  - [ ] error message
  - [ ] error code
  - [ ] stack trace (dev/staging only)
  - [ ] user context

### 5.3 Sensitive Data Protection

- [ ] Never log:
  - [ ] Passwords, tokens, API keys
  - [ ] Credit card numbers
  - [ ] SSN, personal identification numbers
  - [ ] Email addresses (ok to log partially for debugging)

- [ ] If sensitive data must be logged:
  - [ ] Encrypt before writing
  - [ ] Mask in logs (e.g., `****1234` for last 4 digits)

### 5.4 Performance Logging

- [ ] Database query logs (slow query threshold)
- [ ] API response time logs (threshold: >1000ms)
- [ ] Cache hit/miss logs
- [ ] External API call logs (with duration)

---

## 6. Frontend API Interceptor Checklist

**Objective:** All API calls go through a centralized interceptor that handles errors.

### 6.1 useApi Composable

- [ ] Create `frontend/composables/useApi.ts`:
  - [ ] Base URL from runtime config
  - [ ] Default headers (Accept, Accept-Language: ar)
  - [ ] Auth token injection from store
  - [ ] Correlation ID generation and injection

- [ ] Implement error handling:
  - [ ] 401 errors → call logout, redirect to login
  - [ ] 403 errors → redirect to /403 or dashboard
  - [ ] 422 validation errors → show specific field errors
  - [ ] 429 rate limit errors → show retry-after message
  - [ ] 500+ errors → show generic error message
  - [ ] Network errors → show connectivity error

- [ ] Return structured response:
  - [ ] `apiFetch()` function matching native $fetch API
  - [ ] Transparent error handling in interceptors

### 6.2 Correlation ID Generation

- [ ] Generate unique ID per request:
  - [ ] Format: `${Date.now()}_${random}`
  - [ ] Store in request headers as `X-Correlation-ID`
  - [ ] Available for logging/debugging

### 6.3 Token Management

- [ ] Get token from auth store
- [ ] Inject in `Authorization: Bearer <token>` header
- [ ] Handle token refresh/rotation
- [ ] Clear token on 401 response

---

## 7. Frontend Error Notifications Checklist

**Objective:** User-friendly error messages in toast notifications.

### 7.1 useErrorNotification Composable

- [ ] Create `frontend/composables/useErrorNotification.ts`:
  - [ ] `showErrorNotification(payload)` function
  - [ ] Map error codes to user-friendly Arabic messages
  - [ ] Determine severity (error/warning) from error code
  - [ ] Show toast with title (error code), description (message)

- [ ] Error message mapping:
  - [ ] `VALIDATION_ERROR` → "البيانات غير صحيحة"
  - [ ] `AUTH_INVALID_CREDENTIALS` → "بيانات الدخول غير صحيحة"
  - [ ] `RBAC_ROLE_DENIED` → "غير مصرح لك بهذا الإجراء"
  - [ ] `RESOURCE_NOT_FOUND` → "المورد غير موجود"
  - [ ] `SERVER_ERROR` → "حدث خطأ غير متوقع"
  - [ ] All in Arabic with English fallback

- [ ] Toast configuration:
  - [ ] Duration: 5000ms for warnings, 8000ms for errors
  - [ ] Color: red for errors, yellow for warnings
  - [ ] Position: top-right (RTL-aware)

### 7.2 Retry Logic

- [ ] Support retryable errors (VALIDATION_ERROR, RATE_LIMIT_EXCEEDED)
- [ ] Add "أعد المحاولة" button to toast
- [ ] Call provided `retryFn()` on retry click
- [ ] Show loading state during retry
- [ ] Close toast on successful retry

### 7.3 Error Details

- [ ] For validation errors: show field-level details:
  - [ ] Extract from `error.details` object
  - [ ] Display each field's error message
  - [ ] Highlight affected form fields

---

## 8. Frontend Error Boundary & Pages Checklist

**Objective:** Catch and display unhandled errors with appropriate UI.

### 8.1 Global Error Boundary Component

- [ ] Create `frontend/components/common/AppErrorBoundary.vue`:
  - [ ] Catch all unhandled errors via `onErrorCaptured()`
  - [ ] Display error message in user-friendly card
  - [ ] Show error code for debugging
  - [ ] Provide "العودة" (Back) button
  - [ ] Provide "تحديث الصفحة" (Refresh) button

- [ ] Error display:
  - [ ] Use DESIGN.md styling (shadow-as-border, Geist fonts)
  - [ ] Center card with white background
  - [ ] Icon (exclamation triangle) in red
  - [ ] Arabic heading and description

### 8.2 404 Page (frontend/pages/404.vue)

- [ ] Path: `frontend/pages/404.vue`
- [ ] Layout: `error` (custom error layout)
- [ ] Display:
  - [ ] Large "404" heading in light gray
  - [ ] "الصفحة غير موجودة" heading
  - [ ] Description: "عذرًا، الصفحة التي تبحث عنها غير موجودة أو تم حذفها"
  - [ ] Buttons: "العودة إلى لوحة التحكم" + "الصفحة الرئيسية"

### 8.3 500 Page (frontend/pages/500.vue)

- [ ] Path: `frontend/pages/500.vue`
- [ ] Layout: `error`
- [ ] Display:
  - [ ] Large "500" heading in light gray
  - [ ] "خطأ في الخادم" heading
  - [ ] Description: "حدث خطأ غير متوقع. فريقنا يعمل على حل المشكلة"
  - [ ] Buttons: "أعد المحاولة" + "العودة"

### 8.4 403 Page (frontend/pages/403.vue)

- [ ] Path: `frontend/pages/403.vue`
- [ ] Layout: `error`
- [ ] Middleware: `auth` (require authentication)
- [ ] Display:
  - [ ] Large "403" heading in light gray
  - [ ] "وصول مرفوض" heading
  - [ ] Description: "ليس لديك صلاحيات للوصول إلى هذه الموارد"
  - [ ] Button: "العودة إلى لوحة التحكم"

### 8.5 Error Layout

- [ ] Create `frontend/layouts/error.vue`:
  - [ ] Minimal layout (no header, sidebar, footer)
  - [ ] Full screen centered content
  - [ ] White background
  - [ ] RTL support

### 8.6 App Root Integration

- [ ] Update `frontend/app.vue`:
  - [ ] Wrap `<NuxtPage />` in `<AppErrorBoundary />`
  - [ ] Global error boundary catches all errors

### 8.7 Error Store (Pinia)

- [ ] Create `frontend/stores/error.ts`:
  - [ ] `errors` ref (array of errors)
  - [ ] `lastError` ref (most recent error)
  - [ ] `setError(error)` action
  - [ ] `clearError(code)` action
  - [ ] `clearAll()` action
  - [ ] Auto-clear errors after 30s

---

## 9. Arabic/RTL Support Checklist

**Objective:** All error messages, pages, and notifications support Arabic RTL.

### 9.1 Backend Translations

- [ ] Create `backend/resources/lang/ar/errors.php`:
  - [ ] Error messages for each error code in Arabic
  - [ ] Validation messages for common fields
  - [ ] Workflow error messages

- [ ] Create `backend/resources/lang/en/errors.php`:
  - [ ] English translations as fallback
  - [ ] Consistent with Arabic versions

- [ ] Form Request validation messages:
  - [ ] All in Arabic using `messages()` method
  - [ ] Include Arabic translations for standard Laravel rules

### 9.2 Frontend Translations

- [ ] Create `frontend/locales/ar.json`:
  - [ ] Error message translations
  - [ ] Error page headings/descriptions
  - [ ] Toast notification messages
  - [ ] Button labels (الحين, تحديث, العودة, أعد المحاولة)

- [ ] Create `frontend/locales/en.json`:
  - [ ] English translations as fallback

- [ ] Implement i18n in composables:
  - [ ] Use `useI18n()` in error notifications
  - [ ] Dynamic message translation based on locale

### 9.3 HTML Direction & Tailwind

- [ ] Set `dir="rtl"` on `<html>` element in `nuxt.config.ts`:

  ```typescript
  app: {
    head: {
      htmlAttrs: { dir: 'rtl', lang: 'ar' },
    },
  }
  ```

- [ ] Use Tailwind logical properties:
  - [ ] `ml-4` → `ml-4 rtl:mr-4 rtl:ml-0` (use logical properties)
  - [ ] `text-left` → `text-start` (for RTL support)
  - [ ] `border-r` → `border-e` (end instead of right)

- [ ] Error page components:
  - [ ] All text right-aligned
  - [ ] Buttons arranged for RTL flow

### 9.4 Nuxt UI RTL

- [ ] Verify Nuxt UI handles RTL:
  - [ ] Components automatically mirror in RTL
  - [ ] Toast position: top-right (correct for RTL)
  - [ ] Icons flip if needed

---

## 10. Testing & Validation Checklist

**Objective:** All error handling is tested and validated end-to-end.

### 10.1 Backend Unit Tests

- [ ] Create `backend/tests/Unit/Exceptions/` tests:
  - [ ] Test each custom exception class
  - [ ] Test `getErrorCode()`, `getHttpStatus()`, `getDetails()`
  - [ ] Test exception message formatting

- [ ] Create `backend/tests/Unit/Http/Middleware/` tests:
  - [ ] Test correlation ID generation (missing header)
  - [ ] Test correlation ID usage (provided header)
  - [ ] Test logging middleware (duration calculation)

### 10.2 Backend Feature Tests

- [ ] Test validation error response (422):
  - [ ] [ ] Missing required fields
  - [ ] [ ] Invalid field format
  - [ ] [ ] Field-level error details in response
  - [ ] [ ] Correct error code and message

- [ ] Test authentication errors (401):
  - [ ] [ ] Missing auth header
  - [ ] [ ] Invalid token format
  - [ ] [ ] Expired token
  - [ ] [ ] Correct error message in Arabic

- [ ] Test authorization errors (403):
  - [ ] [ ] Role not permitted
  - [ ] [ ] Correct RBAC_ROLE_DENIED code
  - [ ] [ ] Required vs actual role in details

- [ ] Test not found errors (404):
  - [ ] [ ] Nonexistent project ID
  - [ ] [ ] Nonexistent phase ID
  - [ ] [ ] Correct error code and message

- [ ] Test workflow errors (422):
  - [ ] [ ] Invalid state transition
  - [ ] [ ] Allowed transitions in error details
  - [ ] [ ] Unmet prerequisites

- [ ] Test rate limiting (429):
  - [ ] [ ] Hit rate limit
  - [ ] [ ] Retry-After header in response

- [ ] Test server error (500):
  - [ ] [ ] Unhandled exception
  - [ ] [ ] No stack trace in response
  - [ ] [ ] Logged with correlation ID

### 10.3 Frontend Unit Tests

- [ ] Test `useApi()` composable:
  - [ ] Injects auth token
  - [ ] Generates correlation ID
  - [ ] Handles 401 logout
  - [ ] Handles 403 redirect

- [ ] Test `useErrorNotification()` composable:
  - [ ] Shows toast with correct message
  - [ ] Maps error codes to messages
  - [ ] Severity detection (error vs warning)
  - [ ] Retry button appears for retryable errors

- [ ] Test `AppErrorBoundary` component:
  - [ ] Catches errors via onErrorCaptured
  - [ ] Displays error card
  - [ ] Reset button clears error
  - [ ] Refresh button reloads page

### 10.4 Frontend Integration Tests

- [ ] Test error page rendering:
  - [ ] [ ] 404 page displays correctly
  - [ ] [ ] 500 page displays correctly
  - [ ] [ ] 403 page displays correctly
  - [ ] [ ] All use error layout

- [ ] Test error flow end-to-end:
  - [ ] [ ] API error triggers notification
  - [ ] [ ] Notification shows correct message
  - [ ] [ ] User can retry if applicable
  - [ ] [ ] Retry calls API again

- [ ] Test Arabic/RTL:
  - [ ] [ ] Error pages display in Arabic
  - [ ] [ ] Text is right-aligned
  - [ ] [ ] Toast notifications in Arabic
  - [ ] [ ] All buttons labeled in Arabic

### 10.5 Integration Tests (Full Stack)

- [ ] Test validation error flow:
  - [ ] Backend returns 422 with error details
  - [ ] Frontend API interceptor catches error
  - [ ] Frontend shows validation toast
  - [ ] User sees field-level error messages

- [ ] Test authorization error flow:
  - [ ] Backend returns 403 RBAC_ROLE_DENIED
  - [ ] Frontend intercepts 403
  - [ ] User redirected to dashboard or 403 page
  - [ ] Correct error message displayed

- [ ] Test correlation ID tracing:
  - [ ] Frontend sends X-Correlation-ID header
  - [ ] Backend logs correlation ID
  - [ ] Error response includes correlation ID
  - [ ] Developer can trace full request cycle

### 10.6 Load/Performance Tests

- [ ] Test rate limiting under load:
  - [ ] Requests above limit return 429
  - [ ] Retry-After header present
  - [ ] Clients receive correct error message

- [ ] Test logging performance:
  - [ ] Structured logging doesn't impact latency
  - [ ] JSON formatting doesn't slow response
  - [ ] Correlation ID injection negligible cost

### 10.7 Security Tests

- [ ] Test error detail filtering:
  - [ ] Admin sees stack traces (dev only)
  - [ ] Other roles don't see stack traces
  - [ ] No sensitive data in error responses
  - [ ] No password/token exposure

- [ ] Test RBAC error details:
  - [ ] Customer can't see contractor errors
  - [ ] Field engineer can't see architect errors
  - [ ] Admin sees all error details (appropriately)

---

## 11. COMPLETION CRITERIA

**All checklists items must be completed before marking STAGE_05 as COMPLETE:**

- [ ] All 12 error codes defined and mapped
- [ ] Exception handler catches all exception types
- [ ] All responses follow error contract (success and error)
- [ ] Correlation ID middleware logs all requests
- [ ] Structured logging configured and tested
- [ ] Frontend API interceptor implemented
- [ ] Error notifications working in toast
- [ ] Error boundary component functional
- [ ] Error pages (404, 500, 403) display correctly
- [ ] All error messages in Arabic
- [ ] All error pages RTL-aware
- [ ] Backend tests: 100% coverage of error paths
- [ ] Frontend tests: Error boundary, interceptor, notifications
- [ ] Integration tests: Error flows end-to-end
- [ ] No hardcoded error messages (all translated)
- [ ] No stack traces in production responses
- [ ] RBAC filtering prevents sensitive detail leakage
- [ ] Correlation ID tracing works end-to-end

---

## 12. NOTES & DEPENDENCIES

**Upstream Dependencies:**

- STAGE_01_PROJECT_INITIALIZATION (Laravel + Nuxt.js setup)
- STAGE_02_DATABASE_SCHEMA (tables, migrations)
- STAGE_03_AUTHENTICATION (user model, sanctum config)

**Downstream Consumers:**

- All features built after STAGE_05 must use error contract
- All controllers must throw custom exceptions
- All API responses must follow contract

**Architecture Authority:**

- Error contract binding in AGENTS.md
- Error-handling-patterns skill (`.agents/skills/error-handling-patterns/`)
- i18n governance (`.agents/skills/i18n-governance/`)
- DESIGN.md (frontend error page styling)

**Testing Authority:**

- api-testing-patterns skill for RBAC tests
- PHPUnit/Pest for backend tests
- Vitest for frontend tests
- Feature tests must validate full error flows

---

## 13. QUICK REFERENCE: ERROR HANDLING FLOW

### Backend Error Flow

```
Request → Middleware (Correlation ID) →
  Router → Controller → Service →
    Exception → Handler → Contract Response → Middleware (Logging) → Client
```

### Frontend Error Flow

```
Component → useApi() → Interceptor →
  Error → useErrorNotification() → Toast / Redirect / Retry
    ↓ (if uncaught)
  ErrorBoundary → Error Page
```

### Example: Validation Error

```
Backend: Form Request validation fails
  → ValidationException thrown
  → Handler catches, formats as VALIDATION_ERROR
  → Returns 422 with field details
Frontend: useApi() interceptor sees 422
  → Extracts error code and details
  → Calls showErrorNotification()
  → Toast appears with field errors
  → User sees "البيانات غير صحيحة" message
  → User corrects form and retries
```
