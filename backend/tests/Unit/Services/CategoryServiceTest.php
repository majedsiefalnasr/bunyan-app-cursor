<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class CategoryServiceTest extends TestCase
{
    use RefreshDatabase;

    private CategoryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CategoryService::class);
    }

    public function test_get_tree_for_user_nests_and_sorts_by_sort_order(): void
    {
        $rootB = Category::factory()->create(['parent_id' => null, 'sort_order' => 1, 'is_active' => true]);
        $rootA = Category::factory()->create(['parent_id' => null, 'sort_order' => 0, 'is_active' => true]);
        $childA2 = Category::factory()->create(['parent_id' => $rootA->id, 'sort_order' => 2, 'is_active' => true]);
        $childA1 = Category::factory()->create(['parent_id' => $rootA->id, 'sort_order' => 0, 'is_active' => true]);
        Category::factory()->create(['parent_id' => $rootB->id, 'sort_order' => 0, 'is_active' => true]);

        $tree = $this->service->getTreeForUser(includeInactive: false);

        $this->assertCount(2, $tree);
        $this->assertSame($rootA->id, $tree[0]->id);
        $this->assertSame($rootB->id, $tree[1]->id);

        $this->assertCount(2, $tree[0]->children);
        $this->assertSame($childA1->id, $tree[0]->children[0]->id);
        $this->assertSame($childA2->id, $tree[0]->children[1]->id);
    }

    public function test_create_generates_unique_slug_and_default_sort_order(): void
    {
        $existing = $this->service->create([
            'name_ar' => 'اسمنت',
            'name_en' => 'Cement',
            'slug' => 'cement',
            'parent_id' => null,
        ]);

        $second = $this->service->create([
            'name_ar' => 'اسمنت 2',
            'name_en' => 'Cement',
            'slug' => 'cement',
            'parent_id' => null,
        ]);

        $this->assertSame('cement', $existing->slug);
        $this->assertSame('cement-1', $second->slug);
        $this->assertSame(1, (int) $existing->sort_order);
        $this->assertSame(2, (int) $second->sort_order);
    }

    public function test_update_prevents_creating_cycles(): void
    {
        $root = $this->service->create([
            'name_ar' => 'جذر',
            'name_en' => 'Root',
            'slug' => 'root',
            'parent_id' => null,
        ]);
        $child = $this->service->create([
            'name_ar' => 'ابن',
            'name_en' => 'Child',
            'slug' => 'child',
            'parent_id' => $root->id,
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->update($root, ['parent_id' => $child->id]);
    }

    public function test_delete_rejects_category_with_children(): void
    {
        $root = Category::factory()->create();
        Category::factory()->create(['parent_id' => $root->id]);

        $this->expectException(InvalidArgumentException::class);
        $this->service->delete($root);
    }

    public function test_reorder_among_siblings_reorders_sort_order(): void
    {
        $parent = Category::factory()->create(['parent_id' => null]);
        $a = Category::factory()->create(['parent_id' => $parent->id, 'sort_order' => 0]);
        $b = Category::factory()->create(['parent_id' => $parent->id, 'sort_order' => 1]);
        $c = Category::factory()->create(['parent_id' => $parent->id, 'sort_order' => 2]);

        $this->service->reorderAmongSiblings($c, 0);

        $a = $a->fresh();
        $b = $b->fresh();
        $c = $c->fresh();

        $this->assertSame(1, (int) $a->sort_order);
        $this->assertSame(2, (int) $b->sort_order);
        $this->assertSame(0, (int) $c->sort_order);
    }
}
