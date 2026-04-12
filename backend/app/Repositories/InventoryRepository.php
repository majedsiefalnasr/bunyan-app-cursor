<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\SupplierProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class InventoryRepository extends BaseRepository
{
    protected function model(): string
    {
        return Inventory::class;
    }

    public function lockProductForUpdate(Product $product): void
    {
        Product::query()->whereKey($product->getKey())->lockForUpdate()->first();
    }

    public function findLineForUpdate(int $productId, ?int $variantId, string $warehouse): ?Inventory
    {
        /** @var Builder $q */
        $q = $this->newQuery()
            ->where('product_id', $productId)
            ->where('warehouse_location', $warehouse);

        if ($variantId === null) {
            $q->whereNull('variant_id');
        } else {
            $q->where('variant_id', $variantId);
        }

        /** @var Inventory|null */
        return $q->lockForUpdate()->first();
    }

    public function createLine(array $data): Inventory
    {
        /** @var Inventory */
        return $this->newQuery()->create($data);
    }

    public function paginateForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $this->newQuery()->with(['product', 'variant']);

        if ($user->role !== UserRole::Admin) {
            $profile = SupplierProfile::query()->where('user_id', $user->id)->first();
            if ($profile === null) {
                return $query->whereRaw('1 = 0')->paginate((int) ($filters['per_page'] ?? 15));
            }
            $query->whereHas('product', fn (Builder $p) => $p->where('supplier_id', $profile->id));
        }

        if (! empty($filters['product_id'])) {
            $query->where('product_id', (int) $filters['product_id']);
        }

        if (! empty($filters['warehouse_location'])) {
            $query->where('warehouse_location', (string) $filters['warehouse_location']);
        }

        if (! empty($filters['low_stock'])) {
            $query->whereRaw('(quantity - reserved_quantity) <= min_quantity');
        }

        return $query->orderByDesc('updated_at')->paginate((int) ($filters['per_page'] ?? 15));
    }

    public function countLowStockLines(): int
    {
        return (int) $this->newQuery()
            ->whereRaw('(quantity - reserved_quantity) <= min_quantity')
            ->count();
    }
}
