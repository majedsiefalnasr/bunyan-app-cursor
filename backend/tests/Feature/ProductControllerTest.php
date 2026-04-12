<?php

namespace Tests\Feature\Api\V1;

use App\Enums\SupplierVerificationStatus;
use App\Models\Category;
use App\Models\Product;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_list_products_successfully()
    {
        Product::factory()->count(5)->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/products');

        $response->assertStatus(200)
            ->assertJsonCount(5, 'data');
    }

    public function test_show_product_successfully()
    {
        $product = Product::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson("/api/v1/products/{$product->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $product->id);
    }

    public function test_create_product_by_admin()
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/products', [
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
            ->postJson('/api/v1/admin/products', [
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
            ->putJson("/api/v1/admin/products/{$product->id}", [
                'name' => 'Updated Product',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Product');
    }

    public function test_create_product_by_admin_with_category_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $category = Category::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson('/api/v1/admin/products', [
                'name' => 'أسمنت',
                'category_id' => $category->id,
                'price' => 50.00,
                'quantity' => 100,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.category_id', $category->id);
    }

    public function test_list_products_filters_by_category_id(): void
    {
        $category = Category::factory()->create();
        Product::factory()->create(['category_id' => $category->id, 'active' => true]);
        Product::factory()->count(2)->create(['active' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/products?category_id='.$category->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_list_products_filters_by_supplier_id(): void
    {
        $owner = User::factory()->contractor()->create();
        $profile = SupplierProfile::query()->create([
            'user_id' => $owner->id,
            'company_name_ar' => 'مورد تجريبي',
            'verification_status' => SupplierVerificationStatus::Verified->value,
        ]);
        Product::factory()->create(['supplier_id' => $profile->id, 'active' => true]);
        Product::factory()->create(['active' => true]);
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/products?supplier_id='.$profile->id);

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_admin_can_add_variant(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/products/{$product->id}/variants", [
                'name' => 'عبوة 50 كجم',
                'stock_quantity' => 20,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'عبوة 50 كجم');
    }

    public function test_admin_can_add_media_row(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson("/api/v1/admin/products/{$product->id}/media", [
                'type' => 'image',
                'path' => '/storage/demo/x.png',
                'sort_order' => 1,
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.type', 'image');
    }

    public function test_customer_cannot_add_variant(): void
    {
        $user = User::factory()->create(['role' => 'customer']);
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
            ->postJson("/api/v1/admin/products/{$product->id}/variants", [
                'name' => 'غير مسموح',
            ]);

        $response->assertStatus(403);
    }
}
