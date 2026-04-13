<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuotationItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'rfq_item_id' => $this->rfq_item_id,
            'unit_price' => number_format((float) ($this->unit_price ?? 0), 2, '.', ''),
            'total_price' => number_format((float) ($this->total_price ?? 0), 2, '.', ''),
            'notes' => $this->notes,
        ];
    }
}
