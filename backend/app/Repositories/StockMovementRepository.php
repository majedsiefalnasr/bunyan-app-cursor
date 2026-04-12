<?php

namespace App\Repositories;

use App\Models\StockMovement;
use Illuminate\Pagination\LengthAwarePaginator;

class StockMovementRepository extends BaseRepository
{
    protected function model(): string
    {
        return StockMovement::class;
    }

    public function record(array $data): StockMovement
    {
        $data['created_at'] = $data['created_at'] ?? now();

        /** @var StockMovement */
        return $this->newQuery()->create($data);
    }

    public function paginateForProduct(int $productId, array $filters = []): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->with(['creator'])
            ->where('product_id', $productId)
            ->orderByDesc('created_at');

        if (! empty($filters['variant_id'])) {
            $query->where('variant_id', (int) $filters['variant_id']);
        }

        return $query->paginate((int) ($filters['per_page'] ?? 15));
    }
}
