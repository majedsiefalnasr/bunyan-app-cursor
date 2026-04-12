<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\IndexMediaRequest;
use App\Http\Requests\Api\V1\StoreMediaUploadRequest;
use App\Http\Resources\Api\V1\MediaResource;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;

class MediaController extends BaseController
{
    public function __construct(private MediaService $mediaService)
    {
    }

    public function store(StoreMediaUploadRequest $request): JsonResponse
    {
        $file = $request->file('file');
        if ($file === null) {
            return $this->validationError(['file' => ['الملف مطلوب']]);
        }

        $meta = $request->safe()->only([
            'collection',
            'alt_text_ar',
            'alt_text_en',
            'mediable_type',
            'mediable_id',
            'is_temporary',
            'sort_order',
        ]);

        $media = $this->mediaService->store($request->user(), $file, $meta);

        return $this->sendSuccess(
            new MediaResource($media),
            'تم رفع الوسائط بنجاح',
            201
        );
    }

    public function index(IndexMediaRequest $request): JsonResponse
    {
        $paginator = $this->mediaService->paginateForActor($request->user(), $request->validated());

        return $this->sendSuccess(
            MediaResource::collection($paginator),
            'تم جلب الوسائط بنجاح',
            200
        );
    }

    public function show(Media $media): JsonResponse
    {
        $this->authorize('view', $media);

        return $this->sendSuccess(
            new MediaResource($media),
            'تم جلب الوسيط بنجاح',
            200
        );
    }

    public function destroy(Media $media): JsonResponse
    {
        $this->authorize('delete', $media);

        $this->mediaService->delete($media);

        return $this->sendSuccess(
            null,
            'تم حذف الوسيط بنجاح',
            200
        );
    }
}
