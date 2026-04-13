<?php

namespace App\Http\Resources\Api\V1;

use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin InvoiceItem */
class InvoiceItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'description_ar' => $this->description_ar,
            'description_en' => $this->description_en,
            'quantity' => $this->quantity,
            'unit_price' => (string) $this->unit_price,
            'vat_rate' => (string) $this->vat_rate,
            'line_subtotal' => (string) $this->line_subtotal,
            'line_vat' => (string) $this->line_vat,
            'line_total' => (string) $this->line_total,
        ];
    }
}
