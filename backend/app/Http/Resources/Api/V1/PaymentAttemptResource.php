<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentAttemptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type->value,
            'amount' => number_format((float) $this->amount, 2, '.', ''),
            'status' => $this->status->value,
            'gateway_id' => $this->gateway_id,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
