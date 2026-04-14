<?php

namespace Tests\Feature\Analytics;

use App\Enums\AnalyticsMetricKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

final class AnalyticsEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_requests_return_401(): void
    {
        $this->getJson('/api/v1/analytics/overview')->assertStatus(Response::HTTP_UNAUTHORIZED);
        $this->getJson('/api/v1/analytics/metrics/'.AnalyticsMetricKey::CommerceGmv->value)->assertStatus(Response::HTTP_UNAUTHORIZED);
        $this->getJson('/api/v1/analytics/trends?keys[]='.AnalyticsMetricKey::CommerceGmv->value)->assertStatus(Response::HTTP_UNAUTHORIZED);
    }

    public function test_only_admin_and_supervising_architect_can_access(): void
    {
        $admin = User::factory()->admin()->create();
        $arch = User::factory()->supervisingArchitect()->create();

        $rolesDenied = [
            User::factory()->customer()->create(),
            User::factory()->contractor()->create(),
            User::factory()->fieldEngineer()->create(),
        ];

        foreach ([$admin, $arch] as $allowed) {
            $this->actingAs($allowed)->getJson('/api/v1/analytics/overview')->assertOk();
        }

        foreach ($rolesDenied as $denied) {
            $this->actingAs($denied)->getJson('/api/v1/analytics/overview')->assertStatus(Response::HTTP_FORBIDDEN);
        }
    }

    public function test_unknown_metric_returns_404(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson('/api/v1/analytics/metrics/unknown.metric.key')
            ->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function test_trends_requires_keys_and_rejects_empty_valid_set(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->getJson('/api/v1/analytics/trends')->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->actingAs($admin)
            ->getJson('/api/v1/analytics/trends?keys[]=unknown.metric')
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function test_points_cap_returns_validation_error(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->getJson('/api/v1/analytics/metrics/'.AnalyticsMetricKey::CommerceGmv->value.'?bucket=day&from=2020-01-01&to=2026-01-01')
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }
}
