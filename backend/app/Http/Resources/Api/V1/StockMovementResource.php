<?php

namespace App\Http\Resources\Api\V1;

use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var StockMovement $row */
        $row = $this->resource;

        return [
            'id' => $row->id,
            'product_id' => $row->product_id,
            'variant_id' => $row->variant_id,
            'type' => $row->type,
            'quantity' => $row->quantity,
            'reference_type' => $row->reference_type,
            'reference_id' => $row->reference_id,
            'notes' => $row->notes,
            'created_by' => $row->created_by,
            'created_at' => $row->created_at->toIso8601String(),
        ];
    }
}
