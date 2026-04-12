<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\MarkConversationReadRequest;
use App\Http\Requests\Api\V1\StoreConversationRequest;
use App\Http\Resources\Api\V1\ConversationResource;
use App\Models\Conversation;
use App\Services\ConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationController extends BaseController
{
    public function __construct(private ConversationService $conversationService)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Conversation::class);

        $paginator = $this->conversationService->paginateForUser(
            $request->user(),
            (int) ($request->per_page ?? 15),
        );

        return $this->sendSuccess(
            ConversationResource::collection($paginator),
            'تم جلب المحادثات بنجاح',
            200,
        );
    }

    public function store(StoreConversationRequest $request): JsonResponse
    {
        $conversation = $this->conversationService->create(
            $request->user(),
            $request->validated(),
        );

        return $this->sendSuccess(
            new ConversationResource($conversation),
            'تم إنشاء المحادثة بنجاح',
            201,
        );
    }

    public function markRead(MarkConversationReadRequest $request, Conversation $conversation): JsonResponse
    {
        $this->conversationService->markRead($request->user(), $conversation);

        return $this->sendSuccess(null, 'تم تحديث حالة القراءة', 200);
    }
}
