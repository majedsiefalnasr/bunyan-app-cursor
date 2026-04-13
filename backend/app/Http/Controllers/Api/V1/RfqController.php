<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\BeginEvaluationRfqRequest;
use App\Http\Requests\Api\V1\CloseRfqRequest;
use App\Http\Requests\Api\V1\SendRfqRequest;
use App\Http\Requests\Api\V1\StoreRfqRequest;
use App\Http\Resources\Api\V1\QuotationResource;
use App\Http\Resources\Api\V1\RfqItemResource;
use App\Http\Resources\Api\V1\RfqResource;
use App\Models\Rfq;
use App\Repositories\RfqRepository;
use App\Services\QuotationService;
use App\Services\RfqService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RfqController extends BaseController
{
    public function __construct(
        private readonly RfqService $rfqService,
        private readonly QuotationService $quotationService,
        private readonly RfqRepository $rfqRepository,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Rfq::class);

        $rfqs = $this->rfqRepository->listVisibleTo(
            $request->user(),
            $request->only(['status', 'per_page', 'sort']),
        );

        return $this->sendSuccess(
            RfqResource::collection($rfqs),
            'تم جلب طلبات التسعير بنجاح',
            200,
        );
    }

    public function store(StoreRfqRequest $request): JsonResponse
    {
        $rfq = $this->rfqService->createDraft($request->user(), $request->validated());

        return $this->sendSuccess(
            new RfqResource($rfq->load('items')),
            'تم إنشاء طلب التسعير بنجاح',
            201,
        );
    }

    public function show(Request $request, Rfq $rfq): JsonResponse
    {
        $this->authorize('view', $rfq);
        $rfq->load(['items']);

        return $this->sendSuccess(
            new RfqResource($rfq),
            'تم جلب طلب التسعير بنجاح',
            200,
        );
    }

    public function send(SendRfqRequest $request, Rfq $rfq): JsonResponse
    {
        try {
            $rfq = $this->rfqService->send($request->user(), $rfq, $request->validated());
        } catch (ValidationException $e) {
            return $this->sendError(
                ErrorCode::WORKFLOW_INVALID_TRANSITION->value,
                'تعذر إرسال طلب التسعير',
                $e->errors(),
                422,
            );
        }

        return $this->sendSuccess(
            new RfqResource($rfq->load('items')),
            'تم إرسال طلب التسعير بنجاح',
            200,
        );
    }

    public function close(CloseRfqRequest $request, Rfq $rfq): JsonResponse
    {
        try {
            $rfq = $this->rfqService->close($request->user(), $rfq);
        } catch (ValidationException $e) {
            return $this->sendError(
                ErrorCode::WORKFLOW_INVALID_TRANSITION->value,
                'تعذر إغلاق طلب التسعير',
                $e->errors(),
                422,
            );
        }

        return $this->sendSuccess(
            new RfqResource($rfq),
            'تم إغلاق طلب التسعير',
            200,
        );
    }

    public function beginEvaluation(BeginEvaluationRfqRequest $request, Rfq $rfq): JsonResponse
    {
        try {
            $rfq = $this->rfqService->beginEvaluation($request->user(), $rfq);
        } catch (ValidationException $e) {
            return $this->sendError(
                ErrorCode::WORKFLOW_INVALID_TRANSITION->value,
                'تعذر بدء مرحلة التقييم',
                $e->errors(),
                422,
            );
        }

        return $this->sendSuccess(
            new RfqResource($rfq->load('items')),
            'تم بدء مرحلة التقييم',
            200,
        );
    }

    public function compare(Request $request, Rfq $rfq): JsonResponse
    {
        $this->authorize('view', $rfq);

        $rfq->load(['items']);
        $comparison = $this->quotationService->buildComparison($rfq);

        return $this->sendSuccess(
            [
                'items' => RfqItemResource::collection($comparison['items']),
                'quotations' => QuotationResource::collection($comparison['quotations']),
                'cells' => $comparison['cells'],
            ],
            'تم تجهيز المقارنة بنجاح',
            200,
        );
    }
}
