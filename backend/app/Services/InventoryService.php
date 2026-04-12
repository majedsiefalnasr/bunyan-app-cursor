<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Repositories\InventoryRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ProductVariantRepository;
use App\Repositories\StockMovementRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function __construct(
        private InventoryRepository $inventories,
        private StockMovementRepository $movements,
        private ProductRepository $products,
        private ProductVariantRepository $variants,
    ) {
    }

    public function paginateForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        return $this->inventories->paginateForUser($user, $filters);
    }

    public function lowStockForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $filters['low_stock'] = true;

        return $this->inventories->paginateForUser($user, $filters);
    }

    public function paginateMovements(Product $product, array $filters = []): LengthAwarePaginator
    {
        return $this->movements->paginateForProduct((int) $product->getKey(), $filters);
    }

    /**
     * @param  array{quantity_delta: int, variant_id?: int|null, warehouse_location?: string, notes?: string|null}  $payload
     */
    public function adjust(Product $product, User $user, array $payload): Inventory
    {
        $delta = (int) $payload['quantity_delta'];
        $variantId = isset($payload['variant_id']) ? (int) $payload['variant_id'] : null;
        $warehouse = (string) ($payload['warehouse_location'] ?? 'default');
        $notes = $payload['notes'] ?? null;

        if ($variantId !== null) {
            $variant = $this->variants->findForProduct((int) $product->getKey(), $variantId);
            if ($variant === null) {
                throw ValidationException::withMessages([
                    'variant_id' => ['تعذر العثور على المتغير لهذا المنتج'],
                ]);
            }
        }

        return DB::transaction(function () use ($product, $user, $delta, $variantId, $warehouse, $notes) {
            $this->inventories->lockProductForUpdate($product);

            $line = $this->inventories->findLineForUpdate((int) $product->getKey(), $variantId, $warehouse);
            if ($line === null) {
                $line = $this->inventories->createLine([
                    'product_id' => $product->id,
                    'variant_id' => $variantId,
                    'warehouse_location' => $warehouse,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                    'min_quantity' => 0,
                ]);
                $line = $this->inventories->findLineForUpdate((int) $product->getKey(), $variantId, $warehouse);
            }

            if ($line === null) {
                throw ValidationException::withMessages([
                    'warehouse_location' => ['تعذر إنشاء سجل المخزون'],
                ]);
            }

            $newQty = (int) $line->quantity + $delta;
            if ($newQty < 0) {
                throw ValidationException::withMessages([
                    'quantity_delta' => ['المخزون غير كافٍ لهذا التعديل'],
                ]);
            }
            if ($newQty < (int) $line->reserved_quantity) {
                throw ValidationException::withMessages([
                    'quantity_delta' => ['لا يمكن أن يقل المخزون عن الكمية المحجوزة'],
                ]);
            }

            $line->quantity = $newQty;
            $line->save();

            $this->movements->record([
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'type' => 'adjust',
                'quantity' => $delta,
                'reference_type' => null,
                'reference_id' => null,
                'notes' => $notes,
                'created_by' => $user->id,
            ]);

            if ($variantId === null) {
                $this->products->adjustQuantityInStock((int) $product->getKey(), $delta);
            } else {
                $variant = $this->variants->findForProduct((int) $product->getKey(), $variantId);
                if ($variant !== null) {
                    $this->variants->setStockQuantity($variant, (int) $variant->stock_quantity + $delta);
                }
            }

            Log::info('inventory.adjust', [
                'action' => 'inventory.adjust',
                'product_id' => $product->id,
                'variant_id' => $variantId,
                'warehouse_location' => $warehouse,
                'quantity_delta' => $delta,
                'user_id' => $user->id,
            ]);

            return $line->fresh(['product', 'variant']) ?? $line;
        });
    }

    public function logLowStockSnapshot(): int
    {
        $count = $this->inventories->countLowStockLines();

        Log::info('inventory.low_stock_scan', [
            'action' => 'inventory.low_stock_scan',
            'low_stock_rows' => $count,
        ]);

        return (int) $count;
    }
}
