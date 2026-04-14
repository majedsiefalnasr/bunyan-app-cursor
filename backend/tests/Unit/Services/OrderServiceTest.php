<?php

namespace Tests\Unit\Services;

use App\Enums\OrderStatus;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private OrderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OrderService::class);
    }

    public function test_create_from_items_creates_order_and_items(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        $p1 = Product::factory()->create();
        $p2 = Product::factory()->create();

        $order = $this->service->createFromItems($customer, [
            'items' => [
                ['product_id' => $p1->id, 'quantity' => 2, 'price' => 10],
                ['product_id' => $p2->id, 'quantity' => 1, 'price' => 25],
            ],
        ]);

        $this->assertSame($customer->id, $order->customer_id);
        $this->assertCount(2, $order->items);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame(45.0, (float) $order->subtotal);
        $this->assertSame(45.0, (float) $order->total_amount);
    }

    public function test_confirm_reserves_inventory_and_sets_confirmed_status(): void
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

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Pending->value,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'quantity' => 4,
            'unit_price' => 10,
            'subtotal' => 40,
            'description' => null,
        ]);

        $confirmed = $this->service->confirm($actor, $order);
        $this->assertSame(OrderStatus::Confirmed, $confirmed->status);

        $line = Inventory::query()->where('product_id', $product->id)->firstOrFail();
        $this->assertSame(4, (int) $line->reserved_quantity);
    }

    public function test_cancel_after_confirm_releases_inventory(): void
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

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatus::Pending->value,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'quantity' => 2,
            'unit_price' => 10,
            'subtotal' => 20,
            'description' => null,
        ]);

        $confirmed = $this->service->confirm($actor, $order);
        $cancelled = $this->service->cancel($actor, $confirmed);

        $this->assertSame(OrderStatus::Cancelled, $cancelled->status);
        $line = Inventory::query()->where('product_id', $product->id)->firstOrFail();
        $this->assertSame(0, (int) $line->reserved_quantity);
    }

    public function test_transition_status_rejects_invalid_transitions(): void
    {
        $actor = User::factory()->create(['role' => 'admin']);
        $order = Order::factory()->create(['status' => OrderStatus::Pending->value]);

        $this->expectException(ValidationException::class);
        $this->service->transitionStatus($actor, $order, OrderStatus::Shipped);
    }
}
