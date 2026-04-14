<?php

namespace Tests\Unit\Services;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class InventoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private InventoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(InventoryService::class);
    }

    public function test_adjust_creates_inventory_line_and_updates_quantity(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();

        $line = $this->service->adjust($product, $user, [
            'quantity_delta' => 5,
        ]);

        $this->assertSame($product->id, $line->product_id);
        $this->assertSame(5, (int) $line->quantity);
        $this->assertSame(0, (int) $line->reserved_quantity);
    }

    public function test_adjust_rejects_negative_inventory(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();

        $this->service->adjust($product, $user, [
            'quantity_delta' => 2,
        ]);

        $this->expectException(ValidationException::class);
        $this->service->adjust($product, $user, [
            'quantity_delta' => -3,
        ]);
    }

    public function test_reserve_and_release_for_order_updates_reserved_quantity(): void
    {
        $actor = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create();

        Inventory::query()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'warehouse_location' => 'default',
            'quantity' => 10,
            'reserved_quantity' => 0,
            'min_quantity' => 0,
        ]);

        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $order->items()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'quantity' => 3,
            'unit_price' => 10,
            'subtotal' => 30,
            'description' => null,
        ]);

        $order->load('items.product');

        $this->service->reserveForOrder($actor, $order);
        $line = Inventory::query()->where('product_id', $product->id)->firstOrFail();
        $this->assertSame(3, (int) $line->reserved_quantity);

        $this->service->releaseReservationsForOrder($actor, $order);
        $line = $line->fresh();
        $this->assertSame(0, (int) $line->reserved_quantity);
    }

    public function test_reserve_for_order_requires_existing_inventory_line(): void
    {
        $actor = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create();

        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $order->items()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'quantity' => 1,
            'unit_price' => 10,
            'subtotal' => 10,
            'description' => null,
        ]);

        $order->load('items.product');

        $this->expectException(ValidationException::class);
        $this->service->reserveForOrder($actor, $order);
    }
}
