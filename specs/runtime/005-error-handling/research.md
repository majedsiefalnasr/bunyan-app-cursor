# STAGE_05: Error Handling & Logging — Technical Research

**Phase:** 01_PLATFORM_FOUNDATION  
**Generated:** 2026-04-11  
**Status:** PLANNING  
**Focus:** Laravel patterns, Nuxt.js techniques, logging libraries, structured tracing

---

## 1. LARAVEL EXCEPTION HANDLING PATTERNS

### 1.1 Exception Handler Architecture

Laravel's exception handling is centered on the `Handler` class in `app/Exceptions/Handler.php`. The handler is invoked by `Illuminate\Foundation\Bootstrap\HandleExceptions` middleware when any uncaught exception occurs.

**Key Methods:**

```php
class Handler extends ExceptionHandler
{
    /**
     * Report the exception (log it)
     */
    public function report(Throwable $e): void {}
    
    /**
     * Render the exception (return response)
     */
    public function render($request, Throwable $e): Response|JsonResponse {}
    
    /**
     * Register custom exception handlers
     */
    public function register(): void {}
}
```

### 1.2 Custom Exception Pattern

Best practice: Create domain-specific exception classes extending `Exception`:

```php
namespace App\Exceptions;

class PaymentFailedException extends Exception
{
    public function __construct(
        public readonly string $method,
        public readonly string $reason,
        public readonly bool $retryable = false,
    ) {
        parent::__construct("Payment failed: {$reason}");
    }
}
```

**Why:** Allows catching by specific type, including context properties, and mapping to HTTP responses.

### 1.3 Exception Detection in Handler

Use `instanceof` checks to route exceptions:

```php
public function render($request, Throwable $e)
{
    if ($e instanceof PaymentFailedException) {
        return $this->paymentErrorResponse($e);
    }
    
    if ($e instanceof ValidationException) {
        return $this->validationResponse($e);
    }
    
    // Default
    return $this->fallbackResponse($e);
}
```

### 1.4 Middleware Exception Handling

Exceptions thrown in middleware are caught by the exception handler if not caught within the middleware:

```php
class MyMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        try {
            return $next($request);
        } catch (UnauthorizedException $e) {
            // Custom handling
            throw new AuthorizationException($e->getMessage());
        }
    }
}
```

### 1.5 Register Custom Exception Handlers (PHP 8.0+)

Using `register()` method to handle exceptions by type:

```php
public function register(): void
{
    $this->renderable(function (PaymentFailedException $e, $request) {
        return response()->json([...], 422);
    });
}
```

**Pros:** Cleaner than large switch statements  
**Cons:** Less control over handler flow

### 1.6 Throw Custom Exceptions from Services

Services throw specific exceptions for specific failures:

```php
class ProjectService
{
    public function createProject(array $data): Project
    {
        if (!$this->canUserCreateProject($data['customer_id'])) {
            throw new InsufficientPermissionException('Cannot create project');
        }
        
        try {
            return Project::create($data);
        } catch (QueryException $e) {
            throw new DomainException('Failed to create project');
        }
    }
}
```

**Pattern:** Repository/Service layer throws domain exceptions, controller doesn't catch (lets Handler deal with it).

---

## 2. NUXT.JS 3 ERROR BOUNDARY & LIFECYCLE

### 2.1 Error Capture with onErrorCaptured()

Nuxt 3 provides `onErrorCaptured()` hook to catch errors in component tree:

```vue
<script setup>
import { onErrorCaptured } from 'vue'

const error = ref(null)

onErrorCaptured((err) => {
  error.value = err
  console.error('Caught error:', err)
  
  // Return false to prevent error from propagating
  return false
})
</script>

<template>
  <div v-if="error">
    <p>Error: {{ error.message }}</p>
  </div>
  <slot v-else />
</template>
```

**Key Points:**
- `onErrorCaptured` only catches errors in child components
- Return `false` to prevent further propagation
- Can be used at any component level, but root/app level catches all

### 2.2 useError() Composable (Nuxt 3)

Nuxt 3 provides `useError()` to access and set errors:

```typescript
const error = useError()

if (error.value) {
  // An error was set
  console.log(error.value.message)
}

// Set an error
error.value = new Error('Something went wrong')
```

### 2.3 Global Error Handler

Set global error handler in `nuxt.config.ts`:

```typescript
export default defineNuxtConfig({
  modules: ['@nuxt/ui'],
  
  errorHandler: (error, instance) => {
    // Global error handler
    console.error('Global error:', error)
    
    // Can emit to store or notification system
  },
})
```

### 2.4 Asyncdata/Fetch Error Handling

Errors in `useAsyncData()` or `useFetch()` are caught:

```typescript
const { data, error, pending } = await useFetch('/api/projects')

if (error.value) {
  console.error('Fetch failed:', error.value)
  // Navigate or show error
}
```

### 2.5 Async Component Error Handling

For lazy-loaded components:

```vue
<script setup>
import { defineAsyncComponent } from 'vue'

const MyAsyncComponent = defineAsyncComponent({
  loader: () => import('./MyComponent.vue'),
  
  errorComponent: () => import('./ErrorComponent.vue'),
  
  delay: 200,
  timeout: 5000,
})
</script>

<template>
  <Suspense>
    <template #default>
      <MyAsyncComponent />
    </template>
    
    <template #fallback>
      <div>Loading...</div>
    </template>
  </Suspense>
</template>
```

### 2.6 Middleware Error Handling

Route middleware can throw errors:

```typescript
// middleware/auth.ts
export default defineRouteMiddleware((to, from) => {
  const auth = useAuthStore()
  
  if (!auth.isAuthenticated) {
    throw new Error('Unauthorized')
    // or navigateTo('/login')
  }
})
```

Errors from middleware are caught by the exception handler.

---

## 3. STRUCTURED LOGGING LIBRARIES

### 3.1 Laravel Monolog Integration

Laravel uses Monolog for logging. Configuration in `config/logging.php`:

```php
'channels' => [
    'single' => [
        'driver' => 'single',
        'path' => storage_path('logs/laravel.log'),
        'level' => 'debug',
    ],

    'structured' => [
        'driver' => 'monolog',
        'handler' => Monolog\Handler\StreamHandler::class,
        'formatter' => Monolog\Formatter\JsonFormatter::class,
        'with' => [
            'stream' => storage_path('logs/structured.log'),
        ],
        'level' => 'debug',
    ],
],
```

### 3.2 JSON Formatter Output

Monolog's `JsonFormatter` outputs each log entry as a JSON object:

```json
{
  "message": "API Request",
  "context": {
    "correlation_id": "req_1712844645_abc123",
    "method": "POST",
    "path": "/api/v1/projects",
    "status": 201,
    "duration_ms": 145.23
  },
  "level": 200,
  "level_name": "INFO",
  "channel": "stack",
  "datetime": {
    "date": "2026-04-11T10:30:45.123456Z",
    "timezone_type": 3,
    "timezone": "UTC"
  }
}
```

### 3.3 Custom Monolog Handler

Create custom handler to add correlation ID to all logs:

```php
namespace App\Logging;

use Monolog\Processor\ProcessorInterface;

class CorrelationIdProcessor implements ProcessorInterface
{
    public function __invoke(array $record): array
    {
        $record['extra']['correlation_id'] = 
            app('request')->attributes->get('correlation_id', 'unknown');
        
        return $record;
    }
}
```

Register in `bootstrap/app.php`:

```php
use App\Logging\CorrelationIdProcessor;

// Add processor to all channels
Log::getHandlers()->each(fn ($handler) => 
    $handler->pushProcessor(new CorrelationIdProcessor())
);
```

### 3.4 Using Log Channels

In code:

```php
use Illuminate\Support\Facades\Log;

Log::channel('structured')->info('User created', [
    'user_id' => 42,
    'email' => 'user@example.com',
    'role' => 'customer',
]);

Log::channel('structured')->error('Payment failed', [
    'error_code' => 'PAYMENT_FAILED',
    'order_id' => 123,
    'amount' => 1500.00,
]);
```

### 3.5 Log Levels

```php
Log::debug('Detailed information');           // 100
Log::info('Informational messages');          // 200
Log::notice('Normal but significant');        // 250
Log::warning('Warnings');                     // 300
Log::error('Errors');                         // 400
Log::critical('Critical conditions');         // 500
Log::alert('Action must be taken');           // 550
Log::emergency('System is unusable');         // 600
```

---

## 4. CORRELATION ID PATTERNS

### 4.1 Generation & Propagation

**Backend Generation:**

```php
// Middleware: InjectCorrelationId
$correlationId = $request->header('X-Correlation-ID') 
    ?: uniqid('req_', true);

$request->attributes->set('correlation_id', $correlationId);
```

**Frontend Generation:**

```typescript
function generateCorrelationId(): string {
  return `${Date.now()}_${Math.random().toString(36).substr(2, 9)}`
}

// Send in header
headers.set('X-Correlation-ID', generateCorrelationId())
```

### 4.2 Correlation ID in Logs

Add to all logs via Monolog processor:

```php
class CorrelationIdProcessor
{
    public function __invoke(array $record): array
    {
        $record['extra']['correlation_id'] = 
            app('request')?->attributes->get('correlation_id');
        
        return $record;
    }
}
```

Then query logs by correlation ID:

```bash
grep "correlation_id.*req_1712844645_abc123" storage/logs/structured.log
```

### 4.3 Distributed Tracing (OpenTelemetry)

For microservices, use correlation IDs with OpenTelemetry:

```php
use OpenTelemetry\API\Trace\TracerProvider;

$tracer = TracerProvider::getInstance()->getTracer('app');

$span = $tracer->spanBuilder('api_request')
    ->setAttributes([
        'correlation_id' => $correlationId,
        'user_id' => $userId,
    ])
    ->startSpan();

// Do work...

$span->end();
```

**For Bunyan MVP:** Simple correlation IDs in logs are sufficient.

### 4.4 Session-Level vs Request-Level

**Option A (Request-level):** Each request gets unique ID
- Simpler
- Good for single-request debugging
- Loses context across multiple requests from same user

**Option B (Session-level):** Multiple requests share same ID
- Better for tracing user workflows
- Requires session binding
- More complex

**Recommendation for Bunyan:** Start with **request-level** (simpler), add session-level if needed later.

---

## 5. ERROR NOTIFICATION LIBRARIES

### 5.1 Nuxt UI Toast System

Nuxt UI (`@nuxt/ui`) provides `useToast()` composable:

```typescript
import { useToast } from '#ui/composables/useToast'

const toast = useToast()

toast.add({
  title: 'Error',
  description: 'Failed to create project',
  color: 'red',
  timeout: 5000,
  actions: [
    {
      label: 'Retry',
      click: () => retryFunction(),
    },
  ],
})
```

**Props:**
- `title`: Toast title
- `description`: Toast message
- `color`: Color variant (red, yellow, green, etc.)
- `timeout`: Auto-dismiss delay (ms)
- `actions`: Array of action buttons
- `position`: Toast position (top-right, top-center, etc.)

### 5.2 Toast Positioning (RTL-Aware)

Nuxt UI automatically handles RTL positioning. Set in `nuxt.config.ts`:

```typescript
export default defineNuxtConfig({
  ui: {
    // Toast default position
    toast: {
      position: 'top-right',
    },
  },
})
```

For RTL, Nuxt UI automatically positions on left side.

### 5.3 Custom Toast Component

If Nuxt UI's toast isn't sufficient, create custom:

```vue
<script setup lang="ts">
interface Toast {
  id: string
  title: string
  description: string
  color: 'red' | 'yellow' | 'green'
  timeout: number
}

const toasts = ref<Toast[]>([])

const add = (payload: Omit<Toast, 'id'>) => {
  const id = crypto.randomUUID()
  const toast = { id, ...payload }
  
  toasts.value.push(toast)
  
  setTimeout(() => {
    remove(id)
  }, payload.timeout)
}

const remove = (id: string) => {
  toasts.value = toasts.value.filter(t => t.id !== id)
}
</script>

<template>
  <div class="fixed top-right space-y-2">
    <div v-for="toast in toasts" :key="toast.id" 
      :class="toastColorClass(toast.color)"
      class="p-4 rounded">
      <p class="font-semibold">{{ toast.title }}</p>
      <p class="text-sm">{{ toast.description }}</p>
    </div>
  </div>
</template>
```

### 5.4 Error Toast Queue Behavior

**Options:**

1. **Stack:** Multiple toasts visible simultaneously
2. **Replace:** New error replaces previous toast
3. **Queue:** One at a time, next shows after timeout

**Implementation for Queue:**

```typescript
const toastQueue = ref<ErrorPayload[]>([])
const currentToast = ref<ErrorPayload | null>(null)

const enqueue = (payload: ErrorPayload) => {
  toastQueue.value.push(payload)
  if (!currentToast.value) {
    showNext()
  }
}

const showNext = () => {
  if (toastQueue.value.length === 0) {
    currentToast.value = null
    return
  }
  
  currentToast.value = toastQueue.value.shift()
  
  setTimeout(() => {
    showNext()
  }, currentToast.value.timeout)
}
```

**For Bunyan:** Stack behavior (show multiple validation errors at once) is better UX.

---

## 6. RBAC FILTERING PATTERNS

### 6.1 Role-Based Detail Visibility

Pattern: Check user role before including sensitive details:

```php
protected function filterErrorDetails($error, $request): ?array
{
    // Non-authenticated users get nothing
    if (!$request->user()) {
        return null;
    }
    
    // Admin in dev gets everything
    if ($request->user()->role === 'admin' && app()->environment('local')) {
        return [
            'exception' => class_basename($error),
            'file' => $error->getFile(),
            'line' => $error->getLine(),
            'trace' => $error->getTraceAsString(),
        ];
    }
    
    // Other users get role-specific details
    if ($request->user()->role === 'contractor') {
        return [
            'payment_method' => 'masked', // Not full details
        ];
    }
    
    return null; // No details for non-admin
}
```

### 6.2 Exception Details Interface

Design exceptions to include role-aware details:

```php
interface RoleAwareException
{
    /**
     * Get details appropriate for this user role
     */
    public function getDetailsByRole(UserRole $role): ?array;
}

class PaymentFailedException extends DomainException implements RoleAwareException
{
    public function getDetailsByRole(UserRole $role): ?array
    {
        return match($role) {
            UserRole::Contractor => [
                'reason' => 'Payment declined',
                // Don't expose card number, failure code, etc.
            ],
            UserRole::Admin => [
                'reason' => $this->reason,
                'method' => $this->method,
                'error_code' => $this->errorCode,
                'retry_possible' => $this->retryable,
            ],
            default => null,
        };
    }
}
```

### 6.3 Policy-Based Authorization

Use Laravel Policies for fine-grained RBAC:

```php
namespace App\Policies;

class ProjectPolicy
{
    public function view(User $user, Project $project): bool
    {
        // Customer can view their own projects
        return $user->id === $project->customer_id;
    }
    
    public function update(User $user, Project $project): bool
    {
        // Only contractor assigned to project can update
        return $project->contractor_id === $user->id;
    }
}
```

Then in controller:

```php
class ProjectController
{
    public function show(Project $project)
    {
        $this->authorize('view', $project);
        return $project;
    }
}
```

Throws `AuthorizationException` (403) if policy denies.

### 6.4 Middleware-Based Role Filtering

For API endpoints, filter by role in middleware:

```php
namespace App\Http\Middleware;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!in_array($request->user()?->role->value, $roles)) {
            throw new AuthorizationException('User role not permitted');
        }
        
        return $next($request);
    }
}
```

Register in route:

```php
Route::post('/projects', [ProjectController::class, 'store'])
    ->middleware('role:customer,admin');
```

---

## 7. FRONTEND API CLIENT PATTERNS

### 7.1 Fetch with Error Interceptor

Nuxt 3 provides `$fetch` with interceptors:

```typescript
const apiFetch = $fetch.create({
  baseURL: config.public.apiUrl,
  
  onRequest({ request, options }) {
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
      'X-Correlation-ID': generateCorrelationId(),
    }
  },
  
  onResponse({ response }) {
    // Success handling (optional)
  },
  
  onResponseError({ request, response, error }) {
    // Handle specific error codes
    const errorCode = response._data?.error?.code
    
    if (response.status === 401) {
      auth.logout()
      navigateTo('/login')
    } else if (response.status === 403) {
      navigateTo('/403')
    }
    
    // Emit error event
    useErrorStore().addError({
      code: errorCode,
      message: response._data?.error?.message,
    })
  },
})

export { apiFetch }
```

### 7.2 Response Typing

Type the API responses for TypeScript safety:

```typescript
interface ApiResponse<T> {
  success: boolean
  data: T | null
  error: {
    code: string
    message: string
    details?: Record<string, any>
  } | null
}

// Usage
const response = await apiFetch<Project>('/api/v1/projects/1')
if (response.success) {
  console.log(response.data.id) // TypeScript knows type
}
```

### 7.3 Retry Logic

Implement exponential backoff for retryable errors:

```typescript
async function retryWithBackoff(
  fn: () => Promise<any>,
  maxRetries: number = 3,
  baseDelay: number = 1000
): Promise<any> {
  for (let attempt = 0; attempt < maxRetries; attempt++) {
    try {
      return await fn()
    } catch (error) {
      if (attempt === maxRetries - 1) throw error
      
      const delay = baseDelay * Math.pow(2, attempt)
      await new Promise(resolve => setTimeout(resolve, delay))
    }
  }
}

// Usage
const result = await retryWithBackoff(
  () => apiFetch('/api/v1/projects'),
  3,
  1000
)
```

---

## 8. TRANSLATION & i18n PATTERNS

### 8.1 Backend i18n with Laravel

Create translation files in `resources/lang/{locale}/`:

**backend/resources/lang/ar/errors.php:**

```php
<?php

return [
    'validation_error' => 'بيانات المدخلات غير صحيحة',
    'auth_unauthorized' => 'يجب تسجيل الدخول أولاً',
    'rbac_denied' => 'غير مصرح لك بهذا الإجراء',
    'not_found' => 'المورد المطلوب غير موجود',
    'server_error' => 'حدث خطأ غير متوقع',
];
```

Use in code:

```php
throw new DomainException(__('errors.validation_error'));
```

### 8.2 Frontend i18n with Nuxt

Create translation files in `locales/{locale}.json`:

**frontend/locales/ar.json:**

```json
{
  "errors": {
    "validation": "بيانات المدخلات غير صحيحة",
    "unauthorized": "يجب تسجيل الدخول أولاً",
    "forbidden": "غير مصرح لك بهذا الإجراء",
    "notFound": "المورد غير موجود",
    "serverError": "حدث خطأ غير متوقع"
  }
}
```

Use with `useI18n()`:

```typescript
const { t } = useI18n()

const message = t('errors.validation')
```

### 8.3 Form Validation Messages

In Laravel Form Request:

```php
public function messages(): array
{
    return [
        'name.required' => __('validation.name_required'),
        'email.email' => __('validation.email_invalid'),
        'budget.min' => __('validation.budget_min'),
    ];
}
```

Then in `resources/lang/ar/validation.php`:

```php
<?php

return [
    'name_required' => 'حقل الاسم مطلوب',
    'email_invalid' => 'البريد الإلكتروني غير صالح',
    'budget_min' => 'الميزانية يجب أن تكون أكبر من 0',
];
```

---

## 9. BEST PRACTICES & ANTI-PATTERNS

### 9.1 DO (Best Practices)

- ✓ Catch specific exceptions, not generic `Exception`
- ✓ Log correlation ID with every log entry
- ✓ Filter sensitive details by role
- ✓ Use domain exceptions in services
- ✓ Return consistent JSON format from all endpoints
- ✓ Use middleware for cross-cutting concerns (logging, auth)
- ✓ Provide Arabic error messages (primary language)
- ✓ Include context in error responses (what went wrong)
- ✓ Use structured logging (JSON format)
- ✓ Generate unique correlation IDs for request tracing

### 9.2 DON'T (Anti-Patterns)

- ✗ Catch all exceptions with bare `catch (Exception $e)`
- ✗ Log stack traces in production responses
- ✗ Expose database error details to users
- ✗ Use HTTP 500 for client errors (use 4xx)
- ✗ Throw exceptions from middleware without catching
- ✗ Use generic error messages ("Error occurred")
- ✗ Mix business logic in exception handlers
- ✗ Log passwords, tokens, credit card numbers
- ✗ Hardcode error messages (use i18n)
- ✗ Forget to test error paths in unit tests

---

## 10. PERFORMANCE CONSIDERATIONS

### 10.1 Logging Performance

**Issue:** Structured JSON logging to disk can be slow under high load.

**Solutions:**

1. **Async logging:** Queue logs and write asynchronously
   ```php
   Log::channel('structured')->info('Event', ['data' => $data]);
   // Queued job writes to disk later
   ```

2. **Log sampling:** Only log 10% of non-error requests
   ```php
   if (rand(1, 100) <= 10 || $response->status >= 400) {
       Log::info('API Request', $context);
   }
   ```

3. **Buffered writes:** Buffer logs in memory, write in batches
   ```php
   // Write 100 logs at a time
   $buffer = [];
   $buffer[] = $logEntry;
   if (count($buffer) >= 100) {
       file_put_contents('logs.json', json_encode($buffer), FILE_APPEND);
       $buffer = [];
   }
   ```

### 10.2 Error Response Size

Keep error responses small:

- ✓ Include only necessary details
- ✗ Don't include full stack trace in production
- ✗ Don't include entire request body in error response

### 10.3 Toast Notification Performance

Avoid creating 100 toasts at once:

- Use queue/stack model (show 3-5 at a time)
- Debounce duplicate errors (same code within 1s)
- Limit toast lifetime (5-8s auto-dismiss)

### 10.4 Correlation ID Generation

Use fast ID generation:

```typescript
// Fast (use this)
const id = `${Date.now()}_${Math.random().toString(36).substr(2)}`

// Slow (avoid)
const id = crypto.randomUUID() // Slower on browser
```

---

## 11. TESTING ERROR HANDLING

### 11.1 Unit Test Example (Backend)

```php
public function test_payment_failed_exception_returns_422()
{
    $exception = new PaymentFailedException(
        'card',
        'Card declined',
        false
    );
    
    $this->assertEquals('PAYMENT_FAILED', $exception->getErrorCode());
    $this->assertEquals(422, $exception->getHttpStatus());
    $this->assertArrayHasKey('reason', $exception->getDetails());
}
```

### 11.2 Integration Test Example (Backend)

```php
public function test_validation_error_response_format()
{
    $response = $this->post('/api/v1/projects', [
        // Missing required fields
    ]);
    
    $response->assertStatus(422);
    $response->assertJson([
        'success' => false,
        'error' => [
            'code' => 'VALIDATION_ERROR',
        ],
    ]);
    $response->assertJsonStructure([
        'error' => [
            'details' => ['name', 'budget'],
        ],
    ]);
}
```

### 11.3 Component Test Example (Frontend)

```typescript
describe('AppErrorBoundary', () => {
  it('catches and displays errors', () => {
    const { getByText } = render(AppErrorBoundary, {
      slots: {
        default: () => {
          throw new Error('Test error')
        },
      },
    })
    
    expect(getByText('حدث خطأ')).toBeDefined()
  })
})
```

---

## 12. TOOLS & LIBRARIES SUMMARY

| Tool | Purpose | Version |
|---|---|---|
| **Laravel** | Backend framework, exception handling | 11+ |
| **Monolog** | Structured logging | 3.0+ |
| **Nuxt UI** | Toast notifications | Latest |
| **Pinia** | State management | Latest |
| **VeeValidate** | Form validation | 4.0+ |
| **Zod** | Schema validation (optional) | 3.0+ |

---

**Generated:** 2026-04-11 | **Authority:** error-handling-patterns, laravel docs, nuxt docs
