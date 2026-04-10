<?php

namespace Tests\Feature\Api\V1;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_products_successfully()
    {
        Product::factory()->count(5)->create();

        $response = $this->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_show_product_successfully()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $product->id);
    }

    public function test_create_product_by_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/products', [
                'name' => 'Cement',
                'category' => 'building_materials',
                'price' => 50.00,
                'quantity' => 100,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Cement');
    }

    public function test_create_product_unauthorized()
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)
            ->postJson('/api/v1/products', [
                'name' => 'Cement',
                'category' => 'building_materials',
                'price' => 50.00,
                'quantity' => 100,
            ]);

        $response->assertStatus(403);
    }

    public function test_update_product_by_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)
            ->putJson("/api/v1/products/{$product->id}", [
                'name' => 'Updated Product',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Product');
    }
}
