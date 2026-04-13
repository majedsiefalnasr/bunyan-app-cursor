<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Api\V1\BaseController;
use App\Http\Requests\Api\V1\StoreBoqTemplateRequest;
use App\Http\Requests\Api\V1\UpdateBoqTemplateRequest;
use App\Http\Resources\Api\V1\BoqTemplateResource;
use App\Models\BoqTemplate;
use App\Repositories\BoqTemplateRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoqTemplateController extends BaseController
{
    public function __construct(
        private BoqTemplateRepository $boqTemplateRepository,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', BoqTemplate::class);
        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);
        $paginator = $this->boqTemplateRepository->paginate($perPage);

        return $this->sendSuccess(
            BoqTemplateResource::collection($paginator),
            'تم جلب قوالب BOQ بنجاح',
            200,
        );
    }

    public function store(StoreBoqTemplateRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $template = $this->boqTemplateRepository->create([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'],
            'project_type' => $validated['project_type'] ?? null,
            'items_json' => $validated['items_json'],
            'created_by' => $request->user()->id,
        ]);

        return $this->sendSuccess(
            new BoqTemplateResource($template),
            'تم إنشاء القالب بنجاح',
            201,
        );
    }

    public function show(BoqTemplate $boqTemplate): JsonResponse
    {
        $this->authorize('view', $boqTemplate);

        return $this->sendSuccess(
            new BoqTemplateResource($boqTemplate),
            'تم جلب القالب بنجاح',
            200,
        );
    }

    public function update(UpdateBoqTemplateRequest $request, BoqTemplate $boqTemplate): JsonResponse
    {
        $template = $this->boqTemplateRepository->update($boqTemplate, $request->validated());

        return $this->sendSuccess(
            new BoqTemplateResource($template),
            'تم تحديث القالب بنجاح',
            200,
        );
    }

    public function destroy(BoqTemplate $boqTemplate): JsonResponse
    {
        $this->authorize('delete', $boqTemplate);
        $this->boqTemplateRepository->delete($boqTemplate);

        return $this->sendSuccess(
            null,
            'تم حذف القالب بنجاح',
            200,
        );
    }
}
