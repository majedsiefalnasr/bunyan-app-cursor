# Laravel Exception Handler Contract

**File:** `backend/app/Exceptions/Handler.php`  
**Purpose:** Convert all exceptions to standardized error response format  
**Authority:** AGENTS.md error contract

---

## Interface Contract

```php
interface ExceptionHandlerContract
{
    /**
     * Render exception as JSON response
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Illuminate\Http\JsonResponse
     */
    public function render($request, Throwable $e): JsonResponse;

    /**
     * Report exception (logging)
     *
     * @param  \Throwable  $e
     * @return void
     */
    public function report(Throwable $e): void;
}
```

---

## Rendering Rules (In Priority Order)

### Rule 1: ValidationException → 422

```php
if ($e instanceof ValidationException) {
    return response()->json([
        'success' => false,
        'data' => null,
        'error' => [
            'code' => 'VALIDATION_ERROR',
            'message' => __('errors.validation'),
            'details' => $e->errors(),
        ],
    ], 422);
}
```

**When:** Form request validation fails  
**HTTP Status:** 422  
**Details:** Field-level validation errors

---

### Rule 2: AuthorizationException → 403

```php
if ($e instanceof AuthorizationException) {
    return response()->json([
        'success' => false,
        'data' => null,
        'error' => [
            'code' => 'RBAC_ROLE_DENIED',
            'message' => __('errors.rbac_denied'),
            'details' => null,
        ],
    ], 403);
}
```

**When:** User lacks required role  
**HTTP Status:** 403  
**Details:** None (policy failed)

---

### Rule 3: ModelNotFoundException → 404

```php
if ($e instanceof ModelNotFoundException) {
    return response()->json([
        'success' => false,
        'data' => null,
        'error' => [
            'code' => 'RESOURCE_NOT_FOUND',
            'message' => __('errors.not_found'),
            'details' => [
                'resource' => $e->getModel(),
                'id' => $e->getIds()[0] ?? null,
            ],
        ],
    ], 404);
}
```

**When:** Eloquent model not found  
**HTTP Status:** 404  
**Details:** Resource type and ID

---

### Rule 4: ThrottleRequestsException → 429

```php
if ($e instanceof ThrottleRequestsException) {
    $retryAfter = (int)$e->getHeaders()['Retry-After'] ?? 60;

    return response()->json([
        'success' => false,
        'data' => null,
        'error' => [
            'code' => 'RATE_LIMIT_EXCEEDED',
            'message' => __('errors.rate_limit'),
            'details' => [
                'retry_after' => $retryAfter,
            ],
        ],
    ], 429)->header('Retry-After', $retryAfter);
}
```

**When:** Rate limit exceeded  
**HTTP Status:** 429  
**Details:** Retry-After seconds

---

### Rule 5: DomainException (Custom) → Varies

```php
if ($e instanceof DomainException) {
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
```

**When:** Custom business logic exception  
**HTTP Status:** From exception  
**Details:** From exception's getDetails() method

---

### Rule 6: Not Authenticated (No User) → 401

```php
if ($request->expectsJson() && !$request->user()) {
    return response()->json([
        'success' => false,
        'data' => null,
        'error' => [
            'code' => 'AUTH_UNAUTHORIZED',
            'message' => __('errors.unauthorized'),
            'details' => null,
        ],
    ], 401);
}
```

**When:** No auth token provided  
**HTTP Status:** 401  
**Details:** None

---

### Rule 7: Fallback → 500

```php
// Log the error
$this->logError($e, $request);

return response()->json([
    'success' => false,
    'data' => null,
    'error' => [
        'code' => 'SERVER_ERROR',
        'message' => __('errors.server_error'),
        'details' => null,
    ],
], 500);
```

**When:** Any other exception  
**HTTP Status:** 500  
**Details:** None (never expose in production)

---

## Logging Contract

```php
protected function logError(Throwable $e, Request $request): void
{
    // Extract correlation ID
    $correlationId = $request->attributes->get('correlation_id', 'unknown');

    // Log with full context
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
```

---

## RBAC Filtering Contract

```php
protected function filterDetailsByRole($exception, $request): ?array
{
    // No user? No details
    if (!$request->user()) {
        return null;
    }

    // Non-admin? No sensitive details
    if ($request->user()->role !== UserRole::Admin) {
        return null;
    }

    // Admin in production? Still no stack trace
    if (app()->environment('production')) {
        return null;
    }

    // Admin in development? Full details
    if (method_exists($exception, 'getDetails')) {
        return $exception->getDetails();
    }

    return null;
}
```

---

## Response Format Contract

```json
{
  "success": false,
  "data": null,
  "error": {
    "code": "ERROR_CODE",
    "message": "User-readable message",
    "details": null // or object with field-level details
  }
}
```

**Rules:**

- `success` always boolean
- `data` always null on error
- `error` always object with code, message, details
- `code` always PascalCase_WITH_UNDERSCORES
- `message` always Arabic (primary)
- `details` optional (validation, context)

---

## Implementation Checklist

- [ ] Handler extends ExceptionHandler
- [ ] register() configurable renderers (PHP 8.0+ style) OR large if/else
- [ ] render() returns JsonResponse for API requests
- [ ] All 7 exception types routed correctly
- [ ] ValidationException extracts field-level errors
- [ ] ModelNotFoundException extracts resource type
- [ ] ThrottleRequestsException includes Retry-After
- [ ] DomainException uses getErrorCode/getHttpStatus/getDetails
- [ ] Fallback 500 never exposes stack trace
- [ ] RBAC filtering prevents detail leakage
- [ ] Correlation ID included in logs
- [ ] All error messages use \_\_() translation function
- [ ] Tests verify 100% coverage (7 exception types)

---

**Authority:** AGENTS.md, error-handling-patterns skill
