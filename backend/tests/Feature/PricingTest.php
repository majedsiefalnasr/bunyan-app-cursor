<?php

namespace Tests\Feature\Api\V1;

use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PricingTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_product_pricing_requires_auth(): void
    {
        $product = Product::factory()->create();

        $this->getJson("/api/v1/products/{$product->id}/pricing")
            ->assertStatus(401);
    }

    public function test_get_product_pricing_returns_tiers(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        PriceTier::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => null,
            'min_quantity' => 1,
            'max_quantity' => 5,
            'unit_price' => 10.00,
        ]);

        $this->actingAs($user)
            ->getJson("/api/v1/products/{$product->id}/pricing")
            ->assertStatus(200)
            ->assertJsonCount(1, 'data.tiers');
    }

    public function test_admin_can_sync_pricing(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();

        $this->actingAs($admin)
            ->putJson("/api/v1/admin/products/{$product->id}/pricing", [
                'tiers' => [
                    ['min_quantity' => 1, 'max_quantity' => 10, 'unit_price' => 100, 'product_variant_id' => null],
                    ['min_quantity' => 11, 'max_quantity' => null, 'unit_price' => 90, 'product_variant_id' => null],
                ],
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.tiers.0.unit_price', '100.00');

        $this->assertDatabaseCount('price_tiers', 2);
    }

    public function test_non_admin_cannot_sync_pricing(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->putJson("/api/v1/admin/products/{$product->id}/pricing", [
                'tiers' => [
                    ['min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 1, 'product_variant_id' => null],
                ],
            ])
            ->assertStatus(403);
    }

    public function test_sync_rejects_overlapping_bands(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();

        $this->actingAs($admin)
            ->putJson("/api/v1/admin/products/{$product->id}/pricing", [
                'tiers' => [
                    ['min_quantity' => 1, 'max_quantity' => 10, 'unit_price' => 100, 'product_variant_id' => null],
                    ['min_quantity' => 10, 'max_quantity' => null, 'unit_price' => 90, 'product_variant_id' => null],
                ],
            ])
            ->assertStatus(422);
    }

    public function test_calculate_uses_tier_unit_price(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 50.00]);

        PriceTier::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => null,
            'min_quantity' => 1,
            'max_quantity' => 10,
            'unit_price' => 40.00,
        ]);

        $this->actingAs($user)
            ->postJson('/api/v1/pricing/calculate', [
                'product_id' => $product->id,
                'quantity' => 3,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.unit_price', '40.00')
            ->assertJsonPath('data.line_total', '120.00')
            ->assertJsonPath('data.currency', 'SAR');
    }

    public function test_calculate_falls_back_to_base_price(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 25.50]);

        $this->actingAs($user)
            ->postJson('/api/v1/pricing/calculate', [
                'product_id' => $product->id,
                'quantity' => 2,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.unit_price', '25.50')
            ->assertJsonPath('data.line_total', '51.00');
    }

    public function test_product_price_update_writes_history(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create(['price' => 10.00]);

        $this->actingAs($admin)
            ->putJson("/api/v1/admin/products/{$product->id}", [
                'price' => 15.50,
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('price_histories', [
            'product_id' => $product->id,
            'old_price' => 10.00,
            'new_price' => 15.50,
        ]);
    }

    public function test_variant_tiers_take_precedence(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100.00]);
        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'name' => 'Size L',
            'sku' => 'VAR-TEST-1',
            'price_modifier' => 5.00,
            'stock_quantity' => 10,
            'attributes_json' => null,
            'is_active' => true,
        ]);

        PriceTier::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'min_quantity' => 1,
            'max_quantity' => null,
            'unit_price' => 77.00,
        ]);

        $this->actingAs($user)
            ->postJson('/api/v1/pricing/calculate', [
                'product_id' => $product->id,
                'product_variant_id' => $variant->id,
                'quantity' => 1,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.unit_price', '77.00');
    }
}
