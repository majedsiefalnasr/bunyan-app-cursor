<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidatePaymentWebhookSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = (string) config('payments.webhook_secret');
        $provided = (string) $request->header('X-Payment-Webhook-Secret', '');

        if ($configured === '') {
            abort(Response::HTTP_SERVICE_UNAVAILABLE, 'Payment webhook is not configured.');
        }

        if (! hash_equals($configured, $provided)) {
            abort(Response::HTTP_FORBIDDEN, 'Invalid webhook signature.');
        }

        return $next($request);
    }
}
