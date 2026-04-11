<?php

namespace Tests\Feature\Api\V1;

use App\Models\Order;
use App\Models\Product;
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
}
