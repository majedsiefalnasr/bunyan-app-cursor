# STAGE_05: Error Handling & Logging — Implementation Quickstart

**Phase:** 01_PLATFORM_FOUNDATION  
**Generated:** 2026-04-11  
**Status:** PLANNING  
**Target:** Get first error handling working in 1 hour

---

## Executive Summary

This quickstart gets STAGE_05 error handling working end-to-end in minimal time. Follow this sequence:

**Time Estimate:** 60 minutes  
**Outcome:** API returns standardized error responses, frontend shows error toasts

**Phase:** Backend (15 min) → Frontend (30 min) → E2E Test (15 min)

---

## Phase 1: Backend Bootstrap (15 minutes)

### Step 1.1: Create ErrorCode Enum (5 min)

**File:** `backend/app/Enums/ErrorCode.php`

```php
<?php

namespace App\Enums;

enum ErrorCode: string
{
    case VALIDATION_ERROR = 'VALIDATION_ERROR';
    case AUTH_UNAUTHORIZED = 'AUTH_UNAUTHORIZED';
    case RBAC_ROLE_DENIED = 'RBAC_ROLE_DENIED';
    case RESOURCE_NOT_FOUND = 'RESOURCE_NOT_FOUND';
    case SERVER_ERROR = 'SERVER_ERROR';

    public function httpStatus(): int
    {
        return match($this) {
            self::VALIDATION_ERROR => 422,
            self::AUTH_UNAUTHORIZED => 401,
            self::RBAC_ROLE_DENIED => 403,
            self::RESOURCE_NOT_FOUND => 404,
            self::SERVER_ERROR => 500,
        };
    }
}
```

### Step 1.2: Create ApiResponse Trait (5 min)

**File:** `backend/app/Http/Controllers/Api/ApiResponse.php`

```php
<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function sendSuccess($data = null, int $statusCode = 200): JsonResponse
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
    ): JsonResponse {
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

### Step 1.3: Update Exception Handler (5 min)

**File:** `backend/app/Exceptions/Handler.php`

```php
<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Throwable;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        // Validation errors (422)
        if ($e instanceof ValidationException) {
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

        // Not found (404)
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json([
                'success' => false,
                'data' => null,
                'error' => [
                    'code' => 'RESOURCE_NOT_FOUND',
                    'message' => 'المورد المطلوب غير موجود',
                    'details' => null,
                ],
            ], 404);
        }

        // Auth errors (401)
        if (!$request->user() && $request->expectsJson()) {
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

        // Server error (500)
        return response()->json([
            'success' => false,
            'data' => null,
            'error' => [
                'code' => 'SERVER_ERROR',
                'message' => 'حدث خطأ غير متوقع',
                'details' => null,
            ],
        ], 500);
    }
}
```

---

## Phase 2: Frontend Bootstrap (30 minutes)

### Step 2.1: Create API Interceptor Composable (10 min)

**File:** `frontend/composables/useApi.ts`

```typescript
import { useRuntimeConfig } from '#app'
import { useAuthStore } from '~/stores/auth'
import { useErrorNotification } from '~/composables/useErrorNotification'

export function useApi() {
  const config = useRuntimeConfig()
  const auth = useAuthStore()
  const { showErrorNotification } = useErrorNotification()

  const apiFetch = $fetch.create({
    baseURL: config.public.apiBaseUrl,

    onRequest({ options }) {
      // Add auth token
      if (auth.token) {
        options.headers = {
          ...options.headers,
          Authorization: `Bearer ${auth.token}`,
        }
      }

      // Add correlation ID
      options.headers = {
        ...options.headers,
        'X-Correlation-ID': `${Date.now()}_${Math.random().toString(36).substr(2, 9)}`,
      }
    },

    onResponseError({ response }) {
      const data = response._data || {}
      const error = data.error || {}

      // Handle 401
      if (response.status === 401) {
        auth.logout()
        navigateTo('/auth/login')
        return
      }

      // Handle 403
      if (response.status === 403) {
        navigateTo('/dashboard')
        return
      }

      // Show error notification
      showErrorNotification({
        code: error.code || 'SERVER_ERROR',
        message: error.message || 'حدث خطأ غير متوقع',
        details: error.details,
        statusCode: response.status,
      })
    },
  })

  return { apiFetch }
}
```

### Step 2.2: Create Error Notification Composable (10 min)

**File:** `frontend/composables/useErrorNotification.ts`

```typescript
import { useToast } from '#ui/composables/useToast'

export interface ErrorPayload {
  code: string
  message: string
  details?: Record<string, any>
  statusCode?: number
}

export function useErrorNotification() {
  const toast = useToast()

  const showErrorNotification = (payload: ErrorPayload) => {
    const { code, message, statusCode } = payload
    const severity = statusCode && statusCode >= 500 ? 'error' : 'warning'

    toast.add({
      title: code,
      description: message,
      color: severity === 'error' ? 'red' : 'yellow',
      timeout: severity === 'error' ? 8000 : 5000,
    })
  }

  return { showErrorNotification }
}
```

### Step 2.3: Create Error Boundary Component (5 min)

**File:** `frontend/components/common/AppErrorBoundary.vue`

```vue
<script setup lang="ts">
import { ref, onErrorCaptured } from 'vue'

const errorState = ref<{ hasError: boolean; message: string }>({
  hasError: false,
  message: '',
})

const resetError = () => {
  errorState.value = { hasError: false, message: '' }
}

onErrorCaptured((error) => {
  errorState.value = {
    hasError: true,
    message: error instanceof Error ? error.message : String(error),
  }
  console.error('Error caught:', error)
  return false
})
</script>

<template>
  <div v-if="errorState.hasError" class="min-h-screen flex items-center justify-center bg-white">
    <div class="max-w-md w-full bg-white rounded-lg shadow-[0px_0px_0px_1px_rgba(0,0,0,0.08)] p-6 text-center">
      <h1 class="text-2xl font-semibold text-[#171717] mb-4">حدث خطأ</h1>
      <p class="text-gray-600 mb-6">{{ errorState.message }}</p>
      <div class="space-y-2">
        <UButton color="black" block @click="resetError">العودة</UButton>
        <UButton color="white" variant="outline" block @click="location.reload()">تحديث</UButton>
      </div>
    </div>
  </div>

  <slot v-else />
</template>
```

### Step 2.4: Update app.vue (5 min)

**File:** `frontend/app.vue`

```vue
<script setup lang="ts">
import AppErrorBoundary from '~/components/common/AppErrorBoundary.vue'
</script>

<template>
  <AppErrorBoundary>
    <NuxtPage />
  </AppErrorBoundary>
</template>
```

---

## Phase 3: E2E Test (15 minutes)

### Step 3.1: Create Test Endpoint

**File:** `backend/routes/api.php`

```php
Route::post('/v1/test-validation', function (Request $request) {
    $request->validate([
        'name' => 'required|string',
        'email' => 'required|email',
    ]);
});

Route::get('/v1/test-404', function () {
    abort(404);
});
```

### Step 3.2: Test Validation Error

**In Postman or frontend:**

```bash
curl -X POST http://localhost:8000/api/v1/test-validation \
  -H "Content-Type: application/json" \
  -d '{}'
```

**Expected Response (422):**

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "بيانات المدخلات غير صحيحة",
    "details": {
      "name": ["حقل الاسم مطلوب"],
      "email": ["البريد الإلكتروني مطلوب"]
    }
  }
}
```

### Step 3.3: Test Frontend Integration

In Nuxt component:

```vue
<script setup lang="ts">
const { apiFetch } = useApi()

const testError = async () => {
  try {
    await apiFetch('/api/v1/test-validation', {
      method: 'POST',
      body: {},
    })
  } catch (e) {
    // Error handled by interceptor
    console.log('Error caught and notified')
  }
}
</script>

<template>
  <button @click="testError">Test Error</button>
</template>
```

**Expected:** Error toast appears with "VALIDATION_ERROR" and message

---

## Next Steps (After Quickstart)

### Build on this foundation:

1. **Add more error codes** in `ErrorCode` enum (PAYMENT_FAILED, WORKFLOW_*, etc.)
2. **Create custom exceptions** (InvalidStateTransition, PaymentFailed, etc.)
3. **Add correlation ID middleware** for request tracing
4. **Add structured logging** to JSON files
5. **Create error pages** (404.vue, 500.vue, 403.vue)
6. **Add Arabic translations** for all error messages
7. **Implement RBAC filtering** for sensitive error details
8. **Write comprehensive tests** for error flows

---

## Troubleshooting

### Issue: Validation error not formatted correctly

**Solution:** Check that `Handler.php` catches `ValidationException` first in `render()` method

### Issue: Frontend toast not showing

**Solution:**
1. Check `useErrorNotification()` is called in interceptor
2. Verify `@nuxt/ui` is installed: `npm list @nuxt/ui`
3. Check browser console for errors

### Issue: Correlation ID not generated

**Solution:** Verify middleware is registered in `Kernel.php` (middleware needs to be first)

---

## Files Created (Quickstart Only)

```
backend/
├── app/
│   ├── Enums/
│   │   └── ErrorCode.php ← NEW (15 lines)
│   ├── Exceptions/
│   │   └── Handler.php ← MODIFIED (50 lines changed)
│   └── Http/
│       └── Controllers/
│           └── Api/
│               └── ApiResponse.php ← NEW (25 lines)

frontend/
├── composables/
│   ├── useApi.ts ← NEW (40 lines)
│   └── useErrorNotification.ts ← NEW (25 lines)
├── components/
│   └── common/
│       └── AppErrorBoundary.vue ← NEW (35 lines)
└── app.vue ← MODIFIED (3 lines added)
```

**Total Lines of Code:** ~190 lines  
**Total Time:** ~60 minutes

---

## What's Working

✅ API responses follow standardized error format  
✅ Validation errors return 422 with field details  
✅ Frontend interceptor catches and displays errors  
✅ Error toasts show with Arabic messages  
✅ Error boundary catches uncaught component errors  
✅ Auth errors redirect to login  
✅ RBAC errors redirect to dashboard

---

## What's NOT Included (Add Later)

- ❌ Correlation ID middleware (adds request tracing)
- ❌ Request/response logging middleware
- ❌ Persistent error logs to database
- ❌ Custom exception hierarchy (InvalidStateTransition, etc.)
- ❌ Structured JSON logging
- ❌ Error metrics dashboard
- ❌ Detailed RBAC filtering
- ❌ Error pages (404.vue, 500.vue, 403.vue)
- ❌ Form validation retry logic
- ❌ Toast queue behavior

---

**Generated:** 2026-04-11 | **Time to Complete:** 60 minutes
