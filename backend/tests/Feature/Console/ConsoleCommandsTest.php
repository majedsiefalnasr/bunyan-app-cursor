<?php

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ConsoleCommandsTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_log_prune_dry_run_succeeds(): void
    {
        $exit = Artisan::call('activity-log:prune', [
            '--days' => 365,
            '--dry-run' => true,
        ]);

        $this->assertSame(0, $exit);
    }

    public function test_activity_log_prune_rejects_invalid_retention(): void
    {
        $exit = Artisan::call('activity-log:prune', ['--days' => 0]);

        $this->assertSame(1, $exit);
    }

    public function test_inventory_check_low_stock_succeeds(): void
    {
        $exit = Artisan::call('inventory:check-low-stock');

        $this->assertSame(0, $exit);
    }

    public function test_media_prune_temporary_succeeds(): void
    {
        $exit = Artisan::call('media:prune-temporary', ['--hours' => 24]);

        $this->assertSame(0, $exit);
    }

    public function test_media_prune_temporary_rejects_invalid_hours(): void
    {
        $exit = Artisan::call('media:prune-temporary', ['--hours' => 0]);

        $this->assertSame(1, $exit);
    }
}
