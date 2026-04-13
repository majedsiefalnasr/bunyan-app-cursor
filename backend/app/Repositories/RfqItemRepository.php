<?php

namespace App\Repositories;

use App\Models\RfqItem;
use Illuminate\Database\Eloquent\Collection;

class RfqItemRepository extends BaseRepository
{
    protected function model(): string
    {
        return RfqItem::class;
    }

    /**
     * @return Collection<int, RfqItem>
     */
    public function byRfqId(int $rfqId): Collection
    {
        /** @var Collection<int, RfqItem> */
        return $this->newQuery()
            ->where('rfq_id', $rfqId)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     */
    public function createManyForRfq(int $rfqId, array $items): void
    {
        if ($items === []) {
            return;
        }

        $now = now();
        $rows = array_map(function (array $item) use ($rfqId, $now) {
            return [
                'rfq_id' => $rfqId,
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit' => $item['unit'],
                'specifications' => isset($item['specifications']) ? json_encode($item['specifications']) : null,
                'sort_order' => (int) ($item['sort_order'] ?? 0),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }, $items);

        $this->newQuery()->insert($rows);
    }

    /**
     * @return array<int>
     */
    public function productIdsForRfq(int $rfqId): array
    {
        /** @var array<int> */
        return $this->newQuery()
            ->where('rfq_id', $rfqId)
            ->whereNotNull('product_id')
            ->distinct()
            ->pluck('product_id')
            ->map(fn ($v) => (int) $v)
            ->values()
            ->all();
    }
}
