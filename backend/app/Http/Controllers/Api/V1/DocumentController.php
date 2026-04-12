<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\Api\V1\DocumentResource;
use App\Http\Resources\Api\V1\DocumentVersionResource;
use App\Models\Document;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends BaseController
{
    public function __construct(private DocumentService $documentService)
    {
    }

    public function show(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $document->loadMissing('uploadedBy');

        return $this->sendSuccess(
            new DocumentResource($document),
            'تم جلب المستند بنجاح',
            200,
        );
    }

    public function download(Document $document): StreamedResponse|JsonResponse
    {
        $this->authorize('view', $document);

        return $this->documentService->downloadResponse($document);
    }

    public function destroy(Document $document): JsonResponse
    {
        $this->authorize('delete', $document);

        $this->documentService->softDelete($document);

        return $this->sendSuccess(
            null,
            'تم حذف المستند بنجاح',
            200,
        );
    }

    public function versions(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        $rows = $this->documentService->listVersions($document);

        return $this->sendSuccess(
            DocumentVersionResource::collection($rows),
            'تم جلب إصدارات المستند بنجاح',
            200,
        );
    }
}
