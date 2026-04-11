<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository extends BaseRepository
{
    protected function model(): string
    {
        return Product::class;
    }

    public function findBySku(string $sku): ?Product
    {
        /** @var Product|null */
        return $this->newQuery()->bySku($sku)->first();
    }

    public function allActive(array $filters = []): LengthAwarePaginator
    {
        return $this->newQuery()
            ->active()
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->byCategory($category))
            ->when($filters['in_stock'] ?? null, fn ($q) => $q->inStock())
            ->when(
                $filters['search'] ?? null,
                fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
            )
            ->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 20));
    }

    public function allByCategory(string $category, array $filters = []): Collection
    {
        /** @var Collection<int, Product> */
        return $this->newQuery()
            ->byCategory($category)
            ->active()
            ->when($filters['in_stock'] ?? null, fn ($q) => $q->inStock())
            ->orderBy('name')
            ->get();
    }

    public function allInStock(): Collection
    {
        /** @var Collection<int, Product> */
        return $this->newQuery()
            ->active()
            ->inStock()
            ->orderBy('name')
            ->get();
    }
}
