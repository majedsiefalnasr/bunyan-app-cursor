<?php

namespace App\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ProductRepository
{
    public function __construct(
        private readonly Product $model,
    ) {
    }

    public function findById(int $id): ?Product
    {
        return $this->model->find($id);
    }

    public function findByIdOrFail(int $id): Product
    {
        return $this->model->findOrFail($id);
    }

    public function findBySku(string $sku): ?Product
    {
        return $this->model->bySku($sku)->first();
    }

    public function allActive(array $filters = []): LengthAwarePaginator
    {
        return $this->model
            ->active()
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->byCategory($category))
            ->when($filters['in_stock'] ?? null, fn ($q) => $q->inStock())
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function allByCategory(string $category, array $filters = []): Collection
    {
        return $this->model
            ->byCategory($category)
            ->active()
            ->when($filters['in_stock'] ?? null, fn ($q) => $q->inStock())
            ->orderBy('name')
            ->get();
    }

    public function allInStock(): Collection
    {
        return $this->model
            ->active()
            ->inStock()
            ->orderBy('name')
            ->get();
    }

    public function create(array $data): Product
    {
        return $this->model->create($data);
    }

    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }

    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    public function restore(int $productId): bool
    {
        return $this->model->withTrashed()->findOrFail($productId)->restore();
    }
}
