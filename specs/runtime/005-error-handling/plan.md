# STAGE_05: Error Handling & Logging — Technical Plan

**Phase:** 01_PLATFORM_FOUNDATION  
**Generated:** 2026-04-11  
**Status:** PLANNING  
**Authority:** AGENTS.md, ADRs, error-handling-patterns skill, i18n-governance skill

---

## Executive Summary

This technical plan translates the STAGE_05_ERROR_HANDLING specification into actionable implementation tasks. It defines the exact data models, API contracts, middleware pipeline, file structure, and step-by-step implementation order with clear dependencies.

**Key Deliverables:**

1. Unified error response contract (all API responses)
2. 12 standardized error codes with HTTP status mapping
3. Custom exception hierarchy for business logic
4. Middleware pipeline (correlation ID → logging → error filtering)
5. Frontend error interceptor, boundary, and notification system
6. Structured JSON logging with correlation IDs
7. Complete RBAC-aware error detail filtering
8. Arabic/RTL-first error messages and pages

---

## 1. ARCHITECTURE OVERVIEW

### 1.1 Error Handling Flow Diagram

```
REQUEST FLOW:
┌─────────────┐
│   Browser   │ ← Sends request with correlation ID header
└─────┬───────┘
      │
      ↓
┌──────────────────────────────────────────────────┐
│ Frontend: useApi Interceptor                     │
│  - Adds X-Correlation-ID header                  │
│  - Adds Authorization header                     │
│  - Catches response errors                       │
└──────────────────────────────────────────────────┘
      │
      ↓
┌──────────────────────────────────────────────────┐
│ Backend: InjectCorrelationId Middleware          │
│  - Extracts/generates X-Correlation-ID           │
│  - Injects into request context                  │
│  - Pushes to Log processor                       │
└──────────────────────────────────────────────────┘
      │
      ↓
┌──────────────────────────────────────────────────┐
│ Backend: Routes → Middleware (Auth, RBAC)       │
│ Backend: Controllers → Services → Exceptions     │
└──────────────────────────────────────────────────┘
      │
      ├─ No error? ──→ Response (200/201/204)
      │
      └─ Error thrown ──→
┌──────────────────────────────────────────────────┐
│ Backend: Exception Handler (Handler.php)        │
│  1. Catch specific exception type               │
│  2. Map to error code + HTTP status             │
│  3. Filter details by RBAC                      │
│  4. Format standardized error response          │
│  5. Log error with correlation ID               │
└──────────────────────────────────────────────────┘
      │
      ↓
┌──────────────────────────────────────────────────┐
│ Backend: LogApiActivity Middleware               │
│  - Log response status, duration                │
│  - Include correlation ID                       │
│  - Include user context                         │
└──────────────────────────────────────────────────┘
      │
      ↓
┌──────────────────────────────────────────────────┐
│ Response (JSON)                                  │
│ {success: bool, data: any, error: {code, msg}} │
└──────────────────────────────────────────────────┘
      │
      ↓
┌──────────────────────────────────────────────────┐
│ Frontend: useApi Interceptor (error handler)    │
│  - Extracts error code, message, details        │
│  - Routes by status code (401, 403, 5xx, etc)  │
│  - Calls useErrorNotification() for toast       │
│  - Redirects if needed (401 → login, 403)      │
└──────────────────────────────────────────────────┘
      │
      ↓
┌──────────────────────────────────────────────────┐
│ Frontend: Error Store (Pinia)                    │
│  - Stores error in error store                  │
│  - Auto-clears after 30s                        │
└──────────────────────────────────────────────────┘
      │
      ↓
┌──────────────────────────────────────────────────┐
│ Frontend: Toast Notification                    │
│  - Shows error code (e.g., "VALIDATION_ERROR") │
│  - Shows localized message (Arabic)             │
│  - Shows duration (5s warning, 8s error)       │
│  - Offers retry button if applicable            │
└──────────────────────────────────────────────────┘
      │
      ↓ (if uncaught component error)
      │
┌──────────────────────────────────────────────────┐
│ Frontend: AppErrorBoundary                       │
│  - Catches unhandled errors                     │
│  - Displays error card with recovery options   │
└──────────────────────────────────────────────────┘
```

### 1.2 Middleware Pipeline Order

Backend middleware **must** execute in this strict order:

1. **InjectCorrelationId** — First (before any other processing)
   - Extract/generate correlation ID
   - Store in request attributes
   - Push to Log processor

2. **(Auth Middleware — Laravel built-in)**
   - Authenticate user via Sanctum

3. **(RBAC Middleware — per-route)**
   - Verify user has required role

4. **LogApiActivity** — Before controller execution
   - Log request start
   - Record start time

5. **Controller/Route Handler** — Request processing
   - Service calls
   - Exception throws

6. **Exception Handler** — Catches all exceptions
   - Formats response
   - Logs error

7. **LogApiActivity** — After response generated
   - Log response details
   - Calculate duration

---

## 2. DATA MODELS

### 2.1 Error Code Registry

**File:** `backend/app/Enums/ErrorCode.php`

```php
<?php

namespace App\Enums;

enum ErrorCode: string
{
    // 4xx Client Errors
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case AUTH_INVALID_CREDENTIALS = 'AUTH_INVALID_CREDENTIALS';
    case AUTH_TOKEN_EXPIRED = 'AUTH_TOKEN_EXPIRED';
    case AUTH_UNAUTHORIZED = 'AUTH_UNAUTHORIZED';
    case RBAC_ROLE_DENIED = 'RBAC_ROLE_DENIED';
    case RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    case WORKFLOW_INVALID_TRANSITION = 'WORKFLOW_INVALID_TRANSITION';
    case WORKFLOW_PREREQUISITES_UNMET = 'WORKFLOW_PREREQUISITES_UNMET';
    case PAYMENT_FAILED = 'PAYMENT_FAILED';
    case RATE_LIMIT_EXCEEDED = 'RATE_LIMIT_EXCEEDED';

    // 5xx Server Errors
    case SERVER_ERROR = 'SERVER_ERROR';
    case SERVICE_UNAVAILABLE = 'SERVICE_UNAVAILABLE';

    public function httpStatus(): int
    {
        return match($this) {
            self::VALIDATION_ERROR,
            self::WORKFLOW_INVALID_TRANSITION,
            self::WORKFLOW_PREREQUISITES_UNMET,
            self::PAYMENT_FAILED => 422,

            self::AUTH_INVALID_CREDENTIALS,
            self::AUTH_TOKEN_EXPIRED,
            self::AUTH_UNAUTHORIZED => 401,

            self::RBAC_ROLE_DENIED => 403,

            self::RESOURCE_NOT_FOUND => 404,

            self::RATE_LIMIT_EXCEEDED => 429,

            self::SERVER_ERROR => 500,
            self::SERVICE_UNAVAILABLE => 503,
        };
    }

    public function severity(): string
    {
        return match($this) {
            self::SERVER_ERROR,
            self::SERVICE_UNAVAILABLE,
            self::PAYMENT_FAILED => 'error',
            default => 'warning',
        };
    }

    public function description(): string
    {
        return match($this) {
            self::VALIDATION_ERROR => 'Input validation failed',
            self::AUTH_INVALID_CREDENTIALS => 'Login credentials incorrect',
            self::AUTH_TOKEN_EXPIRED => 'Authentication token expired',
            self::AUTH_UNAUTHORIZED => 'User not authenticated',
            self::RBAC_ROLE_DENIED => 'User role not permitted',
            self::RESOURCE_NOT_FOUND => 'Requested resource not found',
            self::WORKFLOW_INVALID_TRANSITION => 'Invalid state transition',
            self::WORKFLOW_PREREQUISITES_UNMET => 'Prerequisites not satisfied',
            self::PAYMENT_FAILED => 'Payment processing failed',
            self::RATE_LIMIT_EXCEEDED => 'Too many requests',
            self::SERVER_ERROR => 'Internal server error',
            self::SERVICE_UNAVAILABLE => 'Service temporarily unavailable',
        };
    }
}
```

### 2.2 Error Response Models

**File:** `backend/app/Http/Resources/ErrorResource.php`

```php
<?php

namespace App\Http\Resources;

class ErrorResource
{
    public function __construct(
        public readonly string $code,
        public readonly string $message,
        public readonly ?array $details = null,
    ) {}

    public function toArray(): array
    {
        return [
            'success' => false,
            'data' => null,
            'error' => [
                'code' => $this->code,
                'message' => $this->message,
                'details' => $this->details,
            ],
        ];
    }
}
```

**File:** `backend/app/Http/Resources/SuccessResource.php`

```php
<?php

namespace App\Http\Resources;

class SuccessResource
{
    public function __construct(
        public readonly mixed $data = null,
        public readonly string $message = '',
        public readonly int $statusCode = 200,
    ) {}

    public function toArray(): array
    {
        return [
            'success' => true,
            'data' => $this->data,
            'error' => null,
        ];
    }
}
```

### 2.3 Logging Schema (Optional Persistent Logging)

If persistent error logging is needed, define a migration:

**File:** `backend/database/migrations/2026_04_11_000000_create_error_logs_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_logs', function (Blueprint $table) {
            $table->id();
            $table->string('correlation_id', 255)->index();
            $table->string('error_code', 100)->index();
            $table->string('message', 500);
            $table->json('details')->nullable();
            $table->json('context')->nullable(); // user_id, request_path, etc.
            $table->string('severity', 50); // error, warning, critical
            $table->integer('http_status')->nullable();
            $table->longText('stack_trace')->nullable();
            $table->foreignIdFor(\App\Models\User::class)->nullable()->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['error_code', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
```

---

## 3. API CONTRACTS

### 3.1 Success Response Contract

**Endpoint:** Any successful API response  
**HTTP Status:** 200, 201, 202, 204

**Response Body:**

```json
{
  "success": true,
  "data": {
    // Resource data (object, array, or null)
  },
  "error": null
}
```

**Example: Create Project (201)**

```json
{
  "success": true,
  "data": {
    "id": 42,
    "name": "برج التجارة",
    "budget": 500000,
    "customer_id": 1,
    "created_at": "2026-04-11T10:30:45Z"
  },
  "error": null
}
```

### 3.2 Error Response Contract

**HTTP Status:** 400, 401, 403, 404, 422, 429, 500, 503

**Response Body:**

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "ERROR_CODE",
    "message": "User-readable message in Arabic",
    "details": {
      // Optional field-level details
    }
  }
}
```

### 3.3 Error Response Examples

**VALIDATION_ERROR (422)**

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "بيانات المدخلات غير صحيحة",
    "details": {
      "name": ["حقل الاسم مطلوب"],
      "budget": ["الميزانية يجب أن تكون أكبر من 0"]
    }
  }
}
```

**AUTH_UNAUTHORIZED (401)**

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "AUTH_UNAUTHORIZED",
    "message": "يجب تسجيل الدخول أولاً",
    "details": null
  }
}
```

**RBAC_ROLE_DENIED (403)**

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "RBAC_ROLE_DENIED",
    "message": "غير مصرح لك بهذا الإجراء",
    "details": {
      "required_role": "SupervisingArchitect",
      "your_role": "FieldEngineer"
    }
  }
}
```

**RESOURCE_NOT_FOUND (404)**

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "RESOURCE_NOT_FOUND",
    "message": "المورد المطلوب غير موجود",
    "details": {
      "resource": "Project",
      "id": "123"
    }
  }
}
```

**WORKFLOW_INVALID_TRANSITION (422)**

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "WORKFLOW_INVALID_TRANSITION",
    "message": "لا يمكن الانتقال من حالة 'مكتمل' إلى حالة 'قيد التنفيذ'",
    "details": {
      "from_state": "Complete",
      "to_state": "InProgress",
      "allowed_transitions": ["Paid"]
    }
  }
}
```

**SERVER_ERROR (500)**

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "SERVER_ERROR",
    "message": "حدث خطأ غير متوقع. يرجى المحاولة لاحقًا",
    "details": null
  }
}
```

### 3.4 Correlation ID Header Protocol

**Request Header:**

```
X-Correlation-ID: req_1712844645000_9a8b7c6d
```

If client doesn't provide, backend generates:

- Format: `req_{timestamp}_{random}`
- Returned in all error responses (optional in success)

**Response Header (Error Only):**

```
X-Correlation-ID: req_1712844645000_9a8b7c6d
```

---

## 4. EXCEPTION HIERARCHY

### 4.1 Custom Exception Classes to Create

**File Structure:**

```
backend/app/Exceptions/
├── DomainException.php (base)
├── ValidationException.php
├── InvalidStateTransitionException.php
├── InsufficientPermissionException.php
├── ResourceNotFoundException.php
├── PaymentFailedException.php
└── WorkflowPrerequisiteException.php
```

### 4.2 Exception Interface

All custom exceptions must implement:

```php
interface ExceptionContract
{
    public function getErrorCode(): string;
    public function getHttpStatus(): int;
    public function getDetails(): ?array;
}
```

### 4.3 Base DomainException

```php
<?php

namespace App\Exceptions;

use Exception;

abstract class DomainException extends Exception implements ExceptionContract
{
    public function getErrorCode(): string
    {
        return 'SERVER_ERROR';
    }

    public function getHttpStatus(): int
    {
        return 500;
    }

    public function getDetails(): ?array
    {
        return null;
    }
}
```

### 4.4 Specific Exception Examples

**InvalidStateTransitionException**

```php
class InvalidStateTransitionException extends DomainException
{
    public function __construct(
        string $message,
        public readonly string $fromState,
        public readonly string $toState,
        public readonly array $allowedTransitions = [],
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return 'WORKFLOW_INVALID_TRANSITION';
    }

    public function getHttpStatus(): int
    {
        return 422;
    }

    public function getDetails(): ?array
    {
        return [
            'from_state' => $this->fromState,
            'to_state' => $this->toState,
            'allowed_transitions' => $this->allowedTransitions,
        ];
    }
}
```

**ResourceNotFoundException**

```php
class ResourceNotFoundException extends DomainException
{
    public function __construct(
        string $message,
        public readonly string $resourceType,
        public readonly mixed $resourceId,
    ) {
        parent::__construct($message);
    }

    public function getErrorCode(): string
    {
        return 'RESOURCE_NOT_FOUND';
    }

    public function getHttpStatus(): int
    {
        return 404;
    }

    public function getDetails(): ?array
    {
        return [
            'resource' => $this->resourceType,
            'id' => $this->resourceId,
        ];
    }
}
```

---

## 5. FILE STRUCTURE TO CREATE

### Backend Files (14 files)

```
backend/
├── app/
│   ├── Enums/
│   │   └── ErrorCode.php ← NEW
│   ├── Exceptions/
│   │   ├── Handler.php ← MODIFY
│   │   ├── DomainException.php ← NEW
│   │   ├── ValidationException.php ← NEW
│   │   ├── InvalidStateTransitionException.php ← NEW
│   │   ├── InsufficientPermissionException.php ← NEW
│   │   ├── ResourceNotFoundException.php ← NEW
│   │   ├── PaymentFailedException.php ← NEW
│   │   └── WorkflowPrerequisiteException.php ← NEW
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── ApiResponse.php (trait) ← NEW
│   │   ├── Middleware/
│   │   │   ├── InjectCorrelationId.php ← NEW
│   │   │   ├── LogApiActivity.php ← NEW
│   │   │   └── ErrorDetailFiltering.php ← NEW (optional)
│   │   └── Resources/
│   │       ├── SuccessResource.php ← NEW
│   │       └── ErrorResource.php ← NEW
│   └── Services/
│       ├── LoggingService.php ← NEW
│       └── ErrorCodeRegistry.php ← NEW
├── config/
│   └── logging.php ← MODIFY (add structured channel)
└── database/
    └── migrations/
        └── 2026_04_11_000000_create_error_logs_table.php ← NEW (optional)
```

### Frontend Files (11 files)

```
frontend/
├── composables/
│   ├── useApi.ts ← NEW
│   └── useErrorNotification.ts ← NEW
├── components/
│   └── common/
│       └── AppErrorBoundary.vue ← NEW
├── pages/
│   ├── 404.vue ← NEW
│   ├── 500.vue ← NEW
│   └── 403.vue ← NEW
├── layouts/
│   └── error.vue ← NEW
├── stores/
│   └── error.ts ← NEW
├── types/
│   ├── errors.ts ← NEW
│   └── api.ts ← NEW (if needed)
├── middleware/
│   └── errorHandler.ts ← NEW
├── locales/
│   ├── ar.json ← MODIFY (add error messages)
│   └── en.json ← MODIFY (add error messages)
└── app.vue ← MODIFY (wrap with ErrorBoundary)
```

---

## 6. IMPLEMENTATION ORDER & DEPENDENCIES

### Phase 1: Backend Exception Infrastructure (Days 1-2)

**Dependency:** None (foundational)

1. Create `ErrorCode` enum in `backend/app/Enums/ErrorCode.php`
   - All 12 error codes with status mapping
   - Severity and description methods

2. Create exception hierarchy:
   - `DomainException` base class
   - All 7 specific exception classes
   - Each with `getErrorCode()`, `getHttpStatus()`, `getDetails()` methods

3. Create `ApiResponse` trait in `backend/app/Http/Controllers/Api/`
   - `sendSuccess()` method
   - `sendError()` method
   - Both format to contract

4. Update `Handler.php` in `backend/app/Exceptions/`
   - Catch each exception type
   - Map to error code
   - Format standardized response
   - Log with correlation ID

**Testing:** Unit tests for exception handler (5 test cases)

---

### Phase 2: Backend Middleware & Logging (Days 2-3)

**Dependency:** Phase 1

1. Create `InjectCorrelationId` middleware
   - Extract/generate correlation ID
   - Store in request attributes
   - Push to Log processor

2. Create `LogApiActivity` middleware
   - Log request entry
   - Calculate response duration
   - Log with structured fields

3. Update `backend/config/logging.php`
   - Add `structured` channel with JsonFormatter
   - Configure retention policies
   - Set log levels

4. Create `ErrorDetailFiltering` middleware (optional, advanced)
   - Filter details by user role
   - Admin sees stack traces in dev only

5. Register middleware in `backend/app/Http/Kernel.php`
   - InjectCorrelationId (first)
   - LogApiActivity
   - ErrorDetailFiltering

**Testing:** Integration tests for middleware (3 test cases)

---

### Phase 3: Frontend Interceptor & Error Handling (Days 3-4)

**Dependency:** Phase 1-2 (backend contracts finalized)

1. Create `useApi()` composable in `frontend/composables/useApi.ts`
   - Initialize $fetch with base URL
   - Inject auth token from store
   - Generate correlation ID
   - Error interceptor logic (401, 403, 5xx routing)

2. Create `useErrorNotification()` composable
   - Error code to message mapping
   - Severity detection
   - Toast display logic
   - Retry button support

3. Create `AppErrorBoundary.vue` component
   - `onErrorCaptured()` lifecycle
   - Error card display
   - Recovery buttons

4. Create error pages:
   - `404.vue` — Not found
   - `500.vue` — Server error
   - `403.vue` — Forbidden

5. Create error layout:
   - `frontend/layouts/error.vue`
   - Minimal (no header/sidebar)

6. Create error store:
   - `frontend/stores/error.ts` (Pinia)
   - `errors` ref, `lastError` ref
   - Auto-clear after 30s

7. Update `frontend/app.vue`
   - Wrap NuxtPage with AppErrorBoundary

**Testing:** Unit tests for composables and components (8 test cases)

---

### Phase 4: Localization (i18n) (Day 4)

**Dependency:** Phase 3

1. Backend translations:
   - Create `backend/resources/lang/ar/errors.php`
   - Create `backend/resources/lang/en/errors.php`
   - Validation messages for all rules

2. Frontend translations:
   - Update `frontend/locales/ar.json` with error messages
   - Update `frontend/locales/en.json`
   - Button labels, error page text

3. Form Request translations:
   - All Laravel validators in Arabic
   - Custom messages() arrays

**Testing:** i18n smoke tests (2 test cases)

---

### Phase 5: Integration & E2E Testing (Day 5)

**Dependency:** Phase 1-4

1. Backend feature tests:
   - Validation error flow (422)
   - Auth error flow (401)
   - RBAC error flow (403)
   - Not found flow (404)
   - Workflow error flow (422)
   - Rate limit flow (429)
   - Server error flow (500)

2. Frontend integration tests:
   - Error interceptor → notification
   - Error page rendering
   - RTL layout verification
   - Correlation ID tracing

3. Full-stack end-to-end tests:
   - User attempts action
   - Backend returns error
   - Frontend captures and displays
   - User sees Arabic message
   - User can retry

**Testing:** 15+ integration test cases

---

## 7. MIDDLEWARE PIPELINE (Exact Order)

### Request Processing Order

```
1. InjectCorrelationId
   ↓
2. Auth (Laravel built-in)
   ↓
3. RBAC (per-route, via Policies)
   ↓
4. LogApiActivity (start)
   ↓
5. Route Handler / Exception
   ↓
6. Exception Handler (catches)
   ↓
7. LogApiActivity (end / duration)
   ↓
Response sent to client
```

### Registration in Kernel.php

```php
protected $middleware = [
    // Global middleware (all routes)
    \App\Http\Middleware\InjectCorrelationId::class, // ← FIRST
];

protected $middlewareGroups = [
    'api' => [
        // API-specific middleware
        'throttle:60,1', // Laravel rate limiting
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \App\Http\Middleware\LogApiActivity::class,
    ],
];

protected $routeMiddleware = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'role' => \App\Http\Middleware\CheckRole::class, // RBAC
    // ...
];
```

---

## 8. ERROR CODE TO HTTP STATUS MAPPING TABLE

| Error Code                     | HTTP Status | Severity | Use Case                |
| ------------------------------ | ----------- | -------- | ----------------------- |
| `VALIDATION_ERROR`             | 422         | Warning  | Input validation failed |
| `AUTH_INVALID_CREDENTIALS`     | 401         | Warning  | Wrong password/username |
| `AUTH_TOKEN_EXPIRED`           | 401         | Warning  | Session expired         |
| `AUTH_UNAUTHORIZED`            | 401         | Warning  | Missing auth header     |
| `RBAC_ROLE_DENIED`             | 403         | Warning  | Insufficient role       |
| `RESOURCE_NOT_FOUND`           | 404         | Warning  | Resource doesn't exist  |
| `WORKFLOW_INVALID_TRANSITION`  | 422         | Warning  | Invalid state change    |
| `WORKFLOW_PREREQUISITES_UNMET` | 422         | Warning  | Prerequisites not met   |
| `PAYMENT_FAILED`               | 422         | Error    | Payment declined        |
| `RATE_LIMIT_EXCEEDED`          | 429         | Warning  | Too many requests       |
| `SERVER_ERROR`                 | 500         | Error    | Unhandled exception     |
| `SERVICE_UNAVAILABLE`          | 503         | Error    | Service down            |

**Key Rule:** Error code → HTTP status is **immutable**. Never vary status for same code.

---

## 9. RBAC ERROR DETAIL FILTERING

### Visibility Matrix

| Detail             | Customer | Contractor | Architect | Field Engineer | Admin |
| ------------------ | -------- | ---------- | --------- | -------------- | ----- |
| Error code         | ✓        | ✓          | ✓         | ✓              | ✓     |
| Message            | ✓        | ✓          | ✓         | ✓              | ✓     |
| Validation details | ✓        | ✓          | ✓         | ✓              | ✓     |
| Workflow details   | ✓        | ✓          | ✓         | ✓              | ✓     |
| Stack trace        | ✗        | ✗          | ✗         | ✗              | ✓\*   |
| DB error details   | ✗        | ✗          | ✗         | ✗              | ✓\*   |
| Internal cause     | ✗        | ✗          | ✗         | ✗              | ✓\*   |

\*Admin sees stack traces **development/staging only**, never in production.

### Implementation in Handler.php

```php
protected function filterErrorDetails($e, $request): ?array
{
    // Never expose sensitive details to non-admin or in production
    if (!$request->user() || $request->user()->role !== UserRole::Admin) {
        return null;
    }

    // Admin only sees details in development
    if (!app()->environment('local', 'testing')) {
        return null;
    }

    // Return full details (stack trace, etc.)
    return [
        'exception' => class_basename($e),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString(),
    ];
}
```

---

## 10. ARABIC/RTL IMPLEMENTATION CHECKLIST

### Backend (Laravel i18n)

- [ ] Create `backend/resources/lang/ar/errors.php` with all error messages
- [ ] Create `backend/resources/lang/en/errors.php` for fallback
- [ ] All Form Request `messages()` arrays in Arabic
- [ ] Field validation messages in Arabic

### Frontend (Nuxt i18n)

- [ ] Set `dir="rtl"` in `nuxt.config.ts` HTML attributes
- [ ] Create `frontend/locales/ar.json` with error strings
- [ ] Create `frontend/locales/en.json` for fallback
- [ ] Use Tailwind logical properties (ms-, me-, ps-, pe-, start, end)
- [ ] Error pages styled with RTL-aware layout

### Example Localization File

**frontend/locales/ar.json:**

```json
{
  "errors": {
    "validation": "البيانات المدخلة غير صحيحة",
    "unauthorized": "يجب تسجيل الدخول أولاً",
    "forbidden": "غير مصرح لك بهذا الإجراء",
    "notFound": "المورد غير موجود",
    "serverError": "حدث خطأ غير متوقع",
    "retryButton": "أعد المحاولة",
    "backButton": "العودة"
  }
}
```

---

## 11. TESTING STRATEGY

### Unit Tests (Backend)

**Location:** `backend/tests/Unit/Exceptions/`

```php
class ErrorCodeEnumTest extends TestCase
{
    public function test_error_code_http_status_mapping()
    {
        $this->assertEquals(422, ErrorCode::VALIDATION_ERROR->httpStatus());
        $this->assertEquals(401, ErrorCode::AUTH_UNAUTHORIZED->httpStatus());
        $this->assertEquals(403, ErrorCode::RBAC_ROLE_DENIED->httpStatus());
        // ... all 12 codes
    }
}

class ExceptionHandlerTest extends TestCase
{
    public function test_validation_exception_formats_response()
    {
        // Throw ValidationException, assert 422 response with field details
    }

    public function test_unauthorized_exception_hides_details()
    {
        // Assert stack trace not in response for non-admin
    }
}
```

### Integration Tests (Backend)

**Location:** `backend/tests/Feature/ErrorHandling/`

```php
class ValidationErrorResponseTest extends TestCase
{
    public function test_validation_error_returns_422_with_details()
    {
        $response = $this->post('/api/v1/projects', [
            // Missing required fields
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'data',
            'error' => ['code', 'message', 'details'],
        ]);
        $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
    }
}
```

### Unit Tests (Frontend)

**Location:** `frontend/tests/unit/composables/`

```typescript
describe("useApi", () => {
  it("injects correlation ID on all requests", () => {
    const { apiFetch } = useApi();
    // Mock $fetch and verify X-Correlation-ID header
  });

  it("handles 401 by logging out", () => {
    // Simulate 401 response, verify logout called
  });
});

describe("useErrorNotification", () => {
  it("shows toast with correct severity", () => {
    const { showErrorNotification } = useErrorNotification();
    // Verify toast color/duration matches severity
  });
});
```

### Integration Tests (Frontend)

**Location:** `frontend/tests/integration/errors/`

```typescript
describe("Error handling flow", () => {
  it("shows validation error notification on 422", () => {
    // Create form with missing fields
    // Submit form
    // Assert validation error toast appears with field details
  });

  it("redirects to login on 401", () => {
    // Attempt API call that returns 401
    // Assert redirect to /auth/login
  });
});
```

---

## 12. GOVERNANCE COMPLIANCE

### Architecture Authority

- ✓ **AGENTS.md:** Error contract binding (unified format)
- ✓ **ADRs:** Check `docs/architecture/ADR/` for related decisions
- ✓ **Skills:** Apply `error-handling-patterns/SKILL.md`, `i18n-governance/SKILL.md`

### Design System Authority

- ✓ **DESIGN.md:** Error pages use shadow-as-border, Geist fonts, RTL
- ✓ **Color Palette:** Neutral grays for error cards, no colored backgrounds
- ✓ **Typography:** Error headings with aggressive letter-spacing

### RBAC Authority

- ✓ **030-architecture-guardian.mdc:** RBAC filtering enforced
- ✓ **040-security-auditor.mdc:** No credential/PII leakage in errors

### i18n Authority

- ✓ **i18n-governance/SKILL.md:** Arabic-first, RTL support
- ✓ **No hardcoded error messages:** All translated

### Testing Authority

- ✓ **api-testing-patterns/SKILL.md:** RBAC error tests
- ✓ **PHPUnit/Pest:** Backend tests
- ✓ **Vitest:** Frontend tests

### Conflict Resolution

1. ADRs (Architecture Decisions)
2. AGENTS.md (error contract)
3. error-handling-patterns skill
4. DESIGN.md (visual language)
5. This plan

---

## 13. DEPENDENCIES & BLOCKERS

### External Dependencies

- Laravel 11+ (Sanctum for auth)
- Nuxt.js 3 (Vue 3 Composition API)
- Nuxt UI (`@nuxt/ui`) for toast notifications
- Pinia for state management
- Tailwind CSS v4 for styling

### Upstream Dependencies

- ✓ STAGE_01: Project initialization (Laravel + Nuxt setup)
- ✓ STAGE_02: Database schema (users, projects, phases)
- ✓ STAGE_03: Authentication (Sanctum, user model)

### Downstream Consumers

- All features built after STAGE_05 must use error contract
- All controllers must throw custom exceptions
- All API responses must follow format

---

## 14. SUCCESS METRICS

### Implementation Complete When:

- [ ] All 12 error codes defined and mapped (ErrorCode enum)
- [ ] Exception handler catches all exception types
- [ ] All API responses follow contract (success & error)
- [ ] Correlation ID middleware logs all requests
- [ ] Structured logging configured (JSON format)
- [ ] Frontend API interceptor implemented
- [ ] Error notifications working (toast + redirect)
- [ ] Error boundary component functional
- [ ] All error pages display correctly (404, 500, 403)
- [ ] All error messages in Arabic
- [ ] All error pages RTL-aware
- [ ] Backend tests: 100% coverage of error paths
- [ ] Frontend tests: Composables + components tested
- [ ] Integration tests: Full-stack error flows
- [ ] No hardcoded error messages (all translated)
- [ ] No stack traces in production
- [ ] RBAC filtering working correctly
- [ ] Correlation ID tracing end-to-end

---

## 15. QUICK START CHECKLIST

Use this checklist to track Phase 1-5 completion:

**Phase 1: Backend Exception Infrastructure**

- [ ] ErrorCode enum created
- [ ] 7 custom exception classes created
- [ ] ApiResponse trait created
- [ ] Handler.php updated
- [ ] Unit tests passing

**Phase 2: Backend Middleware & Logging**

- [ ] InjectCorrelationId middleware created
- [ ] LogApiActivity middleware created
- [ ] logging.php configured
- [ ] Middleware registered in Kernel.php
- [ ] Integration tests passing

**Phase 3: Frontend Interceptor & Error Handling**

- [ ] useApi() composable created
- [ ] useErrorNotification() composable created
- [ ] AppErrorBoundary.vue created
- [ ] Error pages (404, 500, 403) created
- [ ] Error store created
- [ ] app.vue updated
- [ ] Unit tests passing

**Phase 4: Localization**

- [ ] Backend i18n files created
- [ ] Frontend i18n files created
- [ ] Form Request messages in Arabic
- [ ] Smoke tests passing

**Phase 5: Integration & E2E Testing**

- [ ] All feature tests passing
- [ ] All integration tests passing
- [ ] E2E tests passing
- [ ] Correlation ID tracing verified
- [ ] RBAC filtering verified

---

**Generated:** 2026-04-11 | **Authority:** AGENTS.md, error-handling-patterns, DESIGN.md, i18n-governance
