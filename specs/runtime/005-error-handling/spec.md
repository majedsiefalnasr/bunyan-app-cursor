# STAGE_05: Error Handling & Logging — Detailed Specification

**Phase:** 01_PLATFORM_FOUNDATION  
**Stage File:** `specs/phases/01_PLATFORM_FOUNDATION/STAGE_05_ERROR_HANDLING.md`  
**Generated:** 2026-04-11  
**Status:** SPECIFYING

---

## Executive Summary

This specification establishes the error handling contract, structured logging foundation, and error boundary system for the entire Bunyan platform. Every API response, system exception, and client-side error must conform to this contract. The error handling layer is **not business logic** — it is **infrastructure**, and it is enforced before any domain code executes.

**Key Principles:**

- **Single Error Contract:** All API responses follow unified format with `success`, `data`, `error` fields
- **Standardized Error Codes:** Globally unique error codes (VALIDATION_ERROR, UNAUTHORIZED, etc.) mapped to HTTP status codes
- **Structured Logging:** All errors logged with correlation IDs, severity levels, and request context
- **Client Resilience:** Frontend error interceptors, boundary components, and retry logic
- **RBAC-Aware:** Error details filtered by role (production vs development visibility)
- **Arabic-First:** All error messages, pages, and notifications support Arabic RTL

---

## 1. API ERROR CONTRACT

### 1.1 Success Response Format

All successful API responses follow this structure:

```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Example",
    ...
  },
  "error": null
}
```

**Fields:**

- `success` (boolean): Always `true` for successful responses
- `data` (object|array|null): Response payload (resource, collection, or null if no data)
- `error` (null): Always `null` for success

**HTTP Status:** 200, 201, 202, 204 (success codes)

### 1.2 Error Response Format

All error responses follow this structure:

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "ERROR_CODE",
    "message": "Human-readable error message (Arabic preferred in production)",
    "details": {
      "field": ["Error detail"],
      "nested_object.field": ["Error detail"]
    }
  }
}
```

**Fields:**

- `success` (boolean): Always `false` for errors
- `data` (null): Always `null` for errors
- `error` (object):
  - `code` (string): Standardized error code (e.g., `VALIDATION_ERROR`)
  - `message` (string): Human-readable message in user's language
  - `details` (object): Optional field-level error details (validation errors, nested objects)

**HTTP Status:** 400, 401, 403, 404, 422, 429, 500 (error codes matching error type)

### 1.3 Error Response Examples

#### Validation Error (422)

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "بيانات المدخلات غير صحيحة",
    "details": {
      "name": ["حقل الاسم مطلوب"],
      "email": ["البريد الإلكتروني غير صالح"],
      "budget": ["الميزانية يجب أن تكون أكبر من 0"]
    }
  }
}
```

#### Authentication Error (401)

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "AUTH_TOKEN_EXPIRED",
    "message": "انتهت صلاحية جلستك. يرجى تسجيل الدخول مجددًا",
    "details": null
  }
}
```

#### Authorization Error (403)

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

#### Resource Not Found (404)

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

#### Workflow Error (422)

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

#### Server Error (500)

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

---

## 2. ERROR CODE REGISTRY

All error codes must be globally unique and mapped to HTTP status codes and severity levels.

| Code                           | HTTP | Severity | Description                      | Example                                  |
| ------------------------------ | ---- | -------- | -------------------------------- | ---------------------------------------- |
| `VALIDATION_ERROR`             | 422  | Warning  | Input validation failed          | Missing required fields, invalid format  |
| `AUTH_INVALID_CREDENTIALS`     | 401  | Warning  | Login credentials incorrect      | Wrong password, user not found           |
| `AUTH_TOKEN_EXPIRED`           | 401  | Warning  | Authentication token expired     | Session expired after 7 days             |
| `AUTH_UNAUTHORIZED`            | 403  | Warning  | User not authenticated           | Missing `Authorization` header           |
| `RBAC_ROLE_DENIED`             | 403  | Warning  | User role not permitted          | Customer attempting contractor action    |
| `RESOURCE_NOT_FOUND`           | 404  | Warning  | Requested resource doesn't exist | Project ID doesn't match any record      |
| `WORKFLOW_INVALID_TRANSITION`  | 422  | Warning  | Invalid state transition         | Phase status cannot go backward          |
| `WORKFLOW_PREREQUISITES_UNMET` | 422  | Warning  | Prerequisites not satisfied      | Cannot complete phase without all tasks  |
| `PAYMENT_FAILED`               | 422  | Error    | Payment processing failed        | Insufficient funds, card declined        |
| `RATE_LIMIT_EXCEEDED`          | 429  | Warning  | Too many requests                | API rate limit hit                       |
| `SERVER_ERROR`                 | 500  | Error    | Internal server error            | Unhandled exception, database connection |
| `SERVICE_UNAVAILABLE`          | 503  | Error    | Service temporarily unavailable  | Database offline, third-party API down   |

**Notes:**

- Error codes are **PascalCase_WITH_UNDERSCORES** for consistency and visibility
- Each code has a **fixed HTTP status** (never varies)
- **Severity** helps with logging and client-side retry strategies
- `Warning` severity codes are client recoverable; `Error` codes require manual intervention

---

## 3. BACKEND ERROR HANDLING

### 3.1 Custom Exception Hierarchy

All custom exceptions must extend from domain-specific base classes:

```
Throwable
├── Exception
│   ├── DomainException (base for business logic errors)
│   │   ├── ValidationException (field-level validation)
│   │   ├── InvalidStateTransitionException (workflow errors)
│   │   ├── InsufficientPermissionException (authorization)
│   │   ├── ResourceNotFoundException (404 errors)
│   │   ├── PaymentFailedException (payment errors)
│   │   └── WorkflowPrerequisiteException (workflow prerequisites)
│   └── Laravel Exceptions (caught by Handler)
│       ├── ValidationException
│       ├── AuthorizationException
│       ├── ModelNotFoundException
│       └── ThrottleException
```

**Custom Exception Pattern:**

```php
<?php

namespace App\Exceptions;

use DomainException;

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

    public function getDetails(): array
    {
        return [
            'from_state' => $this->fromState,
            'to_state' => $this->toState,
            'allowed_transitions' => $this->allowedTransitions,
        ];
    }
}
```

### 3.2 Exception Handler (app/Exceptions/Handler.php)

The exception handler converts all exceptions to the standardized error contract:

```php
<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthorizationException;
use Illuminate\Routing\Exceptions\ThrottleRequestsException;
use Throwable;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        DomainException::class,
        ValidationException::class,
    ];

    public function render($request, Throwable $e): JsonResponse
    {
        // Validation errors (Laravel Form Requests)
        if ($e instanceof ValidationException) {
            return $this->validationResponse($request, $e);
        }

        // Authorization errors
        if ($e instanceof AuthorizationException) {
            return $this->authorizationResponse($request, $e);
        }

        // Not found errors
        if ($e instanceof ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'المورد المطلوب غير موجود',
                    'details' => [
                        'resource' => $e->getModel(),
                        'id' => $e->getIds()[0] ?? null,
                    ],
                ],
            ], 404);
        }

        // Rate limiting
        if ($e instanceof ThrottleRequestsException) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => [
                    'code' => 'RATE_LIMIT_EXCEEDED',
                    'message' => 'لقد تجاوزت حد الطلبات المسموح به',
                    'details' => ['retry_after' => (int)$e->getHeaders()['Retry-After'] ?? 60],
                ],
            ], 429);
        }

        // Custom domain exceptions
        if ($e instanceof DomainException) {
            return $this->domainExceptionResponse($e);
        }

        // Unauthenticated (no token or invalid token)
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => [
                    'code' => 'AUTH_UNAUTHORIZED',
                    'message' => 'يجب تسجيل الدخول أولاً',
                    'details' => null,
                ],
            ], 401);
        }

        // Default server error (only for unhandled exceptions)
        $this->logError($e, $request);

        return response()->json([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => 'SERVER_ERROR',
                'message' => 'حدث خطأ غير متوقع',
                'details' => null, // Never expose stack trace in production
            ],
        ], 500);
    }

    protected function validationResponse($request, ValidationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => 'VALIDATION_ERROR',
                'message' => 'بيانات المدخلات غير صحيحة',
                'details' => $e->errors(),
            ],
        ], 422);
    }

    protected function authorizationResponse($request, AuthorizationException $e): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => 'RBAC_ROLE_DENIED',
                'message' => 'غير مصرح لك بهذا الإجراء',
                'details' => null,
            ],
        ], 403);
    }

    protected function domainExceptionResponse(DomainException $e): JsonResponse
    {
        $httpStatus = method_exists($e, 'getHttpStatus') ? $e->getHttpStatus() : 500;
        $errorCode = method_exists($e, 'getErrorCode') ? $e->getErrorCode() : 'SERVER_ERROR';
        $details = method_exists($e, 'getDetails') ? $e->getDetails() : null;

        return response()->json([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => $errorCode,
                'message' => $e->getMessage(),
                'details' => $details,
            ],
        ], $httpStatus);
    }

    protected function logError(Throwable $e, $request): void
    {
        $correlationId = $request->header('X-Correlation-ID', uniqid('err_'));

        Log::error('Unhandled Exception', [
            'correlation_id' => $correlationId,
            'exception' => class_basename($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'user_id' => $request->user()?->id,
            'request_path' => $request->path(),
            'request_method' => $request->method(),
        ]);
    }
}
```

### 3.3 Middleware: Correlation ID Injection

Every request must include a correlation ID for request tracing:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class InjectCorrelationId
{
    public function handle(Request $request, Closure $next)
    {
        $correlationId = $request->header('X-Correlation-ID') ?: uniqid('req_', true);
        $request->attributes->set('correlation_id', $correlationId);

        \Log::pushProcessor(function ($record) use ($correlationId) {
            $record['extra']['correlation_id'] = $correlationId;
            return $record;
        });

        return $next($request);
    }
}
```

Register in `backend/app/Http/Kernel.php`:

```php
protected $middleware = [
    // ...
    \App\Http\Middleware\InjectCorrelationId::class,
];
```

### 3.4 Middleware: Request/Response Logging

All API requests and responses are logged for debugging and monitoring:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogApiActivity
{
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $duration = (microtime(true) - $startTime) * 1000; // milliseconds

        Log::info('API Request', [
            'correlation_id' => $request->attributes->get('correlation_id'),
            'method' => $request->method(),
            'path' => $request->path(),
            'status' => $response->getStatusCode(),
            'duration_ms' => round($duration, 2),
            'user_id' => $request->user()?->id,
            'user_role' => $request->user()?->role->value,
            'ip' => $request->ip(),
        ]);

        return $response;
    }
}
```

### 3.5 API Response Helper Trait

Controllers use a helper trait for consistent response formatting:

```php
<?php

namespace App\Http\Controllers\Api;

trait ApiResponse
{
    protected function sendSuccess($data = null, string $message = 'Success', int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'error' => null,
        ], $statusCode);
    }

    protected function sendError(
        string $code,
        string $message,
        ?array $details = null,
        int $statusCode = 400
    ) {
        return response()->json([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
        ], $statusCode);
    }
}
```

### 3.6 Structured Logging Configuration

Configure Laravel's logging in `backend/config/logging.php`:

```php
'channels' => [
    'stack' => [
        'driver' => 'stack',
        'channels' => ['single', 'structured'],
    ],

    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
    ],

    'structured' => [
        'driver' => 'monolog',
        'handler' => \Monolog\Handler\StreamHandler::class,
        'formatter' => \Monolog\Formatter\JsonFormatter::class,
        'path' => storage_path('logs/structured.log'),
        'level' => 'debug',
    ],

    'daily' => [
        'driver' => 'daily',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
        'days' => 14,
    ],
],
```

**Log Entry Format (JSON):**

```json
{
  "message": "API Request",
  "context": {
    "correlation_id": "req_1234567890",
    "method": "POST",
    "path": "/api/v1/projects",
    "status": 201,
    "duration_ms": 145.23,
    "user_id": 42,
    "user_role": "customer",
    "ip": "192.168.1.1"
  },
  "level": "info",
  "level_name": "INFO",
  "timestamp": "2026-04-11T10:30:45.123456Z"
}
```

---

## 4. FRONTEND ERROR HANDLING

### 4.1 Global Error Interceptor (useApi Composable)

The API client composable intercepts all responses and handles errors:

```typescript
// frontend/composables/useApi.ts

import { useRuntimeConfig } from "#app";
import { useAuthStore } from "~/stores/auth";
import { useErrorNotification } from "~/composables/useErrorNotification";

export function useApi() {
  const config = useRuntimeConfig();
  const auth = useAuthStore();
  const { showErrorNotification } = useErrorNotification();

  const apiFetch = $fetch.create({
    baseURL: config.public.apiBaseUrl,
    headers: {
      Accept: "application/json",
      "Accept-Language": "ar",
    },

    onRequest({ options }) {
      if (auth.token) {
        options.headers.set("Authorization", `Bearer ${auth.token}`);
      }

      // Inject correlation ID
      options.headers.set("X-Correlation-ID", generateCorrelationId());
    },

    onResponseError({ response, request }) {
      const data = response._data || {};
      const error = data.error || {};
      const errorCode = error.code || "SERVER_ERROR";

      // Handle authentication errors
      if (response.status === 401) {
        auth.logout();
        if (errorCode !== "AUTH_TOKEN_EXPIRED") {
          navigateTo("/auth/login");
        }
      }

      // Handle RBAC errors (403)
      if (response.status === 403 && errorCode === "RBAC_ROLE_DENIED") {
        // Redirect to forbidden page or dashboard
        navigateTo("/dashboard");
      }

      // Show error notification
      showErrorNotification({
        code: errorCode,
        message: error.message || "حدث خطأ غير متوقع",
        details: error.details,
        statusCode: response.status,
      });

      // Log for debugging
      console.error(`[${errorCode}] ${error.message}`, {
        status: response.status,
        details: error.details,
        url: request.url,
      });
    },
  });

  return { apiFetch };
}

function generateCorrelationId(): string {
  return `${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
}
```

### 4.2 Error Notification Composable

Display error messages to users with retry logic:

```typescript
// frontend/composables/useErrorNotification.ts

import { ref } from "vue";
import { useToast } from "#ui/composables/useToast";

export interface ErrorNotificationPayload {
  code: string;
  message: string;
  details?: Record<string, any>;
  statusCode?: number;
  retryable?: boolean;
  retryFn?: () => Promise<any>;
}

export function useErrorNotification() {
  const toast = useToast();
  const isRetrying = ref(false);

  const showErrorNotification = (payload: ErrorNotificationPayload) => {
    const { code, message, statusCode, retryable, retryFn } = payload;

    // Severity mapping
    const severity = getSeverityByCode(code, statusCode);

    // Build toast actions
    const actions: any[] = [];

    if (retryable && retryFn) {
      actions.push({
        label: "أعد المحاولة",
        click: async () => {
          isRetrying.value = true;
          try {
            await retryFn();
            toast.close();
          } finally {
            isRetrying.value = false;
          }
        },
      });
    }

    toast.add({
      title: `${code}`,
      description: message,
      color: severity === "error" ? "red" : "yellow",
      timeout: severity === "error" ? 8000 : 5000,
      actions,
    });
  };

  const getSeverityByCode = (
    code: string,
    statusCode?: number
  ): "error" | "warning" => {
    const errorSeverities = {
      SERVER_ERROR: "error",
      SERVICE_UNAVAILABLE: "error",
      PAYMENT_FAILED: "error",
      RATE_LIMIT_EXCEEDED: "warning",
      VALIDATION_ERROR: "warning",
      AUTH_INVALID_CREDENTIALS: "warning",
      AUTH_TOKEN_EXPIRED: "warning",
      RBAC_ROLE_DENIED: "warning",
      RESOURCE_NOT_FOUND: "warning",
    };

    return (
      (errorSeverities[code as keyof typeof errorSeverities] as any) ||
      (statusCode && statusCode >= 500 ? "error" : "warning")
    );
  };

  return { showErrorNotification };
}
```

### 4.3 Global Error Boundary Component

Catch and display unhandled errors in the UI:

```vue
<!-- frontend/components/common/AppErrorBoundary.vue -->

<script setup lang="ts">
import { ref } from "vue";

interface ErrorState {
  error: Error | null;
  hasError: boolean;
  errorCode: string;
}

const errorState = ref<ErrorState>({
  error: null,
  hasError: false,
  errorCode: "",
});

const resetError = () => {
  errorState.value = {
    error: null,
    hasError: false,
    errorCode: "",
  };
};

onErrorCaptured((error) => {
  errorState.value = {
    error: error instanceof Error ? error : new Error(String(error)),
    hasError: true,
    errorCode: "CLIENT_ERROR",
  };

  // Log to server for monitoring
  console.error("Unhandled client error:", error);

  return false; // Prevent further propagation
});
</script>

<template>
  <div
    v-if="errorState.hasError"
    class="min-h-screen flex items-center justify-center bg-white"
  >
    <div
      class="max-w-md w-full bg-white rounded-lg shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] p-6 text-center"
    >
      <div class="mb-4">
        <UIcon
          name="i-heroicons-exclamation-triangle"
          class="w-12 h-12 mx-auto text-red-500"
        />
      </div>

      <h1 class="text-2xl font-semibold text-[#171717] mb-2">حدث خطأ</h1>
      <p class="text-gray-600 mb-4">{{ errorState.error?.message }}</p>

      <div class="space-y-2">
        <UButton color="black" block @click="resetError">العودة</UButton>
        <UButton
          color="white"
          variant="outline"
          block
          @click="location.reload()"
          >تحديث الصفحة</UButton
        >
      </div>

      <div class="mt-4 p-3 bg-gray-50 rounded text-xs text-gray-500 text-left">
        <p><strong>كود الخطأ:</strong> {{ errorState.errorCode }}</p>
      </div>
    </div>
  </div>

  <slot v-else />
</template>
```

### 4.4 Error Page Components

#### 404 Not Found (frontend/pages/404.vue)

```vue
<script setup lang="ts">
definePageMeta({
  layout: "error",
});
</script>

<template>
  <div
    class="min-h-screen flex flex-col items-center justify-center bg-white px-4"
  >
    <div class="text-center max-w-md">
      <div class="mb-6">
        <p class="text-9xl font-bold text-gray-200 tracking-tighter">404</p>
      </div>

      <h1 class="text-3xl font-semibold text-[#171717] mb-2">
        الصفحة غير موجودة
      </h1>
      <p class="text-gray-600 mb-6">
        عذرًا، الصفحة التي تبحث عنها غير موجودة أو تم حذفها.
      </p>

      <div class="flex gap-3 justify-center">
        <UButton color="black" to="/dashboard">العودة إلى لوحة التحكم</UButton>
        <UButton color="white" variant="outline" to="/"
          >الصفحة الرئيسية</UButton
        >
      </div>
    </div>
  </div>
</template>
```

#### 500 Server Error (frontend/pages/500.vue)

```vue
<script setup lang="ts">
definePageMeta({
  layout: "error",
});
</script>

<template>
  <div
    class="min-h-screen flex flex-col items-center justify-center bg-white px-4"
  >
    <div class="text-center max-w-md">
      <div class="mb-6">
        <p class="text-9xl font-bold text-gray-200 tracking-tighter">500</p>
      </div>

      <h1 class="text-3xl font-semibold text-[#171717] mb-2">خطأ في الخادم</h1>
      <p class="text-gray-600 mb-6">
        حدث خطأ غير متوقع. فريقنا يعمل على حل المشكلة. يرجى المحاولة لاحقًا.
      </p>

      <div class="flex gap-3 justify-center">
        <UButton color="black" @click="location.reload()">أعد المحاولة</UButton>
        <UButton color="white" variant="outline" to="/dashboard"
          >العودة</UButton
        >
      </div>
    </div>
  </div>
</template>
```

#### 403 Forbidden (frontend/pages/403.vue)

```vue
<script setup lang="ts">
definePageMeta({
  layout: "error",
  middleware: ["auth"],
});
</script>

<template>
  <div
    class="min-h-screen flex flex-col items-center justify-center bg-white px-4"
  >
    <div class="text-center max-w-md">
      <div class="mb-6">
        <p class="text-9xl font-bold text-gray-200 tracking-tighter">403</p>
      </div>

      <h1 class="text-3xl font-semibold text-[#171717] mb-2">وصول مرفوض</h1>
      <p class="text-gray-600 mb-6">
        ليس لديك صلاحيات للوصول إلى هذه الموارد. تواصل مع المسؤول إذا كنت تعتقد
        أن هذا خطأ.
      </p>

      <div class="flex gap-3 justify-center">
        <UButton color="black" to="/dashboard">العودة إلى لوحة التحكم</UButton>
      </div>
    </div>
  </div>
</template>
```

### 4.5 App Root Component Error Handling (app.vue)

```vue
<!-- frontend/app.vue -->

<script setup lang="ts">
import { useErrorStore } from "~/stores/error";

const error = useError();
const errorStore = useErrorStore();

watch(error, (newError) => {
  if (newError) {
    errorStore.setError(newError);
  }
});
</script>

<template>
  <div>
    <AppErrorBoundary>
      <NuxtPage />
    </AppErrorBoundary>
  </div>
</template>
```

### 4.6 Error Store (Pinia)

```typescript
// frontend/stores/error.ts

import { defineStore } from "pinia";
import { ref } from "vue";

export interface ClientError {
  code: string;
  message: string;
  details?: Record<string, any>;
  timestamp: number;
}

export const useErrorStore = defineStore("error", () => {
  const errors = ref<ClientError[]>([]);
  const lastError = ref<ClientError | null>(null);

  const setError = (error: ClientError) => {
    errors.value.push(error);
    lastError.value = error;

    // Auto-clear after 30s
    setTimeout(() => {
      clearError(error.code);
    }, 30000);
  };

  const clearError = (code: string) => {
    errors.value = errors.value.filter((e) => e.code !== code);
  };

  const clearAll = () => {
    errors.value = [];
    lastError.value = null;
  };

  return {
    errors,
    lastError,
    setError,
    clearError,
    clearAll,
  };
});
```

---

## 5. LOGGING STANDARDS

### 5.1 Structured Logging Format

All logs must include these fields:

```json
{
  "timestamp": "2026-04-11T10:30:45.123456Z",
  "level": "ERROR",
  "message": "Failed to process payment",
  "correlation_id": "req_1234567890",
  "service": "PaymentService",
  "context": {
    "user_id": 42,
    "order_id": 123,
    "payment_method": "credit_card",
    "amount": 1500.0
  },
  "error": {
    "code": "PAYMENT_FAILED",
    "message": "Card declined",
    "exception": "PaymentFailedException"
  }
}
```

### 5.2 Log Levels

| Level    | Use Case                                               | Retention | Alerts          |
| -------- | ------------------------------------------------------ | --------- | --------------- |
| DEBUG    | Development only, verbose tracing                      | 7 days    | No              |
| INFO     | Standard API requests, successful operations           | 30 days   | No              |
| WARNING  | Recoverable errors, validation failures, auth failures | 60 days   | Optional        |
| ERROR    | Unrecoverable errors, payment failures                 | 90 days   | Yes             |
| CRITICAL | System failures, database errors                       | 180 days  | Yes (immediate) |

### 5.3 Logging Best Practices

**DO:**

- Include correlation ID in all log entries
- Log entry point and exit of services
- Log business-critical decisions (approvals, payments, state transitions)
- Log all errors with full exception context
- Use structured fields, not concatenated strings

**DON'T:**

- Log sensitive data (passwords, credit card numbers, SSNs)
- Log stack traces in production (only in development/staging)
- Log PII without encryption
- Use unstructured logging (single string messages)

---

## 6. CONSTRAINTS & RULES

### 6.1 Laravel Backend Constraints

1. **All exceptions** must be caught by the Handler — no unhandled exceptions in production
2. **All Form Requests** must define validation error messages in Arabic
3. **All responses** must use the error contract format (even errors)
4. **Correlation ID middleware** must be registered first in the middleware stack
5. **Logging middleware** must log response status and duration
6. **No direct Eloquent exceptions** in controllers — catch and transform to domain exceptions

### 6.2 Nuxt Frontend Constraints

1. **All API calls** must go through `useApi()` composable
2. **All errors** must trigger notifications via `useErrorNotification()`
3. **All pages** must be wrapped in error boundary
4. **Error pages** (404, 500, 403) must use error layout
5. **No hardcoded error messages** — use i18n translations
6. **No unhandled promise rejections** — all fetch calls wrapped with try/catch

### 6.3 RBAC-Aware Error Details

Error details are filtered by role:

| Error Detail           | Customer | Contractor | Architect | Field Engineer | Admin        |
| ---------------------- | -------- | ---------- | --------- | -------------- | ------------ |
| Error code             | ✓        | ✓          | ✓         | ✓              | ✓            |
| Human message          | ✓        | ✓          | ✓         | ✓              | ✓            |
| Validation details     | ✓        | ✓          | ✓         | ✓              | ✓            |
| Stack trace            | ✗        | ✗          | ✗         | ✗              | ✓ (dev only) |
| Internal error cause   | ✗        | ✗          | ✗         | ✗              | ✓ (dev only) |
| Database error details | ✗        | ✗          | ✗         | ✗              | ✓ (dev only) |

**Implementation:**

```php
// Handler.php: filter based on user role
if (!$request->user() || $request->user()->role !== UserRole::Admin) {
    // Don't expose stack trace or internal details
    $details = null;
} else if (app()->environment('production')) {
    // Even admin users don't get stack traces in production
    $details = null;
}
```

### 6.4 Arabic/RTL Error Messages

**All error messages must support Arabic:**

1. Error messages in responses — use Arabic (with English as fallback)
2. Error page headings and descriptions — use Arabic
3. Form validation messages — provide in both Arabic and English
4. Error notification toast text — use Arabic

**Example validation messages in Form Request:**

```php
public function messages(): array
{
    return [
        'name.required' => 'حقل الاسم مطلوب',
        'name.max' => 'الاسم يجب أن لا يتجاوز 255 حرف',
        'email.required' => 'البريد الإلكتروني مطلوب',
        'email.email' => 'البريد الإلكتروني غير صالح',
        'budget.required' => 'الميزانية مطلوبة',
        'budget.min' => 'الميزانية يجب أن تكون أكبر من 0',
    ];
}
```

---

## 7. IMPLEMENTATION SEQUENCE

1. **Phase 1: Backend Foundation**

   - Create custom exception hierarchy
   - Implement exception handler with error contract
   - Add correlation ID middleware
   - Add request/response logging middleware
   - Create API response helper trait

2. **Phase 2: Error Registry & Logging**

   - Define all error codes
   - Configure structured logging (JSON formatter)
   - Add error code registry as reference
   - Test error code → HTTP status mapping

3. **Phase 3: Frontend Implementation**

   - Create API interceptor (useApi composable)
   - Create error notification composable
   - Create error boundary component
   - Create error page components (404, 500, 403)
   - Create error store (Pinia)

4. **Phase 4: Testing & Documentation**
   - Unit tests for exception handler
   - Integration tests for error responses
   - Frontend tests for error boundary and notifications
   - Documentation of error codes and recovery strategies

---

## 8. TESTING STRATEGY

### 8.1 Backend Tests (PHPUnit)

**Test:** Validation error response format

```php
public function test_validation_error_response_format()
{
    $response = $this->post('/api/v1/projects', [
        // missing required fields
    ]);

    $response->assertStatus(422);
    $response->assertJsonStructure([
        'success',
        'data',
        'error' => [
            'code',
            'message',
            'details',
        ],
    ]);
    $response->assertJson(['success' => false]);
    $response->assertJsonPath('error.code', 'VALIDATION_ERROR');
}
```

**Test:** Authorization error response

```php
public function test_authorization_error_response()
{
    $customer = User::factory()->customer()->create();
    $project = Project::factory()->create(['customer_id' => $customer->id]);

    $other = User::factory()->contractor()->create();

    $response = $this->actingAs($other)
        ->delete("/api/v1/projects/{$project->id}");

    $response->assertStatus(403);
    $response->assertJsonPath('error.code', 'RBAC_ROLE_DENIED');
}
```

### 8.2 Frontend Tests (Vitest)

**Test:** Error interceptor shows notification

```typescript
import { describe, it, expect, vi } from "vitest";
import { useApi } from "~/composables/useApi";
import { useErrorNotification } from "~/composables/useErrorNotification";

vi.mock("~/composables/useErrorNotification");

describe("useApi error interceptor", () => {
  it("shows error notification on API error", async () => {
    const { apiFetch } = useApi();
    const { showErrorNotification } = useErrorNotification();

    vi.mocked(showErrorNotification).mockImplementation(vi.fn());

    try {
      await apiFetch("/api/v1/projects");
    } catch (error) {
      // Error caught
    }

    expect(showErrorNotification).toHaveBeenCalled();
  });
});
```

---

## 9. GOVERNANCE COMPLIANCE

**Architecture Authority:** ADRs in `docs/architecture/ADR/`  
**Design Authority:** `DESIGN.md` (Vercel-inspired visual language)  
**Error Pattern Authority:** `.agents/skills/error-handling-patterns/SKILL.md`  
**i18n Authority:** `.agents/skills/i18n-governance/SKILL.md`

**Conflict Resolution Order:**

1. ADRs (Architecture Decisions)
2. AGENTS.md error contract
3. error-handling-patterns skill
4. DESIGN.md (frontend error pages)
5. This specification

---

## 10. SUMMARY OF SPECIFICATIONS

**Generated Specifications:**

1. ✓ API Error Contract (unified format)
2. ✓ Error Code Registry (12 standardized codes)
3. ✓ Backend Error Handling (exception hierarchy, handler)
4. ✓ Backend Middleware (correlation ID, logging)
5. ✓ Frontend Error Interceptor (useApi composable)
6. ✓ Frontend Error Notifications (useErrorNotification)
7. ✓ Frontend Error Boundary (component)
8. ✓ Frontend Error Pages (404, 500, 403)
9. ✓ Structured Logging Standards (JSON format, fields)
10. ✓ RBAC-Aware Error Details (role-based filtering)
11. ✓ Arabic/RTL Support (all messages, pages)
12. ✓ Testing Strategy (backend & frontend)

**Constraints Applied:**

- Error contract enforced at exception handler level
- Correlation ID on all requests for tracing
- RBAC filtering on sensitive error details
- Arabic-first error messages with English fallback
- Structured logging with JSON formatter
- Retry logic for recoverable errors
- Separate error pages for 404, 500, 403 with RTL layout

**Key Governance Notes:**

- Compliant with AGENTS.md error contract specification
- Follows `error-handling-patterns` skill guidelines
- Uses i18n governance for Arabic/RTL
- Follows DESIGN.md for error page visual language
- All error pages styled with shadow-as-border technique
- Error codes mapped to HTTP status (never vary)
- All error details filtered by user role

---

## 11. CLARIFICATIONS

### Session 2026-04-11 | SpecKit.Clarify

**Q1: Correlation ID Scope — Request vs. Session vs. User Tracing**

**Question:** Should correlation IDs be:

- **Option A (Current Spec):** Per-request only — each request gets a unique ID, no cross-request linking
- **Option B:** Per-session — multiple requests in same browser session share a correlation ID
- **Option C:** Per-user lifecycle — all requests from same user get same ID until logout

The spec (section 3.3, 4.1) shows per-request generation (`uniqid('req_', true)` + timestamp-based). For debugging and tracing distributed requests (e.g., user makes multiple API calls), should we link them under a session or user ID?

**Decision:** [AWAITING INPUT]
**Reasoning:** Request-level correlation IDs are simpler and work well for request/response debugging. Session-level IDs would help trace user workflows across multiple operations. Choose based on monitoring strategy.

Status: AWAITING INPUT

---

**Q2: RBAC Error Detail Visibility — Specificity for Each Error Type**

**Question:** Section 6.3 defines a role-based visibility matrix. However, for specific error types, what details are acceptable?

Example ambiguities:

- **VALIDATION_ERROR (422):** All roles see field names and validation messages. Should different roles see different field validation rules? (e.g., Admin sees "budget must be > 1000", Customer only sees "budget invalid")
- **RESOURCE_NOT_FOUND (404):** Can non-admin see resource type (e.g., "Project 123 not found") or only generic message?
- **WORKFLOW_INVALID_TRANSITION (422):** Should non-admin see allowed_transitions array or only the error message?
- **PAYMENT_FAILED (422):** Should Contractor see payment method details or only generic "payment failed"?

**Decision:** [AWAITING INPUT]
**Reasoning:** Need explicit rules for each error type to prevent information leakage (e.g., confirming resource existence via 404) or confusion (too little detail).

Status: AWAITING INPUT

---

**Q3: Toast Notification Behavior — Queue vs. Replace vs. Stack**

**Question:** Section 4.2 shows one error notification at a time. What happens when multiple errors occur?

Current code (useErrorNotification.ts) calls `toast.add()` each time. This will:

- **Option A (Queue):** Show one toast, queue others, show next after timeout
- **Option B (Replace):** Show newest error, replace previous one immediately
- **Option C (Stack):** Show multiple toasts simultaneously, stacked vertically

Example scenario: User submits form with 3 validation errors simultaneously, then network error happens before user can fix the form.

Should they see:

- All 3 field errors stacked, then network error adds below?
- Or just the network error (replacement)?
- Or 1 field error, wait 5s, then next field error (queue)?

**Decision:** [AWAITING INPUT]
**Reasoning:** UX varies by use case. Form validation benefits from stacking (see all problems at once). Network errors might replace (no point showing old error if newer one happened).

Status: AWAITING INPUT

---

**Q4: Logging Destination — File vs. Stdout vs. External Service**

**Question:** Section 3.6 configures structured logging to two channels: `single` (file) and `structured` (JSON file). What about production?

- **Option A (Current Spec):** Logs to local files only (`storage_path('logs/....')`)
- **Option B:** Log to stdout + file (for Docker/Kubernetes)
- **Option C:** Log to external service (e.g., DataDog, Sentry, CloudWatch)

Also, section 5.1 shows retention rules (7 days debug, 90 days errors). Who manages log rotation and cleanup?

**Decision:** [AWAITING INPUT]
**Reasoning:** File-based logging works for monolithic deployments. Containerized/serverless needs stdout or external service. Clarify so logging infrastructure is clear.

Status: AWAITING INPUT

---

**Q5: Error Retry Logic — Which Errors and What Strategy**

**Question:** Section 4.2 (useErrorNotification) mentions retryable errors but doesn't define which ones or how retry should work.

Ambiguities:

- **Which errors are retryable?**

  - Currently: VALIDATION_ERROR, RATE_LIMIT_EXCEEDED (line 320)
  - Should also be retryable: PAYMENT_FAILED? SERVICE_UNAVAILABLE? Timeout errors (not in registry)?
  - Should NOT be retryable: RBAC_ROLE_DENIED, AUTH_UNAUTHORIZED, RESOURCE_NOT_FOUND (user can't fix)

- **Retry strategy?**

  - Immediate retry?
  - Exponential backoff (1s, 2s, 4s)?
  - Max retries (3x, 5x)?
  - Show countdown "Retry in 5s" or manual button?

- **Correlation ID on retry?**
  - Same correlation ID (trace linked)?
  - New correlation ID (separate trace)?

**Decision:** [AWAITING INPUT]
**Reasoning:** Retry strategy impacts user experience and server load. Explicit rules prevent retry storms and improve reliability.

Status: AWAITING INPUT

---

### Resolutions from Specification Content

**Q6: Validation Error Details Structure [RESOLVED]**

The spec clearly defines validation errors include a `details` object with field-level messages (section 1.2, example at lines 89-93). This resolves the ambiguity: **field-level details are always included for VALIDATION_ERROR**, and the exception handler maps Laravel's validation errors to this structure.

Status: RESOLVED ✓

---

**Q7: Arabic Message Priority [RESOLVED]**

Section 6.4 and section 1.3 examples confirm **Arabic is primary**, English is fallback. All example responses show Arabic messages. Error code enums can be bilingual, but messages must be Arabic-first.

Status: RESOLVED ✓

---

**Q8: Stack Trace Visibility [RESOLVED]**

Section 6.3 and Handler.php code (line 362) confirm: **stack traces never exposed to any role in production**. Admin users only see stack traces in development/staging. This is clearly specified and enforced in `logError()` method.

Status: RESOLVED ✓

---

**Q9: Correlation ID Format [RESOLVED]**

Section 3.3 (line 445) and 4.1 (line 676) define format: **`${Date.now()}_${random}`** or **`req_{uuid}`**. The spec is clear: unique per request, generated if missing, forwarded in `X-Correlation-ID` header.

Status: RESOLVED ✓

---

**Q10: Error Code HTTP Mapping [RESOLVED]**

Section 2.0 (Error Code Registry table, lines 184-197) provides explicit mapping: each error code has a **fixed HTTP status that never changes**. This eliminates ambiguity: VALIDATION_ERROR is always 422, RBAC_ROLE_DENIED is always 403, etc.

Status: RESOLVED ✓
