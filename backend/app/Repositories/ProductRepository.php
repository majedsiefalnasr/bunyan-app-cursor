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
        $query = $this->newQuery()
            ->active()
            ->with([
                'catalogCategory',
                // For list views, include only the first media item as a thumbnail.
                'productMedia' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')->limit(1),
            ]);

        if ($filters['category_id'] ?? null) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if ($filters['category'] ?? null) {
            $query->byCategory((string) $filters['category']);
        }

        if ($filters['supplier_id'] ?? null) {
            $query->where('supplier_id', (int) $filters['supplier_id']);
        }

        if ($filters['min_price'] ?? null) {
            $query->where('price', '>=', (float) $filters['min_price']);
        }

        if ($filters['max_price'] ?? null) {
            $query->where('price', '<=', (float) $filters['max_price']);
        }

        if ($filters['search'] ?? null) {
            $search = (string) $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if (! empty($filters['in_stock'])) {
            $query->inStock();
        }

        return $query->orderBy('name')
            ->paginate((int) ($filters['per_page'] ?? 15));
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

    public function adjustQuantityInStock(int $productId, int $delta): void
    {
        /** @var Product $product */
        $product = $this->newQuery()->whereKey($productId)->firstOrFail();
        $new = max(0, (int) $product->quantity_in_stock + $delta);
        $this->newQuery()->whereKey($productId)->update(['quantity_in_stock' => $new]);
    }

    /**
     * @return array<int>
     */
    public function supplierIdsForProductIds(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        /** @var array<int> */
        return $this->newQuery()
            ->whereIn('id', $productIds)
            ->whereNotNull('supplier_id')
            ->distinct()
            ->pluck('supplier_id')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();
    }
}
