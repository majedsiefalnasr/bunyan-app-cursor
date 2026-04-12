<?php

namespace App\Repositories;

use App\Models\PriceHistory;

class PriceHistoryRepository
{
    public function record(int $productId, string $oldPrice, string $newPrice, ?int $userId): void
    {
        PriceHistory::query()->create([
            'product_id' => $productId,
            'old_price' => $oldPrice,
            'new_price' => $newPrice,
            'changed_by' => $userId,
            'changed_at' => now(),
        ]);
    }
}
