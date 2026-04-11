# STAGE_05: Error Handling & Logging — Database Schema & Data Models

**Phase:** 01_PLATFORM_FOUNDATION  
**Generated:** 2026-04-11  
**Status:** PLANNING  
**Scope:** Optional persistent error logging, audit trails, error metrics

---

## 1. PERSISTENT ERROR LOGGING (OPTIONAL)

### 1.1 Overview

If the system requires persistent error storage for auditing, debugging, or compliance, use this schema. For MVP, file-based logging is sufficient; this is for later phases.

**When to use:**
- Need to query errors by correlation ID
- Require audit trail of all errors
- Compliance requires error log retention
- Need dashboard showing error trends

### 1.2 Error Logs Table

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
            
            // Correlation & tracing
            $table->string('correlation_id', 255)->index();
            
            // Error information
            $table->string('error_code', 100)->index(); // e.g., VALIDATION_ERROR
            $table->string('message', 1000);
            $table->json('details')->nullable(); // Field-level error details
            $table->json('context')->nullable(); // Request context
            $table->string('severity', 50); // error, warning, critical
            $table->integer('http_status')->nullable(); // 422, 401, 500, etc.
            
            // Debugging
            $table->string('exception_class', 255)->nullable();
            $table->longText('stack_trace')->nullable(); // Only in dev/staging
            
            // User context
            $table->foreignIdFor(\App\Models\User::class)
                ->nullable()
                ->constrained()
                ->cascadeOnDelete();
            $table->string('user_role', 50)->nullable(); // Snapshot of role at time
            
            // Request context
            $table->string('request_method', 10)->nullable(); // GET, POST, etc.
            $table->string('request_path', 500)->nullable(); // /api/v1/projects
            $table->string('request_ip', 45)->nullable(); // IPv4/IPv6
            
            // Response context
            $table->integer('response_time_ms')->nullable(); // Duration
            
            // Timestamps
            $table->timestamps(); // created_at, updated_at
            
            // Indexes for query performance
            $table->index('error_code');
            $table->index('severity');
            $table->index('user_id');
            $table->index('created_at');
            $table->index(['error_code', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_logs');
    }
};
```

### 1.3 Error Logs Model

**File:** `backend/app/Models/ErrorLog.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorLog extends Model
{
    protected $fillable = [
        'correlation_id',
        'error_code',
        'message',
        'details',
        'context',
        'severity',
        'http_status',
        'exception_class',
        'stack_trace',
        'user_id',
        'user_role',
        'request_method',
        'request_path',
        'request_ip',
        'response_time_ms',
    ];

    protected $casts = [
        'details' => 'array',
        'context' => 'array',
        'response_time_ms' => 'integer',
    ];

    /**
     * Error belongs to a user (if authenticated)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: Filter by error code
     */
    public function scopeByCode($query, string $code)
    {
        return $query->where('error_code', $code);
    }

    /**
     * Scope: Filter by severity
     */
    public function scopeByServerity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    /**
     * Scope: Filter by correlation ID
     */
    public function scopeByCorrelationId($query, string $correlationId)
    {
        return $query->where('correlation_id', $correlationId);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeInDateRange($query, $from, $to)
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }
}
```

### 1.4 Logging Service

**File:** `backend/app/Services/ErrorLoggingService.php`

```php
<?php

namespace App\Services;

use App\Models\ErrorLog;
use Throwable;
use Illuminate\Http\Request;

class ErrorLoggingService
{
    /**
     * Log an error to the database
     */
    public function log(
        Throwable $exception,
        Request $request,
        string $errorCode,
        int $httpStatus,
        ?array $details = null,
        ?array $context = null,
    ): ErrorLog {
        $correlationId = $request->attributes->get('correlation_id', 'unknown');
        
        return ErrorLog::create([
            'correlation_id' => $correlationId,
            'error_code' => $errorCode,
            'message' => $exception->getMessage(),
            'details' => $details,
            'context' => $context,
            'severity' => $this->getSeverity($httpStatus),
            'http_status' => $httpStatus,
            'exception_class' => class_basename($exception),
            'stack_trace' => app()->environment('local') 
                ? $exception->getTraceAsString() 
                : null,
            'user_id' => $request->user()?->id,
            'user_role' => $request->user()?->role->value,
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'request_ip' => $request->ip(),
        ]);
    }

    /**
     * Determine severity from HTTP status
     */
    protected function getSeverity(int $httpStatus): string
    {
        return match ($httpStatus) {
            500, 503 => 'critical',
            400, 401, 403, 404, 422, 429 => 'warning',
            default => 'error',
        };
    }

    /**
     * Query errors by correlation ID
     */
    public function findByCorrelationId(string $correlationId)
    {
        return ErrorLog::byCorrelationId($correlationId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get error statistics
     */
    public function getStatistics($from, $to)
    {
        return [
            'total_errors' => ErrorLog::inDateRange($from, $to)->count(),
            'by_code' => ErrorLog::inDateRange($from, $to)
                ->groupBy('error_code')
                ->selectRaw('error_code, count(*) as count')
                ->get(),
            'by_severity' => ErrorLog::inDateRange($from, $to)
                ->groupBy('severity')
                ->selectRaw('severity, count(*) as count')
                ->get(),
        ];
    }
}
```

---

## 2. ERROR METRICS SCHEMA (OPTIONAL)

### 2.1 Overview

For systems tracking error metrics over time (dashboards, alerting), use this schema.

### 2.2 Error Metrics Table

**File:** `backend/database/migrations/2026_04_11_000100_create_error_metrics_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('error_metrics', function (Blueprint $table) {
            $table->id();
            
            // Time bucket (hourly aggregation)
            $table->timestamp('time_bucket')->index(); // e.g., 2026-04-11 10:00:00
            
            // Error classification
            $table->string('error_code', 100)->index();
            $table->integer('http_status');
            $table->string('severity', 50);
            
            // Metrics
            $table->integer('count')->default(0); // Total occurrences
            $table->decimal('avg_response_time_ms', 8, 2)->nullable();
            $table->integer('max_response_time_ms')->nullable();
            
            // Affected users
            $table->integer('unique_users')->default(0);
            $table->integer('unique_ips')->default(0);
            
            // Timestamps
            $table->timestamps();
            
            // Indexes for query performance
            $table->index(['time_bucket', 'error_code']);
            $table->index(['time_bucket', 'severity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('error_metrics');
    }
};
```

### 2.3 Error Metrics Job

**File:** `backend/app/Jobs/AggregateErrorMetrics.php`

```php
<?php

namespace App\Jobs;

use App\Models\ErrorLog;
use App\Models\ErrorMetric;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AggregateErrorMetrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // Aggregate error logs from last hour
        $oneHourAgo = now()->subHour();
        $hourBucket = now()->startOfHour();
        
        $errors = ErrorLog::whereBetween('created_at', [
            $oneHourAgo,
            now(),
        ])->get();
        
        // Group by error_code and aggregate
        foreach ($errors->groupBy('error_code') as $code => $codeErrors) {
            ErrorMetric::updateOrCreate(
                [
                    'time_bucket' => $hourBucket,
                    'error_code' => $code,
                ],
                [
                    'http_status' => $codeErrors->first()->http_status,
                    'severity' => $codeErrors->first()->severity,
                    'count' => $codeErrors->count(),
                    'avg_response_time_ms' => $codeErrors
                        ->avg('response_time_ms'),
                    'max_response_time_ms' => $codeErrors
                        ->max('response_time_ms'),
                    'unique_users' => $codeErrors
                        ->pluck('user_id')
                        ->filter()
                        ->unique()
                        ->count(),
                    'unique_ips' => $codeErrors
                        ->pluck('request_ip')
                        ->unique()
                        ->count(),
                ]
            );
        }
    }
}
```

---

## 3. AUDIT TRAIL SCHEMA (OPTIONAL)

### 3.1 Overview

For compliance and security auditing, track all state-changing operations.

### 3.2 Audit Trail Table

**File:** `backend/database/migrations/2026_04_11_000200_create_audit_trails_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            
            // Correlation
            $table->string('correlation_id', 255)->index();
            
            // Action information
            $table->string('action', 100)->index(); // create, update, delete, approve, etc.
            $table->string('model_type', 255); // Project, Phase, Task, etc.
            $table->unsignedBigInteger('model_id');
            
            // User information
            $table->foreignIdFor(\App\Models\User::class)
                ->constrained()
                ->cascadeOnDelete();
            
            // Changes
            $table->json('old_values')->nullable(); // Previous state
            $table->json('new_values')->nullable(); // New state
            $table->json('meta')->nullable(); // Additional context
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index(['model_type', 'model_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_trails');
    }
};
```

### 3.3 Audit Trail Trait

**File:** `backend/app/Traits/AuditableTrait.php`

```php
<?php

namespace App\Traits;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait AuditableTrait
{
    protected static function booted(): void
    {
        static::created(function ($model) {
            $model->logAuditTrail('create');
        });
        
        static::updated(function ($model) {
            $model->logAuditTrail('update');
        });
        
        static::deleted(function ($model) {
            $model->logAuditTrail('delete');
        });
    }

    protected function logAuditTrail(string $action): void
    {
        if (!Auth::check()) {
            return;
        }
        
        AuditTrail::create([
            'correlation_id' => Request::get('attributes.correlation_id', 'unknown'),
            'action' => $action,
            'model_type' => class_basename($this),
            'model_id' => $this->id,
            'user_id' => Auth::id(),
            'old_values' => $this->getOriginal(),
            'new_values' => $this->getAttributes(),
        ]);
    }
}
```

---

## 4. DATABASE DESIGN PRINCIPLES

### 4.1 Indexing Strategy

For error logs with millions of entries:

```sql
-- Primary indexes (most queries use these)
CREATE INDEX idx_error_logs_correlation_id ON error_logs(correlation_id);
CREATE INDEX idx_error_logs_error_code ON error_logs(error_code);
CREATE INDEX idx_error_logs_created_at ON error_logs(created_at);

-- Composite indexes (common queries)
CREATE INDEX idx_error_logs_code_date ON error_logs(error_code, created_at);
CREATE INDEX idx_error_logs_user_date ON error_logs(user_id, created_at);

-- Query: Find all errors for correlation ID
-- Uses: idx_error_logs_correlation_id
SELECT * FROM error_logs 
WHERE correlation_id = 'req_123' 
ORDER BY created_at DESC;

-- Query: Find errors by code in time range
-- Uses: idx_error_logs_code_date
SELECT * FROM error_logs 
WHERE error_code = 'VALIDATION_ERROR' 
AND created_at BETWEEN ? AND ? 
LIMIT 100;
```

### 4.2 Data Retention Policy

```php
// Job to clean up old error logs
class PruneOldErrorLogs implements ShouldQueue
{
    public function handle(): void
    {
        // Delete logs older than 90 days
        ErrorLog::where('created_at', '<', now()->subDays(90))
            ->delete();
    }
}

// Schedule in Kernel.php
$schedule->job(new PruneOldErrorLogs)
    ->daily()
    ->at('2:00'); // Run at 2 AM
```

### 4.3 Partitioning Strategy (For Very Large Tables)

For systems logging millions of errors, partition by date:

```sql
-- Create partitions by month
CREATE TABLE error_logs_2026_04 PARTITION OF error_logs
    FOR VALUES FROM ('2026-04-01') TO ('2026-05-01');

CREATE TABLE error_logs_2026_05 PARTITION OF error_logs
    FOR VALUES FROM ('2026-05-01') TO ('2026-06-01');
```

---

## 5. IN-MEMORY ERROR REGISTRY (APPLICATION LAYER)

### 5.1 Error Code Registry

**File:** `backend/app/Services/ErrorCodeRegistry.php`

```php
<?php

namespace App\Services;

use App\Enums\ErrorCode;

class ErrorCodeRegistry
{
    private static array $registry = [
        'VALIDATION_ERROR' => [
            'http_status' => 422,
            'severity' => 'warning',
            'description' => 'Input validation failed',
            'retry' => true,
        ],
        'AUTH_UNAUTHORIZED' => [
            'http_status' => 401,
            'severity' => 'warning',
            'description' => 'User not authenticated',
            'retry' => false,
        ],
        'RBAC_ROLE_DENIED' => [
            'http_status' => 403,
            'severity' => 'warning',
            'description' => 'User role not permitted',
            'retry' => false,
        ],
        'RESOURCE_NOT_FOUND' => [
            'http_status' => 404,
            'severity' => 'warning',
            'description' => 'Resource not found',
            'retry' => false,
        ],
        'WORKFLOW_INVALID_TRANSITION' => [
            'http_status' => 422,
            'severity' => 'warning',
            'description' => 'Invalid state transition',
            'retry' => false,
        ],
        'PAYMENT_FAILED' => [
            'http_status' => 422,
            'severity' => 'error',
            'description' => 'Payment processing failed',
            'retry' => true,
        ],
        'RATE_LIMIT_EXCEEDED' => [
            'http_status' => 429,
            'severity' => 'warning',
            'description' => 'Too many requests',
            'retry' => true,
        ],
        'SERVER_ERROR' => [
            'http_status' => 500,
            'severity' => 'error',
            'description' => 'Internal server error',
            'retry' => true,
        ],
    ];

    public static function get(string $errorCode): array
    {
        return self::$registry[$errorCode] ?? [
            'http_status' => 500,
            'severity' => 'error',
            'description' => 'Unknown error',
        ];
    }

    public static function httpStatus(string $errorCode): int
    {
        return self::get($errorCode)['http_status'];
    }

    public static function severity(string $errorCode): string
    {
        return self::get($errorCode)['severity'];
    }

    public static function isRetryable(string $errorCode): bool
    {
        return self::get($errorCode)['retry'] ?? false;
    }

    public static function all(): array
    {
        return self::$registry;
    }
}
```

---

## 6. IMPLEMENTATION ORDER

### Phase 1 (MVP - File-based only)

- No database schema needed
- Errors logged to JSON files via Monolog
- `backend/storage/logs/structured.log`

**Cost:** None (no migrations)  
**Benefit:** Simple, sufficient for debugging

### Phase 2 (When persisting errors needed)

1. Create `error_logs` table migration
2. Create `ErrorLog` model
3. Create `ErrorLoggingService`
4. Update exception handler to log to database

**Cost:** 1-2 hours  
**Benefit:** Queryable error history, audit trail

### Phase 3 (When metrics needed)

1. Create `error_metrics` table migration
2. Create `AggregateErrorMetrics` job
3. Schedule job in kernel
4. Create dashboard query endpoint

**Cost:** 1-2 hours  
**Benefit:** Error trends, alerting capability

### Phase 4 (When audit trail needed)

1. Create `audit_trails` table migration
2. Create `AuditableTrait`
3. Apply to models (Project, Phase, Task, etc.)
4. Create audit dashboard

**Cost:** 2-3 hours  
**Benefit:** Compliance, security audit trail

---

## 7. QUERIES FOR COMMON USE CASES

### Query 1: Trace Request by Correlation ID

```php
$errors = ErrorLog::byCorrelationId('req_1712844645_abc123')
    ->orderBy('created_at', 'asc')
    ->get();
```

### Query 2: Find All Validation Errors in Last Hour

```php
$recent = ErrorLog::byCode('VALIDATION_ERROR')
    ->where('created_at', '>=', now()->subHour())
    ->orderBy('created_at', 'desc')
    ->get();
```

### Query 3: Error Rate by Hour

```php
$hourly = ErrorLog::selectRaw('
    DATE_FORMAT(created_at, "%Y-%m-%d %H:00:00") as hour,
    count(*) as count
')
->where('created_at', '>=', now()->subDays(7))
->groupBy('hour')
->orderBy('hour', 'desc')
->get();
```

### Query 4: Most Common Errors

```php
$topErrors = ErrorLog::selectRaw('
    error_code,
    count(*) as count
')
->where('created_at', '>=', now()->subDays(7))
->groupBy('error_code')
->orderBy('count', 'desc')
->limit(10)
->get();
```

### Query 5: Errors by User Role

```php
$byRole = ErrorLog::selectRaw('
    user_role,
    count(*) as count
')
->whereNotNull('user_role')
->where('created_at', '>=', now()->subHours(24))
->groupBy('user_role')
->get();
```

---

## 8. SCHEMA SUMMARY

| Table | Rows (Annual) | Size | Purpose |
|---|---|---|---|
| `error_logs` | ~5-10M | 500GB+ | Error audit trail |
| `error_metrics` | ~8,760 | 5MB | Time-series metrics |
| `audit_trails` | ~1-5M | 200GB+ | State change audit |

**Note:** Actual sizes depend on business volume. These are estimates for high-traffic system.

---

**Generated:** 2026-04-11 | **Authority:** db-migration-governance skill
