<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\InjectCorrelationId;
use Illuminate\Http\Request;
use Tests\TestCase;

class InjectCorrelationIdTest extends TestCase
{
    public function test_extracts_header_value(): void
    {
        $middleware = new InjectCorrelationId;
        $request = Request::create('/api/v1/ping', 'GET');
        $request->headers->set('X-Correlation-ID', 'custom-id');

        $middleware->handle($request, function (Request $r) {
            $this->assertSame('custom-id', $r->attributes->get('correlation_id'));

            return response('ok');
        });
    }

    public function test_generates_when_missing(): void
    {
        $middleware = new InjectCorrelationId;
        $request = Request::create('/api/v1/ping', 'GET');

        $middleware->handle($request, function (Request $r) {
            $id = (string) $r->attributes->get('correlation_id');
            $this->assertMatchesRegularExpression('/^req_\d+_[0-9a-f]{8}$/', $id);

            return response('ok');
        });
    }
}
