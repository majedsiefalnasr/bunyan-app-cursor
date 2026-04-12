<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Inventory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Inventory $row */
        $row = $this->resource;

        return [
            'id' => $row->id,
            'product_id' => $row->product_id,
            'variant_id' => $row->variant_id,
            'warehouse_location' => $row->warehouse_location,
            'quantity' => $row->quantity,
            'reserved_quantity' => $row->reserved_quantity,
            'min_quantity' => $row->min_quantity,
            'available_quantity' => max(0, $row->quantity - $row->reserved_quantity),
            'product' => ProductResource::make($this->whenLoaded('product')),
            'variant' => ProductVariantResource::make($this->whenLoaded('variant')),
            'updated_at' => $row->updated_at?->toIso8601String(),
        ];
    }
}
