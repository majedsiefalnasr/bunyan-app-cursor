<?php

namespace Tests\Unit\Services;

use App\Services\ErrorLoggingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ErrorLoggingServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_returns_null_and_empty_results_when_table_missing(): void
    {
        Schema::dropIfExists('error_logs');

        $service = app(ErrorLoggingService::class);

        $this->assertNull($service->log([
            'correlation_id' => 'c1',
            'error_code' => 'E_TEST',
            'message' => 'm',
        ]));

        $this->assertSame([], $service->getStatistics());
        $this->assertCount(0, $service->findByCorrelationId('c1'));
    }

    public function test_it_logs_and_reads_statistics_when_table_exists(): void
    {
        $this->assertTrue(Schema::hasTable('error_logs'));

        $service = app(ErrorLoggingService::class);

        $row = $service->log([
            'correlation_id' => 'c2',
            'error_code' => 'E_X',
            'message' => 'boom',
            'severity' => 'error',
        ]);

        $this->assertNotNull($row);
        $this->assertSame('c2', $row->correlation_id);

        $found = $service->findByCorrelationId('c2');
        $this->assertCount(1, $found);

        $stats = $service->getStatistics(24);
        $this->assertSame(1, $stats['E_X']);
    }
}
