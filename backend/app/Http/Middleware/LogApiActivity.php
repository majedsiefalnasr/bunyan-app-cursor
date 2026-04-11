<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogApiActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $startedAt = microtime(true);

        $response = $next($request);

        $durationMs = round((microtime(true) - $startedAt) * 1000, 2);

        $user = $request->user();
        $role = null;
        if ($user !== null && $user->role !== null) {
            // User model casts role to UserRole (string-backed enum).
            $role = $user->role->value;
        }

        Log::channel('structured')->info('api.request', [
            'timestamp' => now()->toIso8601String(),
            'correlation_id' => $request->attributes->get('correlation_id'),
            'user_id' => $user?->id,
            'user_role' => $role,
            'request_method' => $request->method(),
            'request_path' => $request->path(),
            'response_status' => $response->getStatusCode(),
            'duration_ms' => $durationMs,
        ]);

        return $response;
    }
}
