<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'price' => number_format((float) ($this->unit_price ?? $this->price ?? 0), 2, '.', ''),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }
}
