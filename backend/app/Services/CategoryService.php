<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CategoryService
{
    public function __construct(private CategoryRepository $categories)
    {
    }

    /**
     * @return EloquentCollection<int, Category>
     */
    public function getTreeForUser(bool $includeInactive): EloquentCollection
    {
        $onlyActive = ! $includeInactive;

        $rows = $this->categories->allFlatForTree($onlyActive);

        return $this->nestCategories($rows);
    }

    /**
     * @param  EloquentCollection<int, Category>  $rows
     * @return EloquentCollection<int, Category>
     */
    private function nestCategories(EloquentCollection $rows): EloquentCollection
    {
        $byId = $rows->keyBy('id');

        foreach ($rows as $row) {
            $row->unsetRelation('children');
            $row->setRelation('children', new EloquentCollection);
        }

        $roots = new EloquentCollection;

        foreach ($rows as $row) {
            if ($row->parent_id === null) {
                $roots->push($row);
            } else {
                $parent = $byId->get($row->parent_id);
                if ($parent instanceof Category) {
                    $parent->children->push($row);
                }
            }
        }

        return $this->sortTree($roots);
    }

    /**
     * @param  EloquentCollection<int, Category>  $nodes
     * @return EloquentCollection<int, Category>
     */
    private function sortTree(EloquentCollection $nodes): EloquentCollection
    {
        return $nodes->sortBy('sort_order')->values()->map(function (Category $node) {
            $node->setRelation('children', $this->sortTree($node->children));

            return $node;
        });
    }

    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            $slugBase = $data['slug'] ?? Str::slug($data['name_en']);
            if ($slugBase === '') {
                $slugBase = 'category';
            }
            $slug = $this->uniqueSlug($slugBase);

            $parentId = $data['parent_id'] ?? null;
            $sortOrder = $data['sort_order'] ?? $this->categories->nextSortOrder($parentId);

            /** @var Category $category */
            $category = $this->categories->create([
                'parent_id' => $parentId,
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'],
                'slug' => $slug,
                'icon' => $data['icon'] ?? null,
                'sort_order' => $sortOrder,
                'is_active' => $data['is_active'] ?? true,
            ]);

            return $category;
        });
    }

    public function update(Category $category, array $data): Category
    {
        return DB::transaction(function () use ($category, $data) {
            $parentId = array_key_exists('parent_id', $data) ? $data['parent_id'] : $category->parent_id;

            if ($this->wouldCreateCycle($category->id, $parentId)) {
                throw new InvalidArgumentException('لا يمكن تعيين أصل يشكل حلقة في شجرة التصنيفات.');
            }

            $slug = $category->slug;
            if (isset($data['slug']) && $data['slug'] !== $category->slug) {
                $slugBase = $data['slug'] !== '' ? $data['slug'] : Str::slug($data['name_en'] ?? $category->name_en);
                $slug = $this->uniqueSlug($slugBase, $category->id);
            }

            $payload = [
                'name_ar' => $data['name_ar'] ?? $category->name_ar,
                'name_en' => $data['name_en'] ?? $category->name_en,
                'slug' => $slug,
                'icon' => array_key_exists('icon', $data) ? $data['icon'] : $category->icon,
                'is_active' => $data['is_active'] ?? $category->is_active,
                'parent_id' => $parentId,
            ];

            if (array_key_exists('sort_order', $data)) {
                $payload['sort_order'] = $data['sort_order'];
            }

            $updated = $this->categories->update($category, $payload);
            assert($updated instanceof Category);

            return $updated;
        });
    }

    public function delete(Category $category): void
    {
        if ($this->categories->hasChildren($category->id)) {
            throw new InvalidArgumentException('لا يمكن حذف تصنيف يحتوي على تصنيفات فرعية.');
        }

        $this->categories->delete($category);
    }

    public function reorderAmongSiblings(Category $category, int $newIndex): Category
    {
        return DB::transaction(function () use ($category, $newIndex) {
            /** @var EloquentCollection<int, Category> $siblings */
            $siblings = Category::query()
                ->where('parent_id', $category->parent_id)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();

            if ($newIndex < 0 || $newIndex >= $siblings->count()) {
                throw new InvalidArgumentException('ترتيب غير صالح ضمن التصنيفات الأخوة.');
            }

            $ordered = $siblings->reject(fn (Category $c) => $c->id === $category->id)->values();
            $ordered->splice($newIndex, 0, [$category]);

            foreach ($ordered as $index => $node) {
                $node->update(['sort_order' => $index]);
            }

            return $category->fresh() ?? $category;
        });
    }

    private function uniqueSlug(string $base, ?int $exceptId = null): string
    {
        $slug = Str::slug($base);
        if ($slug === '') {
            $slug = 'category';
        }

        $candidate = $slug;
        $i = 1;
        while ($this->categories->slugExists($candidate, $exceptId)) {
            $candidate = $slug.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    private function wouldCreateCycle(int $categoryId, ?int $newParentId): bool
    {
        if ($newParentId === null) {
            return false;
        }

        if ($newParentId === $categoryId) {
            return true;
        }

        $current = Category::query()->find($newParentId);

        while ($current instanceof Category) {
            if ($current->id === $categoryId) {
                return true;
            }
            $current = $current->parent_id ? Category::query()->find($current->parent_id) : null;
        }

        return false;
    }
}
