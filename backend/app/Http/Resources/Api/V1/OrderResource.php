<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'customer_id' => $this->customer_id,
            'supplier_id' => $this->supplier_id,
            'quotation_id' => $this->quotation_id,
            'project_id' => $this->project_id,
            'status' => $this->status,
            'subtotal' => number_format((float) ($this->subtotal ?? 0), 2, '.', ''),
            'tax_amount' => number_format((float) ($this->tax_amount ?? 0), 2, '.', ''),
            'shipping_amount' => number_format((float) ($this->shipping_amount ?? 0), 2, '.', ''),
            'total_price' => number_format((float) ($this->total_amount ?? 0), 2, '.', ''),
            'confirmed_at' => $this->confirmed_at?->toIso8601String(),
            'shipped_at' => $this->shipped_at?->toIso8601String(),
            'delivered_at' => $this->delivered_at?->toIso8601String(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
