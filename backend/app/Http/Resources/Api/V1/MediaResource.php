<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MediaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $disk = $this->disk;

        return [
            'id' => $this->id,
            'collection' => $this->collection,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'url' => Storage::disk($disk)->url($this->path),
            'thumb_url' => $this->thumb_path !== null ? Storage::disk($disk)->url($this->thumb_path) : null,
            'size_bytes' => $this->size_bytes,
            'dimensions' => $this->dimensions_json,
            'alt_text_ar' => $this->alt_text_ar,
            'alt_text_en' => $this->alt_text_en,
            'sort_order' => $this->sort_order,
            'uploaded_by' => $this->uploaded_by,
            'is_temporary' => $this->is_temporary,
            'mediable_type' => $this->mediable_type,
            'mediable_id' => $this->mediable_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
