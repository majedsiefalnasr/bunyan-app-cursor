<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class InjectCorrelationId
{
    public function handle(Request $request, Closure $next): Response
    {
        $header = $request->headers->get('X-Correlation-ID');
        $correlationId = is_string($header) && $header !== '' ? $header : $this->generate();

        $request->attributes->set('correlation_id', $correlationId);

        Log::withContext([
            'correlation_id' => $correlationId,
        ]);

        return $next($request);
    }

    private function generate(): string
    {
        return sprintf('req_%d_%s', (int) (microtime(true) * 1000), bin2hex(random_bytes(4)));
    }
}
