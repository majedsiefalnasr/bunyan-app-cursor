<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductMedia;
use App\Models\ProductVariant;
use App\Repositories\PriceHistoryRepository;
use App\Repositories\ProductRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(
        private ProductRepository $products,
        private PriceHistoryRepository $priceHistories,
    ) {
    }

    public function paginateCatalog(array $filters): LengthAwarePaginator
    {
        return $this->products->allActive($filters);
    }

    public function loadDisplay(Product $product): Product
    {
        return $product->loadMissing(['catalogCategory', 'variants', 'productMedia', 'supplierProfile', 'priceTiers']);
    }

    /**
     * @param  array{name:string,description?:string,category?:string,category_id?:int,price:float|int|string,quantity:int,supplier_id?:int|null}  $data
     */
    public function create(array $data): Product
    {
        $row = [
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sku' => 'SKU-'.strtoupper(Str::random(10)),
            'price' => $data['price'],
            'quantity_in_stock' => $data['quantity'],
            'supplier_id' => $data['supplier_id'] ?? null,
        ];

        if (! empty($data['category_id'])) {
            $row['category_id'] = (int) $data['category_id'];
            $category = Category::query()->find($row['category_id']);
            $row['category'] = $category?->slug ?? ($data['category'] ?? null);
        } else {
            $row['category'] = $data['category'] ?? null;
        }

        /** @var Product $created */
        $created = $this->products->create($row);

        return $created;
    }

    public function update(Product $product, array $data): Product
    {
        $oldPrice = null;
        if (array_key_exists('price', $data)) {
            $oldPrice = number_format((float) $product->price, 2, '.', '');
        }

        if (array_key_exists('quantity', $data)) {
            $data['quantity_in_stock'] = $data['quantity'];
            unset($data['quantity']);
        }

        if (array_key_exists('category_id', $data)) {
            if ($data['category_id'] === null) {
                $data['category'] = null;
            } else {
                $category = Category::query()->find((int) $data['category_id']);
                $data['category'] = $category?->slug;
            }
        }

        /** @var Product $updated */
        $updated = $this->products->update($product, $data);

        if ($oldPrice !== null) {
            $newPrice = number_format((float) $updated->price, 2, '.', '');
            if ($oldPrice !== $newPrice) {
                $this->priceHistories->record(
                    (int) $updated->id,
                    $oldPrice,
                    $newPrice,
                    Auth::id(),
                );
            }
        }

        return $updated;
    }

    public function delete(Product $product): void
    {
        $this->products->delete($product);
    }

    /**
     * @param  array{name:string,sku?:string,price_modifier?:float|int|string,stock_quantity?:int,attributes_json?:array|null,is_active?:bool}  $data
     */
    public function addVariant(Product $product, array $data): ProductVariant
    {
        $sku = $data['sku'] ?? ('VAR-'.strtoupper(Str::random(10)));

        return $product->variants()->create([
            'name' => $data['name'],
            'sku' => $sku,
            'price_modifier' => $data['price_modifier'] ?? 0,
            'stock_quantity' => $data['stock_quantity'] ?? 0,
            'attributes_json' => $data['attributes_json'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);
    }

    /**
     * @param  array{type:string,path:string,sort_order?:int}  $data
     */
    public function addMedia(Product $product, array $data): ProductMedia
    {
        return $product->productMedia()->create([
            'type' => $data['type'],
            'path' => $data['path'],
            'sort_order' => $data['sort_order'] ?? 0,
        ]);
    }
}
