# STAGE_05 Tasks — Error Handling & Logging

**Total Tasks:** 65  
**Completed Tasks:** 65  
**Estimated Duration (Serial):** 5.5 days  
**Estimated Duration (Parallel):** 2.5 days  
**Critical Path:** Phase 1 → Phase 2 → Phase 3 → Phase 4 → Phase 5

**Status:** COMPLETE  
**Authority:** plan.md, quickstart.md, data-model.md, error-handling-patterns skill, i18n-governance skill

---

## Phase 1: Backend Exception Infrastructure (T001-T015)

**Dependencies:** None (foundational)  
**Estimated Duration:** 1.5 days (serial) / 1 day (parallel)  
**Parallel Groups:** 7 tasks can run in parallel (T001-T007)

### T001-T007: Core Exception Classes (Can run in parallel)

- [x] T001 [P] [Backend] Create ErrorCode enum — `backend/app/Enums/ErrorCode.php`
  - 12 error codes: VALIDATION_ERROR, AUTH_INVALID_CREDENTIALS, AUTH_TOKEN_EXPIRED, AUTH_UNAUTHORIZED, RBAC_ROLE_DENIED, RESOURCE_NOT_FOUND, WORKFLOW_INVALID_TRANSITION, WORKFLOW_PREREQUISITES_UNMET, PAYMENT_FAILED, RATE_LIMIT_EXCEEDED, SERVER_ERROR, SERVICE_UNAVAILABLE
  - Methods: `httpStatus()`, `severity()`, `description()`
  - Immutable HTTP status mappings (422, 401, 403, 404, 429, 500, 503)
  - Dependencies: None
  - Time: 30 min
  - Test: Unit test for all status mappings

- [x] T002 [P] [Backend] Create ExceptionContract interface — `backend/app/Exceptions/ExceptionContract.php`
  - Methods: `getErrorCode()`, `getHttpStatus()`, `getDetails()`
  - Define contract for all custom exceptions
  - Dependencies: None
  - Time: 15 min

- [x] T003 [P] [Backend] Create DomainException base class — `backend/app/Exceptions/DomainException.php`
  - Extends `Exception`, implements `ExceptionContract`
  - Default implementations for all three methods
  - Default error code: SERVER_ERROR
  - Default HTTP status: 500
  - Default details: null
  - Dependencies: T002
  - Time: 15 min

- [x] T004 [P] [Backend] Create ValidationException class — `backend/app/Exceptions/ValidationException.php`
  - Extends `DomainException`
  - Properties: `$errors` (array of field errors)
  - Error code: VALIDATION_ERROR
  - HTTP status: 422
  - Details include field-level validation messages
  - Dependencies: T003
  - Time: 15 min

- [x] T005 [P] [Backend] Create InvalidStateTransitionException class — `backend/app/Exceptions/InvalidStateTransitionException.php`
  - Extends `DomainException`
  - Properties: `$fromState`, `$toState`, `$allowedTransitions`
  - Error code: WORKFLOW_INVALID_TRANSITION
  - HTTP status: 422
  - Details include state information
  - Dependencies: T003
  - Time: 15 min

- [x] T006 [P] [Backend] Create ResourceNotFoundException class — `backend/app/Exceptions/ResourceNotFoundException.php`
  - Extends `DomainException`
  - Properties: `$resourceType`, `$resourceId`
  - Error code: RESOURCE_NOT_FOUND
  - HTTP status: 404
  - Details include resource information
  - Dependencies: T003
  - Time: 15 min

- [x] T007 [P] [Backend] Create PaymentFailedException class — `backend/app/Exceptions/PaymentFailedException.php`
  - Extends `DomainException`
  - Properties: `$transactionId`, `$reason`
  - Error code: PAYMENT_FAILED
  - HTTP status: 422
  - Severity: error
  - Dependencies: T003
  - Time: 15 min

### T008-T010: Support Classes (Sequential, depends on T001-T007)

- [x] T008 [Backend] Create ErrorCodeRegistry service — `backend/app/Services/ErrorCodeRegistry.php`
  - In-memory registry mapping error codes to metadata
  - Methods: `get()`, `httpStatus()`, `severity()`, `isRetryable()`, `all()`
  - Registry includes all 12 error codes with HTTP status, severity, description, retry flag
  - Dependencies: T001
  - Time: 30 min

- [x] T009 [Backend] Create ApiResponse trait — `backend/app/Http/Controllers/Api/ApiResponse.php`
  - Methods: `sendSuccess()`, `sendError()`
  - Trait for use in all API controllers
  - Success format: `{success: true, data: ?, error: null}`
  - Error format: `{success: false, data: null, error: {code, message, details}}`
  - Dependencies: None
  - Time: 20 min

- [x] T010 [Backend] Update Exception Handler — `backend/app/Exceptions/Handler.php`
  - Catch ValidationException → 422 with field details
  - Catch InvalidStateTransitionException → 422 with state details
  - Catch ResourceNotFoundException → 404 with resource details
  - Catch PaymentFailedException → 422
  - Catch ModelNotFoundException → 404
  - Catch AuthenticationException → 401
  - Catch AuthorizationException → 403
  - Catch ThrottleRequestsException → 429
  - Default → 500 SERVER_ERROR
  - RBAC error detail filtering (stack traces only for admin in dev)
  - Log all errors with correlation ID (if available)
  - Dependencies: T001, T003-T007, T008
  - Time: 1 hour

### T011-T015: Testing (Can run in parallel)

- [x] T011 [P] [Backend] Unit tests for ErrorCode enum — `backend/tests/Unit/Enums/ErrorCodeTest.php`
  - Test all 12 status mappings
  - Test severity levels
  - Test description strings
  - Coverage: 100%
  - Dependencies: T001
  - Time: 30 min

- [x] T012 [P] [Backend] Unit tests for custom exceptions — `backend/tests/Unit/Exceptions/ExceptionHierarchyTest.php`
  - Test InvalidStateTransitionException details
  - Test ResourceNotFoundException details
  - Test PaymentFailedException details
  - Test exception inheritance and interface implementation
  - Coverage: 100%
  - Dependencies: T003-T007
  - Time: 45 min

- [x] T013 [P] [Backend] Unit tests for ErrorCodeRegistry — `backend/tests/Unit/Services/ErrorCodeRegistryTest.php`
  - Test get() for all error codes
  - Test httpStatus() mapping
  - Test severity() classification
  - Test isRetryable() flag
  - Coverage: 100%
  - Dependencies: T008
  - Time: 30 min

- [x] T014 [Backend] Feature tests for validation error response — `backend/tests/Feature/ErrorHandling/ValidationErrorResponseTest.php`
  - POST to endpoint with missing required fields
  - Assert 422 response
  - Assert error code is VALIDATION_ERROR
  - Assert field-level details included
  - Assert message is in Arabic
  - Test case count: 5+ scenarios
  - Dependencies: T010
  - Time: 1 hour

- [x] T015 [Backend] Feature tests for exception handler integration — `backend/tests/Feature/ErrorHandling/ExceptionHandlerTest.php`
  - Test all 12 error codes can be triggered and formatted
  - Test 401 response for unauthenticated requests
  - Test 403 response for unauthorized roles
  - Test 404 for missing resources
  - Test 500 for unhandled exceptions
  - Test stack traces hidden from non-admin users
  - Test stack traces visible only in development
  - Test case count: 12+ scenarios
  - Coverage matrix: 12 error codes × 2 user types (admin/non-admin) × 2 environments (local/production)
  - Dependencies: T010
  - Time: 2 hours

---

## Phase 2: Backend Middleware & Logging (T016-T030)

**Dependencies:** Phase 1 (T001-T015)  
**Estimated Duration:** 1.5 days (serial) / 1 day (parallel)  
**Parallel Groups:** 3 middleware tasks (T016-T018) can run in parallel

### T016-T018: Middleware Layer (Can run in parallel)

- [x] T016 [P] [Backend] Create InjectCorrelationId middleware — `backend/app/Http/Middleware/InjectCorrelationId.php`
  - Extract X-Correlation-ID header or generate new UUID
  - Format: `req_{timestamp}_{random}`
  - Store in `$request->attributes->set('correlation_id', $id)`
  - Push to Log processor (Monolog) for structured logging context
  - Must execute FIRST in middleware pipeline
  - Dependencies: None
  - Time: 30 min

- [x] T017 [P] [Backend] Create LogApiActivity middleware — `backend/app/Http/Middleware/LogApiActivity.php`
  - Log request entry with method, path, user_id, correlation_id
  - Record start time
  - After response, log response status, duration, correlation_id
  - Use structured logging (JSON format)
  - Include fields: timestamp, correlation_id, user_id, request_method, request_path, response_status, duration_ms, user_role
  - Dependencies: T016
  - Time: 45 min

- [x] T018 [P] [Backend] Create ErrorDetailFiltering middleware — `backend/app/Http/Middleware/ErrorDetailFiltering.php`
  - Filter stack traces from error responses based on user role
  - Admin sees stack traces in development/staging only
  - Non-admin never sees internal error details
  - Applied after exception handler
  - Dependencies: T010
  - Time: 30 min

### T019-T021: Configuration & Registration

- [x] T019 [Backend] Create structured logging configuration — `backend/config/logging.php` (MODIFY)
  - Add `structured` channel with JsonFormatter
  - Set minimum log level to WARNING
  - Configure log retention: 30 days
  - Path: `storage/logs/structured.log`
  - Dependencies: T017
  - Time: 30 min

- [x] T020 [Backend] Register middleware in Kernel — `backend/app/Http/Kernel.php` (MODIFY)
  - Add InjectCorrelationId to `$middleware` array (FIRST, before all others)
  - Add LogApiActivity to `$middlewareGroups['api']`
  - Add ErrorDetailFiltering to `$middlewareGroups['api']`
  - Verify order: InjectCorrelationId → Auth → RBAC → LogApiActivity (start) → Handler → LogApiActivity (end)
  - Dependencies: T016, T017, T018
  - Time: 20 min

- [x] T021 [Backend] Create LoggingService — `backend/app/Services/LoggingService.php`
  - Utility service for structured logging with correlation ID
  - Methods: `logRequest()`, `logResponse()`, `logError()`, `logActivity()`
  - Each method includes correlation_id in context
  - Dependencies: T016
  - Time: 30 min

### T022-T025: Logging & Persistence (Can run in parallel)

- [x] T022 [P] [Backend] Create ErrorLoggingService — `backend/app/Services/ErrorLoggingService.php`
  - Service to log errors to database (optional, for Phase 2 extended)
  - Methods: `log()`, `findByCorrelationId()`, `getStatistics()`
  - Integration with error_logs table (if created)
  - Dependencies: T010
  - Time: 45 min
  - NOTE: Only if database persistence is needed; can be deferred

- [x] T023 [P] [Backend] Create error logs migration — `backend/database/migrations/2026_04_11_000000_create_error_logs_table.php` (OPTIONAL)
  - Table: error_logs
  - Columns: id, correlation_id, error_code, message, details, context, severity, http_status, exception_class, stack_trace, user_id, user_role, request_method, request_path, request_ip, response_time_ms, timestamps
  - Indexes: correlation_id, error_code, severity, user_id, created_at, composite indexes
  - Dependencies: None
  - Time: 30 min
  - NOTE: Optional for MVP; can be deferred to Phase 2 extended

- [x] T024 [Backend] Unit tests for InjectCorrelationId middleware — `backend/tests/Unit/Http/Middleware/InjectCorrelationIdTest.php`
  - Test correlation ID extraction from header
  - Test correlation ID generation if missing
  - Test correlation ID stored in request attributes
  - Test correlation ID format validation
  - Coverage: 100%
  - Dependencies: T016
  - Time: 30 min

- [x] T025 [Backend] Unit tests for LogApiActivity middleware — `backend/tests/Unit/Http/Middleware/LogApiActivityTest.php`
  - Test request logging captures all fields
  - Test duration calculation
  - Test structured JSON format
  - Test correlation_id included in log
  - Coverage: 100%
  - Dependencies: T017
  - Time: 30 min

### T026-T030: Integration Testing

- [x] T026 [Backend] Feature tests for correlation ID propagation — `backend/tests/Feature/ErrorHandling/CorrelationIdTest.php`
  - POST request with X-Correlation-ID header
  - Assert header in response (if error)
  - Assert correlation ID in logs
  - Trace request through middleware pipeline
  - Test case count: 5+ scenarios (with header, without header, invalid format)
  - Dependencies: T016, T017
  - Time: 1 hour

- [x] T027 [Backend] Feature tests for error detail filtering by RBAC — `backend/tests/Feature/ErrorHandling/ErrorDetailFilteringTest.php`
  - Admin user (development): sees stack traces
  - Admin user (production): does NOT see stack traces
  - Non-admin user: never sees stack traces
  - Test case count: 6 scenarios (3 roles × 2 environments)
  - Coverage matrix: Customer, Contractor, Admin × local, production
  - Dependencies: T018, T010
  - Time: 1.5 hours

- [x] T028 [Backend] Middleware pipeline order validation — `backend/tests/Feature/ErrorHandling/MiddlewarePipelineTest.php`
  - Verify InjectCorrelationId executes first
  - Verify Auth middleware runs after InjectCorrelationId
  - Verify RBAC middleware runs after Auth
  - Verify LogApiActivity (start) runs before controller
  - Verify LogApiActivity (end) runs after controller
  - Verify exception handler catches exceptions
  - Dependencies: T016, T017, T020
  - Time: 1 hour

- [x] T029 [Backend] Performance benchmark: Exception handler latency — `backend/tests/Feature/ErrorHandling/PerformanceBenchmarkTest.php`
  - Measure exception handling latency
  - 1000 validation exceptions
  - Assert average latency < 10ms
  - Assert p99 latency < 50ms
  - Dependencies: T010
  - Time: 30 min

- [x] T030 [Backend] Security test: No credential leaks in logs — `backend/tests/Feature/ErrorHandling/SecurityLogsTest.php`
  - Log output does NOT contain passwords
  - Log output does NOT contain API tokens
  - Log output does NOT contain PII (email in some contexts OK)
  - Log output does NOT contain database passwords
  - Scan structured logs for regex patterns (password, token, secret, key)
  - Dependencies: T017, T019
  - Time: 45 min

---

## Phase 3: Frontend Interceptor & Error Handling (T031-T045)

**Dependencies:** Phase 1-2 (T001-T030)  
**Estimated Duration:** 1.5 days (serial) / 1 day (parallel)  
**Parallel Groups:** 5 composable/component tasks (T031-T035) can run in parallel

### T031-T035: Core Frontend Composables & Components (Can run in parallel)

- [x] T031 [P] [Frontend] Create useApi composable — `frontend/composables/useApi.ts`
  - Initialize $fetch with base URL from nuxt.config
  - Inject auth token in Authorization header
  - Generate and inject X-Correlation-ID header
  - Interceptor: onRequest to add headers
  - Interceptor: onResponseError to handle errors by status code
  - 401 → logout and redirect to /auth/login
  - 403 → redirect to /dashboard
  - 5xx → call useErrorNotification() with error details
  - 4xx → call useErrorNotification() with field details if present
  - Expose: `{ apiFetch }`
  - Dependencies: None (Nuxt runtime built-ins)
  - Time: 1 hour

- [x] T032 [P] [Frontend] Create useErrorNotification composable — `frontend/composables/useErrorNotification.ts`
  - Use Nuxt UI toast from `#ui/composables/useToast`
  - Method: `showErrorNotification(payload: ErrorPayload)`
  - ErrorPayload: { code, message, details?, statusCode? }
  - Severity detection: 5xx → error (red, 8s), 4xx → warning (yellow, 5s)
  - Toast title: error code
  - Toast description: localized message
  - Show details as separate toast or modal if present
  - Expose: `{ showErrorNotification }`
  - Dependencies: Nuxt UI (@nuxt/ui)
  - Time: 45 min

- [x] T033 [P] [Frontend] Create errorStore (Pinia) — `frontend/stores/error.ts`
  - State: `errors` (array of error objects), `lastError` (latest error), `isVisible` (boolean)
  - Action: `addError(error)` → push to errors array, set lastError
  - Action: `clearErrors()` → reset errors array
  - Computed: `hasErrors` → errors.length > 0
  - Auto-clear after 30s: watch lastError and schedule clearErrors()
  - Expose: error state and actions
  - Dependencies: Pinia
  - Time: 30 min

- [x] T034 [P] [Frontend] Create AppErrorBoundary component — `frontend/components/common/AppErrorBoundary.vue`
  - Vue 3 onErrorCaptured lifecycle hook
  - Catch unhandled component errors
  - Display error card with details (in development)
  - Show recovery buttons: "العودة" (back), "تحديث" (refresh)
  - Hide error details in production (security)
  - Use DESIGN.md shadow-as-border styling
  - RTL support via Tailwind logical properties
  - Template structure: error card centered, min-h-screen
  - Dependencies: DESIGN.md, Tailwind v4
  - Time: 45 min

- [x] T035 [P] [Frontend] Create ErrorToast component — `frontend/components/common/ErrorToast.vue`
  - Wrapper around Nuxt UI UNotification component
  - Display error code, message, and optional details
  - Color scheme: red for 5xx, yellow for 4xx
  - Timeout: 8s for error, 5s for warning
  - Close button
  - Optional retry button (if statusCode 429 or 5xx)
  - RTL support
  - Dependencies: Nuxt UI (@nuxt/ui)
  - Time: 30 min

### T036-T040: Error Pages (Sequential, can be parallelized)

- [x] T036 [Frontend] Create 404 error page — `frontend/pages/error/404.vue`
  - Heading: "404 — الصفحة غير موجودة" (404 - Page Not Found)
  - Description: "المورد الذي تبحث عنه غير موجود" (The resource you're looking for is not found)
  - Action buttons: "العودة للرئيسية" (back to home), "العودة" (go back)
  - Use error layout: `frontend/layouts/error.vue`
  - RTL-aware layout, shadow-as-border design
  - Dependencies: DESIGN.md
  - Time: 30 min

- [x] T037 [Frontend] Create 500 error page — `frontend/pages/error/500.vue`
  - Heading: "500 — خطأ في الخادم" (500 - Server Error)
  - Description: "حدث خطأ غير متوقع. يرجى المحاولة لاحقًا" (Unexpected error. Please try again later)
  - Action buttons: "العودة للرئيسية" (back to home), "تحديث الصفحة" (refresh page)
  - Option to report error if error store has details
  - Use error layout
  - RTL-aware layout, shadow-as-border design
  - Dependencies: DESIGN.md
  - Time: 30 min

- [x] T038 [Frontend] Create 403 error page — `frontend/pages/error/403.vue`
  - Heading: "403 — غير مصرح" (403 - Forbidden)
  - Description: "ليس لديك صلاحية للوصول إلى هذا المورد" (You don't have permission to access this resource)
  - Action buttons: "العودة للرئيسية" (back to home), "تسجيل الخروج" (logout)
  - Use error layout
  - RTL-aware layout, shadow-as-border design
  - Dependencies: DESIGN.md
  - Time: 30 min

- [x] T039 [Frontend] Create error layout — `frontend/layouts/error.vue`
  - Minimal layout (no header, no sidebar)
  - Container: centered, max-w-md
  - Use Nuxt UI typography
  - Shadow-as-border design from DESIGN.md
  - Heading: aggressive letter-spacing, dark color
  - Description: neutral gray
  - Buttons: Nuxt UI UButton components
  - RTL support via dir="rtl" in html
  - Dependencies: DESIGN.md, Nuxt UI
  - Time: 30 min

- [x] T040 [Frontend] Create error types — `frontend/types/errors.ts`
  - Type: `ErrorPayload` (code, message, details?, statusCode?)
  - Type: `ApiError` (extends ErrorPayload, correlationId?)
  - Type: `ValidationError` (extends ApiError, fieldErrors: Record<string, string[]>)
  - Type: `ErrorCode` (union of all error codes)
  - Export all types for use across app
  - Dependencies: None
  - Time: 30 min

### T041-T045: Frontend Testing (Can run in parallel)

- [x] T041 [P] [Frontend] Unit tests for useApi composable — `frontend/tests/unit/composables/useApi.spec.ts`
  - Test correlation ID generation
  - Test correlation ID header injection
  - Test auth token injection
  - Test 401 error triggers logout
  - Test 403 error triggers redirect
  - Test error notification for 4xx/5xx
  - Coverage: 100%
  - Dependencies: T031
  - Time: 1 hour

- [x] T042 [P] [Frontend] Unit tests for useErrorNotification composable — `frontend/tests/unit/composables/useErrorNotification.spec.ts`
  - Test severity detection (5xx → error, 4xx → warning)
  - Test toast title set to error code
  - Test toast description set to message
  - Test toast duration (8s for error, 5s for warning)
  - Test toast color (red for error, yellow for warning)
  - Coverage: 100%
  - Dependencies: T032
  - Time: 45 min

- [x] T043 [P] [Frontend] Unit tests for errorStore — `frontend/tests/unit/stores/error.spec.ts`
  - Test addError() pushes to array
  - Test clearErrors() resets array
  - Test lastError updated on addError()
  - Test auto-clear after 30s
  - Test hasErrors computed property
  - Coverage: 100%
  - Dependencies: T033
  - Time: 45 min

- [x] T044 [P] [Frontend] Unit tests for AppErrorBoundary component — `frontend/tests/unit/components/AppErrorBoundary.spec.ts`
  - Test error captured and displayed
  - Test recovery buttons functional
  - Test error hidden in production
  - Test error details shown in development
  - Coverage: 100%
  - Dependencies: T034
  - Time: 1 hour

- [x] T045 [Frontend] Unit tests for error pages — `frontend/tests/unit/pages/error.spec.ts`
  - Test 404 page renders correct message
  - Test 500 page renders correct message
  - Test 403 page renders correct message
  - Test action buttons render
  - Test RTL layout (dir="rtl")
  - Coverage: 100%
  - Dependencies: T036-T038
  - Time: 1 hour

---

## Phase 4: Localization & i18n (T046-T052)

**Dependencies:** Phase 1-3 (T001-T045)  
**Estimated Duration:** 1 day (serial) / 1 day (parallel)  
**Parallel Groups:** 2 translation file tasks (T046-T047) can run in parallel

### T046-T047: Translation Files (Can run in parallel)

- [x] T046 [P] [Backend] Create Arabic error translations — `backend/resources/lang/ar/errors.php` (NEW) + validation messages
  - Translation keys for all 12 error codes
  - VALIDATION_ERROR: "البيانات المدخلة غير صحيحة"
  - AUTH_INVALID_CREDENTIALS: "بيانات الدخول غير صحيحة"
  - AUTH_TOKEN_EXPIRED: "انتهت صلاحية الجلسة"
  - AUTH_UNAUTHORIZED: "يجب تسجيل الدخول أولاً"
  - RBAC_ROLE_DENIED: "غير مصرح لك بهذا الإجراء"
  - RESOURCE_NOT_FOUND: "المورد المطلوب غير موجود"
  - WORKFLOW_INVALID_TRANSITION: "لا يمكن الانتقال من حالة إلى أخرى"
  - WORKFLOW_PREREQUISITES_UNMET: "المتطلبات غير مستوفاة"
  - PAYMENT_FAILED: "فشل معالجة الدفع"
  - RATE_LIMIT_EXCEEDED: "تم تجاوز حد الطلبات"
  - SERVER_ERROR: "حدث خطأ غير متوقع"
  - SERVICE_UNAVAILABLE: "الخدمة غير متاحة حالياً"
  - Validation rule messages (required, email, min, max, etc.)
  - All 50+ Laravel validation messages in Arabic
  - Dependencies: T010
  - Time: 1 hour

- [x] T047 [P] [Backend] Create English error translations — `backend/resources/lang/en/errors.php` (NEW)
  - English versions of all error codes
  - English versions of all validation messages
  - Fallback language
  - Dependencies: T010
  - Time: 45 min

### T048-T050: Frontend Translations

- [x] T048 [Frontend] Create Arabic error messages — `frontend/locales/ar.json` (MODIFY/NEW)
  - Error codes as keys: VALIDATION_ERROR, AUTH_UNAUTHORIZED, etc.
  - Error messages in Arabic
  - Button labels: "أعد المحاولة" (retry), "العودة" (go back), "تحديث" (refresh)
  - Page headings and descriptions
  - Toast titles and descriptions
  - Form validation messages
  - Total: 50+ translation keys
  - Dependencies: T032
  - Time: 1 hour

- [x] T049 [Frontend] Create English error messages — `frontend/locales/en.json` (MODIFY/NEW)
  - English translations of all keys
  - Fallback language
  - Total: 50+ translation keys
  - Dependencies: T048
  - Time: 45 min

- [x] T050 [Frontend] Validate Arabic RTL rendering — `frontend/tests/integration/i18n/ArabicRTLTest.spec.ts`
  - Test dir="rtl" applied to <html>
  - Test error pages display with RTL direction
  - Test buttons align correctly (rtl: right side)
  - Test text direction in error cards
  - Test Tailwind logical properties (ms-, me-, start, end)
  - Test Arabic typography legibility
  - Screenshot comparison (baseline vs actual)
  - Dependencies: T036-T039, T048
  - Time: 1.5 hours

### T051-T052: Localization Integration

- [x] T051 [Backend] Update Form Request validation messages — `backend/app/Http/Requests/*` (MODIFY ALL)
  - Add `messages()` method to each Form Request class
  - Return Arabic validation messages for each rule
  - Example: `'email.required' => 'البريد الإلكتروني مطلوب'`
  - Apply to all existing form requests
  - Dependencies: T046
  - Time: 2 hours

- [x] T052 [Frontend] Update useErrorNotification for localization — `frontend/composables/useErrorNotification.ts` (MODIFY)
  - Use $i18n from Nuxt i18n module
  - Look up error.code in locales/ar.json
  - Fallback to error.message if not found
  - Support for field-level error translation
  - Dependencies: T032, T048
  - Time: 30 min

---

## Phase 5: Integration & E2E Testing (T053-T065)

**Dependencies:** Phase 1-4 (T001-T052)  
**Estimated Duration:** 1.5 days (serial) / 1.5 days (parallel)  
**Parallel Groups:** Multiple test suites can run in parallel

### T053-T058: Full-Stack Integration Tests (Can run in parallel)

- [x] T053 [P] [Integration] End-to-end: Validation error → notification → retry — `backend/tests/Feature/ErrorHandling/E2EValidationErrorTest.php`
  - Create form with missing required fields
  - POST request with incomplete data
  - Assert 422 response with field details
  - Assert frontend receives error code VALIDATION_ERROR
  - Assert error toast displays with Arabic message
  - User corrects form and resubmits
  - Assert success response
  - Browser-based test (Playwright) or API + frontend simulation
  - Dependencies: T010, T031, T032
  - Time: 1.5 hours

- [x] T054 [P] [Integration] End-to-end: Auth error → redirect to login — `backend/tests/Feature/ErrorHandling/E2EAuthErrorTest.php`
  - Call protected endpoint without auth token
  - Assert 401 response
  - Assert frontend redirects to /auth/login
  - Assert error toast shows "يجب تسجيل الدخول أولاً"
  - Browser-based test
  - Dependencies: T010, T031
  - Time: 1 hour

- [x] T055 [P] [Integration] End-to-end: RBAC error → denied page — `backend/tests/Feature/ErrorHandling/E2ERBACErrorTest.php`
  - Customer user attempts admin-only endpoint
  - Assert 403 response
  - Assert error code RBAC_ROLE_DENIED
  - Assert frontend redirects to 403 error page
  - Assert message: "غير مصرح لك بهذا الإجراء"
  - Browser-based test
  - Dependencies: T010, T031, T038
  - Time: 1 hour

- [x] T056 [P] [Integration] End-to-end: Server error → 500 page — `backend/tests/Feature/ErrorHandling/E2EServerErrorTest.php`
  - Trigger unhandled exception (divide by zero, etc.)
  - Assert 500 response
  - Assert error code SERVER_ERROR
  - Assert frontend redirects to 500 error page
  - Assert error boundary captures error
  - Browser-based test
  - Dependencies: T010, T037
  - Time: 1 hour

- [x] T057 [P] [Integration] End-to-end: Correlation ID tracing — `backend/tests/Feature/ErrorHandling/E2ECorrelationIdTest.php`
  - Make API request with explicit X-Correlation-ID header
  - Trigger error in endpoint
  - Assert correlation ID in response header
  - Assert correlation ID in logs
  - Query error logs by correlation ID
  - Assert all related logs have same correlation ID
  - Trace request through full stack
  - Dependencies: T016, T017, T026
  - Time: 1.5 hours

- [x] T058 [P] [Integration] End-to-end: Error recovery and retry flow — `backend/tests/Feature/ErrorHandling/E2EErrorRecoveryTest.php`
  - User encounters error (validation, payment, etc.)
  - Error toast shows with retry button
  - User clicks retry button
  - Request is retried
  - Assert retry succeeds or shows different error
  - Test exponential backoff if rate limit
  - Browser-based test
  - Dependencies: T032, T041
  - Time: 1.5 hours

### T059-T061: Security & Performance Testing (Can run in parallel)

- [x] T059 [P] [Security] Security test: XSS prevention in error responses — `backend/tests/Feature/ErrorHandling/XSSPreventionTest.php`
  - Inject XSS payload in validation error details
  - Assert error message HTML-escaped in response
  - Assert error message HTML-escaped in frontend toast
  - Test payload: `<script>alert('xss')</script>`
  - Test payload: `"onload=alert('xss')"`
  - Test payload: Unicode escapes
  - Assert frontend does NOT render raw HTML
  - Dependencies: T010, T031, T032
  - Time: 1 hour

- [x] T060 [P] [Security] Security test: PII protection in error logs — `backend/tests/Feature/ErrorHandling/PIIProtectionTest.php`
  - Validation error with user email
  - Assert email NOT in structured logs (security concern)
  - Assert correlation ID in logs for tracing
  - Assert request path in logs (non-PII)
  - Assert user_id in logs (non-PII)
  - Scan logs for: email patterns, phone patterns, credit card patterns
  - Dependencies: T017, T019
  - Time: 1 hour

- [x] T061 [P] [Performance] Performance benchmark: Exception handling latency — `backend/tests/Feature/ErrorHandling/PerformanceExceptionTest.php`
  - Throw 1000 exceptions and catch them
  - Measure average latency per exception
  - Assert latency < 10ms per exception
  - Assert p99 latency < 50ms
  - Measure middleware overhead (correlation ID injection)
  - Assert middleware adds < 1ms latency
  - Load test: 100 concurrent error requests
  - Assert 100+ req/s throughput
  - Dependencies: T010, T016, T017
  - Time: 1 hour

### T062-T065: Accessibility & Compliance Testing

- [x] T062 [Frontend] Accessibility test: WCAG AA compliance on error pages — `frontend/tests/integration/a11y/ErrorPageA11yTest.spec.ts`
  - Scan 404, 500, 403 pages with axe-core
  - Assert no critical violations
  - Assert heading structure correct (h1, h2 hierarchy)
  - Assert buttons have accessible labels
  - Assert color contrast meets WCAG AA (4.5:1 for text)
  - Test with screen reader simulation
  - Test with keyboard navigation (tab, enter, escape)
  - RTL support verification for Arabic
  - Dependencies: T036-T038
  - Time: 1.5 hours

- [x] T063 [Backend] Compliance test: All error responses follow contract — `backend/tests/Feature/ErrorHandling/ErrorContractComplianceTest.php`
  - Every error response has correct structure
  - Assert success: false
  - Assert data: null
  - Assert error object with code, message, details
  - Check all 52 endpoints in application
  - No endpoint returns non-compliant error format
  - Dependencies: T010
  - Time: 1 hour

- [x] T064 [Frontend] Integration test: Error state synchronization — `frontend/tests/integration/errors/ErrorStateSyncTest.spec.ts`
  - Multiple error events in quick succession
  - Assert error store correctly maintains state
  - Assert all errors displayed (or queue if limiting)
  - Assert auto-clear works (30s timeout)
  - Assert manual clear works
  - Dependencies: T033, T041-T043
  - Time: 1 hour

- [x] T065 [Summary] Generate comprehensive error handling report — Documentation task
  - Document all 12 error codes with examples
  - Document middleware pipeline with diagram
  - Document RBAC filtering rules
  - Document correlation ID tracing
  - Document localization keys
  - Create troubleshooting guide
  - Create deployment checklist
  - Estimated size: 3000+ words
  - Dependencies: All tasks T001-T064
  - Time: 2 hours

---

## Task Summary

### Metrics

| Phase | Tasks | Parallel | Duration (Serial) | Duration (Parallel) | Dependencies |
|-------|-------|----------|-------------------|---------------------|--------------|
| Phase 1: Backend Exception Infrastructure | 15 | 12 | 1.5 days | 1 day | None |
| Phase 2: Backend Middleware & Logging | 15 | 8 | 1.5 days | 1 day | Phase 1 |
| Phase 3: Frontend Interceptor & Error Handling | 15 | 11 | 1.5 days | 1 day | Phase 1-2 |
| Phase 4: Localization & i18n | 7 | 4 | 1 day | 1 day | Phase 1-3 |
| Phase 5: Integration & E2E Testing | 13 | 11 | 1.5 days | 1 day | Phase 1-4 |
| **TOTAL** | **65** | **46** | **7 days** | **5 days** | Sequential phases |

### Parallel Groups

**Optimal Execution Strategy:**

```
Day 1: Phase 1 — Backend Exception Infrastructure (12 parallel tasks)
  └─ T001-T007 (exception classes) in parallel
  └─ T011-T015 (tests) in parallel while T008-T010 running
  
Day 2: Phase 2 — Backend Middleware & Logging (8 parallel tasks)
  └─ T016-T018 (middleware) in parallel
  └─ T024-T025 (unit tests) in parallel
  └─ T026-T030 (integration tests) in parallel
  
Day 3: Phase 3 — Frontend Components (11 parallel tasks)
  └─ T031-T035 (composables/components) in parallel
  └─ T041-T044 (component tests) in parallel
  
Day 4: Phase 4 — Localization (4 parallel tasks)
  └─ T046-T047 (translation files) in parallel
  └─ T048-T049 (frontend translations) in parallel
  
Day 5: Phase 5 — E2E Testing (11 parallel tasks)
  └─ T053-T058 (integration tests) in parallel
  └─ T059-T061 (security/performance) in parallel
  └─ T062-T065 (compliance) in parallel

Total Parallel Execution: 5 days (vs 7 days serial)
Speedup: 1.4x with full parallelization
```

### Critical Dependencies

```
T001 → T008 → T010 → T030 (exception handling must be complete before testing)
T016 → T017 → T019 → T020 (middleware pipeline must be registered)
T031 → T041 (API composable must be complete before testing)
T036-T039 → T050 (error pages must exist before RTL testing)
All Phase 1-4 → Phase 5 (integration tests need all components ready)
```

### Task Complexity Distribution

| Complexity | Count | Examples |
|-----------|-------|----------|
| **Simple (< 30 min)** | 18 | T002, T003, T006, T009, T019, T040, T047, T048, T052 |
| **Medium (30-60 min)** | 32 | T001, T004, T005, T007, T008, T016, T017, T031, T032, etc. |
| **Complex (1-2 hours)** | 12 | T010, T014, T015, T027, T028, T041-T045, T051, T053-T057, T062-T065 |
| **Very Complex (2+ hours)** | 3 | T015 (12-code coverage matrix), T051 (mass update), T065 (documentation) |

### Estimated Effort

- **Total Effort:** 65-75 person-hours (serial)
- **Parallelizable Effort:** ~46 tasks can run in parallel
- **Time Savings (Parallel):** 30-40% faster with optimal parallelization
- **Recommended Team Size:** 2-3 developers for optimal throughput

### Quality Metrics

| Metric | Target | Verification |
|--------|--------|--------------|
| **Test Coverage** | 100% | T011-T015, T024-T025, T041-T044 |
| **Error Code Coverage** | 12/12 | T001 + T014-T015 |
| **Exception Types** | 7/7 | T003-T007 |
| **Middleware Order** | Verified | T028 + T020 |
| **RBAC Filtering** | 5 roles × 2 envs | T027 |
| **Localization** | AR + EN | T046-T049 |
| **E2E Scenarios** | 6+ flows | T053-T058 |
| **Security Tests** | 2+ scans | T059-T060 |
| **Accessibility** | WCAG AA | T062 |

---

## Execution Checklist

### Before Starting

- [ ] Review `plan.md` in full
- [ ] Review `quickstart.md` for bootstrap patterns
- [ ] Verify Laravel 11+, Nuxt 3, @nuxt/ui installed
- [ ] Verify testing frameworks: PHPUnit/Pest, Vitest installed
- [ ] Verify git branch: `feature/stage-05-error-handling`

### Phase 1 Complete When

- [ ] All 12 error codes defined in ErrorCode enum
- [ ] All 7 custom exception classes created
- [ ] Exception handler catches all exception types
- [ ] ApiResponse trait working in controllers
- [ ] All Phase 1 tests passing (T011-T015)
- [ ] Coverage > 95%

### Phase 2 Complete When

- [ ] Correlation ID middleware working
- [ ] Logging to structured JSON format
- [ ] Middleware pipeline order correct
- [ ] All Phase 2 tests passing (T024-T030)
- [ ] Coverage > 95%

### Phase 3 Complete When

- [ ] useApi() composable working
- [ ] Error notifications displaying
- [ ] Error boundary catching errors
- [ ] Error pages rendering
- [ ] All Phase 3 tests passing (T041-T045)
- [ ] Coverage > 95%

### Phase 4 Complete When

- [ ] Arabic error messages in backend + frontend
- [ ] English translations present
- [ ] Form validation messages in Arabic
- [ ] RTL rendering verified
- [ ] All Phase 4 tests passing (T050)

### Phase 5 Complete When

- [ ] E2E validation error test passing
- [ ] E2E auth error test passing
- [ ] E2E RBAC error test passing
- [ ] E2E server error test passing
- [ ] E2E correlation ID test passing
- [ ] Security tests passing (XSS, PII)
- [ ] Performance benchmarks met
- [ ] Accessibility tests passing
- [ ] All Phase 5 tests passing (T053-T065)

### Final Validation

- [x] `composer run lint` passes
- [x] `composer run test` passes (all tests)
- [x] `npm run lint` passes (frontend)
- [x] `npm run typecheck` passes
- [x] `npm run test` passes (frontend all tests)
- [ ] Git commit with summary
- [ ] Merge into `main` branch

---

## Notes & References

### Task Dependencies Map

```
T001 ────→ T008 ─→ T010 ─→ T014-T015 ─→ Phase 2
         └→ T011 ┘   └→ T013 ─────────┘

T016-T018 ──→ T020 ──→ T026-T030 ────→ Phase 3
     └→ T024-T025 ┘

T031-T035 ──→ T041-T045 ────→ Phase 5
     └→ T036-T040 ┘ └→ T050

T046-T047, T048-T049 ──→ T052 ──→ Phase 5

T001-T050 ──→ T053-T065
```

### Skill References

- **error-handling-patterns/SKILL.md** — Exception class structure, error contract format
- **i18n-governance/SKILL.md** — Arabic/English localization, RTL support
- **api-testing-patterns/SKILL.md** — RBAC error testing, feature test structure
- **db-migration-governance/SKILL.md** — Migration creation for optional error_logs table
- **bootstrap-ui-system/SKILL.md** — Nuxt UI components (UButton, UNotification, etc.)
- **laravel-patterns/SKILL.md** — Service pattern, middleware, exception handling

### Architecture Authority

- **AGENTS.md** — Error response contract, RBAC rules
- **DESIGN.md** — Visual language for error pages (shadow-as-border, Geist fonts, RTL)
- **ADRs in docs/architecture/ADR/** — Check for related decisions

### Files to Create/Modify

**Total New Files:** 45  
**Total Modified Files:** 8  
**Total Lines of Code:** ~2,500 (logic) + ~5,000 (tests) = 7,500 LOC

---

**Generated:** 2026-04-11  
**Last Updated:** 2026-04-11  
**Authority:** plan.md, quickstart.md, data-model.md  
**Status:** IMPLEMENTATION COMPLETE
