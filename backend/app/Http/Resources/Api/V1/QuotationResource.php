<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rfq_id' => $this->rfq_id,
            'supplier_id' => $this->supplier_id,
            'status' => $this->status?->value ?? $this->status,
            'status_label' => method_exists($this->status, 'label') ? $this->status->label() : null,
            'total_price' => number_format((float) ($this->total_price ?? 0), 2, '.', ''),
            'delivery_days' => $this->delivery_days,
            'notes' => $this->notes,
            'valid_until' => $this->valid_until?->toDateString(),
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'supplier_profile' => new SupplierProfileResource($this->whenLoaded('supplierProfile')),
            'items' => QuotationItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
