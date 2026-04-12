<?php

namespace App\Http\Resources\Api\V1;

use App\Models\PriceTier;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PriceTierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var PriceTier $tier */
        $tier = $this->resource;

        return [
            'id' => $tier->id,
            'product_id' => $tier->product_id,
            'product_variant_id' => $tier->product_variant_id,
            'min_quantity' => $tier->min_quantity,
            'max_quantity' => $tier->max_quantity,
            'unit_price' => number_format((float) $tier->unit_price, 2, '.', ''),
        ];
    }
}
