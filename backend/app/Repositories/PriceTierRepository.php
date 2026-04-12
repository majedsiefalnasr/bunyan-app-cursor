<?php

namespace App\Repositories;

use App\Models\PriceTier;
use Illuminate\Support\Facades\DB;

class PriceTierRepository extends BaseRepository
{
    protected function model(): string
    {
        return PriceTier::class;
    }

    /**
     * @param  array<int, array{product_variant_id: int|null, min_quantity: int, max_quantity: int|null, unit_price: string}>  $rows
     */
    public function replaceForProduct(int $productId, array $rows): void
    {
        DB::transaction(function () use ($productId, $rows): void {
            $this->newQuery()->where('product_id', $productId)->delete();

            foreach ($rows as $row) {
                $this->newQuery()->create([
                    'product_id' => $productId,
                    'product_variant_id' => $row['product_variant_id'],
                    'min_quantity' => $row['min_quantity'],
                    'max_quantity' => $row['max_quantity'],
                    'unit_price' => $row['unit_price'],
                ]);
            }
        });
    }
}
