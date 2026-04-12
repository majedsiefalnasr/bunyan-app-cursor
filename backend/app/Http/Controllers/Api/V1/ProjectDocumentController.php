<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\DocumentCategory;
use App\Http\Requests\Api\V1\IndexProjectDocumentsRequest;
use App\Http\Requests\Api\V1\StoreProjectDocumentRequest;
use App\Http\Resources\Api\V1\DocumentResource;
use App\Models\Project;
use App\Repositories\DocumentRepository;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;

class ProjectDocumentController extends BaseController
{
    public function __construct(
        private DocumentRepository $documentRepository,
        private DocumentService $documentService,
    ) {
    }

    public function index(IndexProjectDocumentsRequest $request, Project $project): JsonResponse
    {
        $validated = $request->validated();
        $perPage = (int) ($validated['per_page'] ?? 15);
        $categoryRaw = data_get($validated, 'filter.category');
        $category = $categoryRaw !== null ? DocumentCategory::tryFrom((string) $categoryRaw) : null;

        $paginator = $this->documentRepository->paginateForProject($project, $perPage, $category);

        return $this->sendSuccess(
            DocumentResource::collection($paginator),
            'تم جلب المستندات بنجاح',
            200,
        );
    }

    public function store(StoreProjectDocumentRequest $request, Project $project): JsonResponse
    {
        $file = $request->file('file');
        if ($file === null) {
            return $this->validationError(['file' => ['الملف مطلوب']]);
        }

        $validated = $request->validated();
        $category = DocumentCategory::from((string) $validated['category']);
        $documentId = isset($validated['document_id']) ? (int) $validated['document_id'] : null;

        $document = $this->documentService->storeUpload(
            $request->user(),
            $project,
            $file,
            (string) $validated['title'],
            $category,
            $documentId,
        );

        return $this->sendSuccess(
            new DocumentResource($document),
            'تم رفع المستند بنجاح',
            201,
        );
    }
}
