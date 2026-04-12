<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Category $model */
        $model = $this->resource;

        return [
            'id' => $model->id,
            'parent_id' => $model->parent_id,
            'name_ar' => $model->name_ar,
            'name_en' => $model->name_en,
            'slug' => $model->slug,
            'icon' => $model->icon,
            'sort_order' => $model->sort_order,
            'is_active' => $model->is_active,
            'children' => $this->when(
                $model->relationLoaded('children'),
                CategoryResource::collection($model->children),
            ),
            'created_at' => $model->created_at?->toIso8601String(),
            'updated_at' => $model->updated_at?->toIso8601String(),
        ];
    }
}
