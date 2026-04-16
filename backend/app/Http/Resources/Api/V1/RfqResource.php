<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RfqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = $this->status;

        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'created_by' => $this->created_by,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $status instanceof \BackedEnum ? $status->value : $status,
            'status_label' => method_exists($this->status, 'label') ? $this->status->label() : null,
            'delivery_deadline' => $this->delivery_deadline?->toDateString(),
            'response_deadline' => $this->response_deadline?->toIso8601String(),
            'sent_at' => $this->sent_at?->toIso8601String(),
            'awarded_quotation_id' => $this->awarded_quotation_id,
            'awarded_by' => $this->awarded_by,
            'awarded_at' => $this->awarded_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'items' => RfqItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
