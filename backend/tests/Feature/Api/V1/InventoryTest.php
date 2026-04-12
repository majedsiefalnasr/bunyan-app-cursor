<?php

namespace Tests\Feature\Api\V1;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, mixed>
     */
    private function unwrapDataList(array $responseData): array
    {
        if ($responseData === []) {
            return [];
        }

        if (array_is_list($responseData)) {
            return $responseData;
        }

        return isset($responseData['data']) && is_array($responseData['data'])
            ? $responseData['data']
            : [];
    }

    public function test_guest_cannot_list_inventory(): void
    {
        $this->getJson('/api/v1/inventory')->assertStatus(401);
    }

    public function test_customer_cannot_list_inventory(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $this->actingAs($user)->getJson('/api/v1/inventory')->assertStatus(403);
    }

    public function test_admin_can_list_inventory(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        Inventory::query()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'warehouse_location' => 'default',
            'quantity' => 5,
            'reserved_quantity' => 0,
            'min_quantity' => 1,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/inventory');

        $response->assertOk()->assertJsonPath('success', true);
        $rows = $this->unwrapDataList((array) $response->json('data'));
        $this->assertGreaterThanOrEqual(1, count($rows));
    }

    public function test_admin_can_adjust_inventory_and_records_movement(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['quantity_in_stock' => 10]);

        $response = $this->actingAs($admin)->putJson("/api/v1/inventory/{$product->id}/adjust", [
            'quantity_delta' => 3,
            'notes' => 'restock',
        ]);

        $response->assertOk()->assertJsonPath('data.quantity', 3);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'adjust',
            'quantity' => 3,
            'created_by' => $admin->id,
        ]);
        $product->refresh();
        $this->assertSame(13, (int) $product->quantity_in_stock);
    }

    public function test_contractor_can_adjust_own_supplier_product(): void
    {
        $contractor = User::factory()->contractor()->create();
        $profile = SupplierProfile::factory()->verified()->create(['user_id' => $contractor->id]);
        $product = Product::factory()->create(['supplier_id' => $profile->id, 'quantity_in_stock' => 0]);

        $response = $this->actingAs($contractor)->putJson("/api/v1/inventory/{$product->id}/adjust", [
            'quantity_delta' => 2,
        ]);

        $response->assertOk()->assertJsonPath('data.quantity', 2);
    }

    public function test_contractor_cannot_adjust_other_supplier_product(): void
    {
        $contractor = User::factory()->contractor()->create();
        SupplierProfile::factory()->verified()->create(['user_id' => $contractor->id]);
        $otherProfile = SupplierProfile::factory()->verified()->create();
        $product = Product::factory()->create(['supplier_id' => $otherProfile->id]);

        $this->actingAs($contractor)->putJson("/api/v1/inventory/{$product->id}/adjust", [
            'quantity_delta' => 1,
        ])->assertStatus(403);
    }

    public function test_admin_can_list_movements(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        StockMovement::query()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'type' => 'adjust',
            'quantity' => 1,
            'reference_type' => null,
            'reference_id' => null,
            'notes' => null,
            'created_by' => $admin->id,
            'created_at' => now(),
        ]);

        $this->actingAs($admin)
            ->getJson("/api/v1/inventory/{$product->id}/movements")
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_low_stock_endpoint_returns_rows(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create();
        Inventory::query()->create([
            'product_id' => $product->id,
            'variant_id' => null,
            'warehouse_location' => 'default',
            'quantity' => 1,
            'reserved_quantity' => 0,
            'min_quantity' => 5,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/v1/inventory/low-stock');

        $response->assertOk();
        $rows = $this->unwrapDataList((array) $response->json('data'));
        $this->assertGreaterThanOrEqual(1, count($rows));
    }
}
