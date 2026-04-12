<?php

namespace Tests\Feature\Api\V1;

use App\Enums\SupplierVerificationStatus;
use App\Models\Product;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_index_lists_only_verified_suppliers(): void
    {
        SupplierProfile::factory()->count(2)->verified()->create();
        SupplierProfile::factory()->create([
            'verification_status' => SupplierVerificationStatus::Pending->value,
        ]);

        $response = $this->getJson('/api/v1/suppliers');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }

    public function test_public_show_verified_without_auth(): void
    {
        $profile = SupplierProfile::factory()->verified()->create();

        $response = $this->getJson("/api/v1/suppliers/{$profile->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $profile->id);
    }

    public function test_public_show_pending_returns_404_for_guest(): void
    {
        $profile = SupplierProfile::factory()->create([
            'verification_status' => SupplierVerificationStatus::Pending->value,
        ]);

        $response = $this->getJson("/api/v1/suppliers/{$profile->id}");

        $response->assertStatus(404);
    }

    public function test_owner_can_view_pending_profile(): void
    {
        $user = User::factory()->contractor()->create();
        $profile = SupplierProfile::factory()->create([
            'user_id' => $user->id,
            'verification_status' => SupplierVerificationStatus::Pending->value,
        ]);

        $response = $this->actingAs($user)->getJson("/api/v1/suppliers/{$profile->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.verification_status', 'pending');
    }

    public function test_contractor_can_create_supplier_profile(): void
    {
        $user = User::factory()->contractor()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/suppliers', [
            'company_name_ar' => 'شركة مواد البناء',
            'city' => 'الرياض',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.company_name_ar', 'شركة مواد البناء')
            ->assertJsonPath('data.verification_status', 'pending');

        $this->assertDatabaseHas('supplier_profiles', [
            'user_id' => $user->id,
            'company_name_ar' => 'شركة مواد البناء',
        ]);
    }

    public function test_contractor_cannot_create_second_profile(): void
    {
        $user = User::factory()->contractor()->create();
        SupplierProfile::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson('/api/v1/suppliers', [
            'company_name_ar' => 'ملف ثانٍ',
        ]);

        $response->assertStatus(422);
    }

    public function test_customer_cannot_create_supplier_profile(): void
    {
        $user = User::factory()->create(['role' => 'customer']);

        $response = $this->actingAs($user)->postJson('/api/v1/suppliers', [
            'company_name_ar' => 'شركة',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_verify_supplier(): void
    {
        $admin = User::factory()->admin()->create();
        $profile = SupplierProfile::factory()->create([
            'verification_status' => SupplierVerificationStatus::Pending->value,
        ]);

        $response = $this->actingAs($admin)->putJson("/api/v1/suppliers/{$profile->id}/verify", [
            'verification_status' => SupplierVerificationStatus::Verified->value,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.verification_status', 'verified');

        $this->assertNotNull($profile->fresh()->verified_at);
    }

    public function test_admin_can_list_all_suppliers(): void
    {
        $admin = User::factory()->admin()->create();
        SupplierProfile::factory()->count(3)->create();

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/suppliers');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_supplier_products_lists_active_products(): void
    {
        $profile = SupplierProfile::factory()->verified()->create();
        Product::factory()->count(2)->create([
            'supplier_id' => $profile->id,
            'active' => true,
        ]);

        $response = $this->getJson("/api/v1/suppliers/{$profile->id}/products");

        $response->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
