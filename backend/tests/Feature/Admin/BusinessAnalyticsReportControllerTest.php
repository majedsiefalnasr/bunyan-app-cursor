<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessAnalyticsReportControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_list_report_types(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/analytics/reports/types');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['types']]);
    }

    public function test_non_admin_cannot_access_analytics_reports(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->getJson('/api/v1/admin/analytics/reports/types');

        $response->assertStatus(403);
    }

    public function test_admin_can_generate_sales_summary(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Order::factory()->count(2)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/analytics/reports/sales_summary');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => [
                    'meta' => ['report_type', 'generated_at', 'truncated', 'stub'],
                    'summary',
                    'rows',
                ],
            ]);
    }

    public function test_export_xlsx_returns_binary_response(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(
            '/api/v1/admin/analytics/reports/sales_summary/export?format=xlsx'
        );

        $response->assertOk();
        $this->assertStringContainsString('spreadsheetml', (string) $response->headers->get('content-type'));
    }
}
