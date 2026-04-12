<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\UserRole;
use App\Http\Requests\Api\V1\ReorderCategoryRequest;
use App\Http\Requests\Api\V1\StoreCategoryRequest;
use App\Http\Requests\Api\V1\UpdateCategoryRequest;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class CategoryController extends BaseController
{
    public function __construct(private CategoryService $categories)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Category::class);

        $includeInactive = $request->boolean('include_inactive');
        if ($includeInactive && $request->user()->role !== UserRole::Admin) {
            return $this->forbidden();
        }

        $tree = $this->categories->getTreeForUser($includeInactive);

        return $this->sendSuccess(
            CategoryResource::collection($tree)->resolve(),
            'تم جلب التصنيفات بنجاح',
            200,
        );
    }

    public function show(Category $category): JsonResponse
    {
        $this->authorize('view', $category);

        $category->load(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')]);

        return $this->sendSuccess(
            new CategoryResource($category),
            'تم جلب التصنيف بنجاح',
            200,
        );
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', Category::class);

        $category = $this->categories->create($request->validated());
        $category->load(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')]);

        return $this->sendSuccess(
            new CategoryResource($category),
            'تم إنشاء التصنيف بنجاح',
            201,
        );
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        try {
            $category = $this->categories->update($category, $request->validated());
        } catch (InvalidArgumentException $e) {
            return $this->validationError(['parent_id' => [$e->getMessage()]]);
        }

        $category->load(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')]);

        return $this->sendSuccess(
            new CategoryResource($category),
            'تم تحديث التصنيف بنجاح',
            200,
        );
    }

    public function destroy(Category $category): JsonResponse
    {
        $this->authorize('delete', $category);

        try {
            $this->categories->delete($category);
        } catch (InvalidArgumentException $e) {
            return $this->validationError(['category' => [$e->getMessage()]]);
        }

        return $this->sendSuccess(null, 'تم حذف التصنيف بنجاح', 200);
    }

    public function reorder(ReorderCategoryRequest $request, Category $category): JsonResponse
    {
        $this->authorize('update', $category);

        try {
            $category = $this->categories->reorderAmongSiblings(
                $category,
                (int) $request->validated('sort_order'),
            );
        } catch (InvalidArgumentException $e) {
            return $this->validationError(['sort_order' => [$e->getMessage()]]);
        }

        $category->load(['children' => fn ($q) => $q->orderBy('sort_order')->orderBy('id')]);

        return $this->sendSuccess(
            new CategoryResource($category),
            'تم إعادة ترتيب التصنيف بنجاح',
            200,
        );
    }
}
