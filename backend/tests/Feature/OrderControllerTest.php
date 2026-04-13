<?php

namespace Tests\Feature\Api\V1;

use App\Enums\OrderStatus;
use App\Enums\QuotationStatus;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\Product;
use App\Models\Project;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Rfq;
use App\Models\RfqItem;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_own_orders()
    {
        $user = User::factory()->create();
        Order::factory()->count(3)->create(['customer_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_list_all_orders_as_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create();
        Order::factory()->create(['customer_id' => $user->id]);
        Order::factory()->create(['customer_id' => $admin->id]);

        $response = $this->actingAs($admin)
            ->getJson('/api/v1/orders');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_show_own_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['customer_id' => $user->id]);

        $response = $this->actingAs($user)
            ->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_create_order_successfully()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 5,
                        'price' => 250.00,
                    ],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);
    }

    public function test_cannot_view_others_order()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['customer_id' => $user2->id]);

        $response = $this->actingAs($user1)
            ->getJson("/api/v1/orders/{$order->id}");

        $response->assertStatus(403);
    }

    public function test_confirm_reserves_inventory(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create();
        Inventory::query()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'warehouse_location' => 'default',
            'quantity' => 100,
            'reserved_quantity' => 0,
            'min_quantity' => 0,
        ]);

        $create = $this->actingAs($user)
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 3,
                        'price' => 10.00,
                    ],
                ],
            ]);

        $create->assertStatus(201);
        $orderId = (int) $create->json('data.id');

        $confirm = $this->actingAs($user)
            ->putJson("/api/v1/orders/{$orderId}/confirm");

        $confirm->assertStatus(200)
            ->assertJsonPath('data.status', 'confirmed');

        $line = Inventory::query()->where('product_id', $product->id)->first();
        $this->assertNotNull($line);
        $this->assertSame(3, (int) $line->reserved_quantity);
    }

    public function test_customer_can_cancel_pending_order(): void
    {
        $user = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'customer_id' => $user->id,
            'status' => OrderStatus::Pending->value,
        ]);

        $this->actingAs($user)
            ->putJson("/api/v1/orders/{$order->id}/cancel")
            ->assertStatus(200)
            ->assertJsonPath('data.status', OrderStatus::Cancelled->value);
    }

    public function test_customer_cancel_confirmed_order_releases_inventory(): void
    {
        $user = User::factory()->customer()->create();
        $product = Product::factory()->create();
        Inventory::query()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'warehouse_location' => 'default',
            'quantity' => 100,
            'reserved_quantity' => 0,
            'min_quantity' => 0,
        ]);

        $create = $this->actingAs($user)
            ->postJson('/api/v1/orders', [
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 4,
                        'price' => 5.00,
                    ],
                ],
            ]);
        $create->assertStatus(201);
        $orderId = (int) $create->json('data.id');

        $this->actingAs($user)
            ->putJson("/api/v1/orders/{$orderId}/confirm")
            ->assertStatus(200);

        $this->actingAs($user)
            ->putJson("/api/v1/orders/{$orderId}/cancel")
            ->assertStatus(200)
            ->assertJsonPath('data.status', OrderStatus::Cancelled->value);

        $line = Inventory::query()->where('product_id', $product->id)->first();
        $this->assertNotNull($line);
        $this->assertSame(0, (int) $line->reserved_quantity);
    }

    public function test_admin_can_transition_order_status(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create([
            'status' => OrderStatus::Pending->value,
        ]);

        $this->actingAs($admin)
            ->putJson("/api/v1/orders/{$order->id}/status", [
                'status' => OrderStatus::Confirmed->value,
            ])
            ->assertStatus(200)
            ->assertJsonPath('data.status', OrderStatus::Confirmed->value);
    }

    public function test_customer_cannot_transition_order_status(): void
    {
        $user = User::factory()->customer()->create();
        $order = Order::factory()->create([
            'customer_id' => $user->id,
            'status' => OrderStatus::Pending->value,
        ]);

        $this->actingAs($user)
            ->putJson("/api/v1/orders/{$order->id}/status", [
                'status' => OrderStatus::Confirmed->value,
            ])
            ->assertStatus(403);
    }

    public function test_admin_invalid_status_transition_returns_422(): void
    {
        $admin = User::factory()->admin()->create();
        $order = Order::factory()->create([
            'status' => OrderStatus::Pending->value,
        ]);

        $this->actingAs($admin)
            ->putJson("/api/v1/orders/{$order->id}/status", [
                'status' => OrderStatus::Shipped->value,
            ])
            ->assertStatus(422);
    }

    public function test_customer_creates_order_from_accepted_quotation(): void
    {
        $customer = User::factory()->customer()->create();
        $project = Project::factory()->create([
            'customer_id' => $customer->id,
        ]);
        $product = Product::factory()->create();
        $rfq = Rfq::factory()->awarded()->create([
            'project_id' => $project->id,
            'created_by' => $customer->id,
        ]);
        $rfqItem = RfqItem::factory()->create([
            'rfq_id' => $rfq->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'unit' => 'قطعة',
        ]);
        $supplier = SupplierProfile::factory()->verified()->create();
        $quotation = Quotation::factory()->create([
            'rfq_id' => $rfq->id,
            'supplier_id' => $supplier->id,
            'status' => QuotationStatus::Accepted->value,
        ]);
        QuotationItem::factory()->create([
            'quotation_id' => $quotation->id,
            'rfq_item_id' => $rfqItem->id,
            'unit_price' => 15.00,
            'total_price' => 30.00,
        ]);

        $response = $this->actingAs($customer)
            ->postJson("/api/v1/quotations/{$quotation->id}/to-order");

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.quotation_id', $quotation->id)
            ->assertJsonPath('data.supplier_id', $supplier->id);

        $this->assertDatabaseHas('orders', [
            'quotation_id' => $quotation->id,
            'customer_id' => $customer->id,
        ]);
    }
}
