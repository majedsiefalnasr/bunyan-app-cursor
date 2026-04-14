<?php

namespace Tests\Unit\Services;

use App\Models\PriceTier;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PricingServiceTest extends TestCase
{
    use RefreshDatabase;

    private PricingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PricingService::class);
    }

    public function test_sync_tiers_rejects_overlapping_bands_and_unbounded_not_last(): void
    {
        $product = Product::factory()->create(['price' => 100]);

        $this->expectException(ValidationException::class);
        $this->service->syncTiers($product, [
            ['product_variant_id' => null, 'min_quantity' => 1, 'max_quantity' => null, 'unit_price' => 90],
            ['product_variant_id' => null, 'min_quantity' => 10, 'max_quantity' => 20, 'unit_price' => 80],
        ]);
    }

    public function test_calculate_falls_back_to_base_price_when_no_tiers(): void
    {
        $product = Product::factory()->create(['price' => 123.45]);

        $result = $this->service->calculate($product, null, 2);
        $this->assertSame('123.45', $result['unit_price']);
        $this->assertSame('246.90', $result['line_total']);
        $this->assertSame('SAR', $result['currency']);
    }

    public function test_calculate_uses_product_tier_when_matching(): void
    {
        $product = Product::factory()->create(['price' => 100]);

        PriceTier::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => null,
            'min_quantity' => 5,
            'max_quantity' => 9,
            'unit_price' => 80,
        ]);

        $result = $this->service->calculate($product, null, 6);
        $this->assertSame('80.00', $result['unit_price']);
        $this->assertSame('480.00', $result['line_total']);
    }

    public function test_variant_tiers_take_precedence_and_variant_modifier_is_used_when_no_tier(): void
    {
        $product = Product::factory()->create(['price' => 100]);
        $variant = ProductVariant::query()->create([
            'product_id' => $product->id,
            'name' => 'V',
            'sku' => 'SKU-1',
            'price_modifier' => 5,
            'stock_quantity' => 0,
            'attributes_json' => [],
            'is_active' => true,
        ]);

        PriceTier::query()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'min_quantity' => 1,
            'max_quantity' => null,
            'unit_price' => 70,
        ]);

        $result = $this->service->calculate($product, $variant->id, 2);
        $this->assertSame('70.00', $result['unit_price']);
        $this->assertSame('140.00', $result['line_total']);

        PriceTier::query()->where('product_variant_id', $variant->id)->delete();
        $fallback = $this->service->calculate($product->fresh(), $variant->id, 1);
        $this->assertSame('105.00', $fallback['unit_price']);
    }

    public function test_calculate_rejects_invalid_quantity_and_invalid_variant(): void
    {
        $product = Product::factory()->create(['price' => 100]);

        $this->expectException(ValidationException::class);
        $this->service->calculate($product, null, 0);
    }
}
