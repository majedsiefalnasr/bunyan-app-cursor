<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\LogApiActivity;
use Illuminate\Http\Request;
use Tests\TestCase;

class LogApiActivityTest extends TestCase
{
    public function test_records_response_status(): void
    {
        $middleware = new LogApiActivity;
        $request = Request::create('/api/v1/ping', 'GET');
        $request->attributes->set('correlation_id', 'cid-1');

        $response = $middleware->handle($request, fn () => response('ok', 204));

        $this->assertSame(204, $response->getStatusCode());
    }
}
