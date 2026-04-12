<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $projectId = null;
        if ($this->documentable_type === Project::class) {
            $projectId = $this->documentable_id;
        }

        return [
            'id' => $this->id,
            'project_id' => $projectId,
            'title' => $this->title,
            'category' => $this->category->value,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'size_bytes' => $this->size_bytes,
            'version' => $this->version,
            'uploaded_by' => $this->uploaded_by,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
