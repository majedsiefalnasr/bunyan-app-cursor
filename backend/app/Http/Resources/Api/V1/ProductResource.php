<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Product $product */
        $product = $this->resource;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'category' => $product->category,
            'category_id' => $product->category_id,
            'category_detail' => $this->when(
                $product->relationLoaded('catalogCategory') && $product->catalogCategory !== null,
                fn () => CategoryResource::make($product->catalogCategory)
            ),
            'price' => number_format((float) $product->price, 2, '.', ''),
            'quantity' => $product->quantity_in_stock,
            'supplier_id' => $product->supplier_id,
            'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
            'media' => ProductMediaResource::collection($this->whenLoaded('productMedia')),
            'created_at' => $product->created_at?->toIso8601String(),
            'updated_at' => $product->updated_at?->toIso8601String(),
        ];
    }
}
