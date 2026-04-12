<?php

namespace App\Repositories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class CategoryRepository extends BaseRepository
{
    protected function model(): string
    {
        return Category::class;
    }

    /**
     * @return Collection<int, Category>
     */
    public function allFlatForTree(bool $onlyActive): Collection
    {
        $query = $this->newQuery()->orderBy('parent_id')->orderBy('sort_order')->orderBy('id');

        if ($onlyActive) {
            $query->active();
        }

        /** @var Collection<int, Category> */
        return $query->get();
    }

    public function slugExists(string $slug, ?int $exceptId = null): bool
    {
        $query = $this->newQuery()->where('slug', $slug);

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId);
        }

        return $query->exists();
    }

    public function nextSortOrder(?int $parentId): int
    {
        $query = $this->newQuery()->where('parent_id', $parentId);

        $max = (int) $query->max('sort_order');

        return $max + 1;
    }

    public function hasChildren(int $categoryId): bool
    {
        return $this->newQuery()->where('parent_id', $categoryId)->exists();
    }
}
