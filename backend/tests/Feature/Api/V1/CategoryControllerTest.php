<?php

namespace Tests\Feature\Api\V1;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_list_categories(): void
    {
        $response = $this->getJson('/api/v1/categories');

        $response->assertStatus(401);
    }

    public function test_authenticated_user_lists_active_categories_only(): void
    {
        $user = User::factory()->customer()->create();
        $active = Category::factory()->create(['name_en' => 'Active', 'is_active' => true, 'sort_order' => 0]);
        Category::factory()->create(['name_en' => 'Hidden', 'is_active' => false, 'sort_order' => 1]);

        $response = $this->actingAs($user)->getJson('/api/v1/categories');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $names = collect($response->json('data'))->pluck('name_en')->all();
        $this->assertContains('Active', $names);
        $this->assertNotContains('Hidden', $names);
    }

    public function test_non_admin_cannot_use_include_inactive(): void
    {
        $user = User::factory()->customer()->create();
        Category::factory()->inactive()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/categories?include_inactive=1');

        $response->assertStatus(403);
    }

    public function test_admin_can_include_inactive_categories(): void
    {
        $admin = User::factory()->admin()->create();
        Category::factory()->create(['name_en' => 'Shown', 'is_active' => true]);
        Category::factory()->inactive()->create(['name_en' => 'Inactive']);

        $response = $this->actingAs($admin)->getJson('/api/v1/categories?include_inactive=1');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name_en')->all();
        $this->assertContains('Inactive', $names);
    }

    public function test_customer_cannot_create_category(): void
    {
        $user = User::factory()->customer()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/categories', [
            'name_ar' => 'تجربة',
            'name_en' => 'Test',
            'slug' => 'test-cat',
        ]);

        $response->assertStatus(403);
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->postJson('/api/v1/categories', [
            'name_ar' => 'تجربة',
            'name_en' => 'Test category',
            'slug' => 'test-category-create',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.slug', 'test-category-create');
    }

    public function test_admin_cannot_delete_category_with_children(): void
    {
        $admin = User::factory()->admin()->create();
        $parent = Category::factory()->create();
        Category::factory()->childOf($parent)->create();

        $response = $this->actingAs($admin)->deleteJson("/api/v1/categories/{$parent->slug}");

        $response->assertStatus(422);
    }

    public function test_admin_can_reorder_siblings(): void
    {
        $admin = User::factory()->admin()->create();
        $a = Category::factory()->create(['name_en' => 'A', 'sort_order' => 0, 'slug' => 'reorder-a']);
        $b = Category::factory()->create(['name_en' => 'B', 'sort_order' => 1, 'slug' => 'reorder-b']);

        $response = $this->actingAs($admin)->putJson("/api/v1/categories/{$b->slug}/reorder", [
            'sort_order' => 0,
        ]);

        $response->assertOk();
        $this->assertSame(0, $b->fresh()->sort_order);
    }
}
