<?php

namespace App\Repositories;

use App\Models\ProductVariant;

class ProductVariantRepository extends BaseRepository
{
    protected function model(): string
    {
        return ProductVariant::class;
    }

    public function findForProduct(int $productId, int $variantId): ?ProductVariant
    {
        /** @var ProductVariant|null */
        return $this->newQuery()
            ->where('product_id', $productId)
            ->whereKey($variantId)
            ->first();
    }

    public function setStockQuantity(ProductVariant $variant, int $quantity): ProductVariant
    {
        $variant->stock_quantity = max(0, $quantity);
        $variant->save();

        return $variant->fresh() ?? $variant;
    }
}
