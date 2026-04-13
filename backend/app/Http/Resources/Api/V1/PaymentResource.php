<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'payable_type' => $this->payable_type,
            'payable_id' => $this->payable_id,
            'user_id' => $this->user_id,
            'amount' => number_format((float) $this->amount, 2, '.', ''),
            'currency' => $this->currency,
            'method' => $this->method->value,
            'status' => $this->status->value,
            'gateway_reference' => $this->gateway_reference,
            'paid_at' => $this->paid_at?->toIso8601String(),
            'attempts' => PaymentAttemptResource::collection($this->whenLoaded('attempts')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
