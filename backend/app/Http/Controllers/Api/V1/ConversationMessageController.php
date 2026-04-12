<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Api\V1\SendConversationMessageRequest;
use App\Http\Resources\Api\V1\MessageResource;
use App\Models\Conversation;
use App\Repositories\MessageRepository;
use App\Services\MessageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ConversationMessageController extends BaseController
{
    public function __construct(
        private MessageService $messageService,
        private MessageRepository $messages,
    ) {
    }

    public function index(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('view', $conversation);

        $paginator = $this->messages->paginateForConversation(
            $conversation->id,
            (int) ($request->per_page ?? 30),
        );

        return $this->sendSuccess(
            MessageResource::collection($paginator),
            'تم جلب الرسائل بنجاح',
            200,
        );
    }

    public function store(SendConversationMessageRequest $request, Conversation $conversation): JsonResponse
    {
        $message = $this->messageService->send(
            $request->user(),
            $conversation,
            ['body' => $request->input('body')],
            $request->file('attachment'),
        );

        return $this->sendSuccess(
            new MessageResource($message),
            'تم إرسال الرسالة بنجاح',
            201,
        );
    }
}
