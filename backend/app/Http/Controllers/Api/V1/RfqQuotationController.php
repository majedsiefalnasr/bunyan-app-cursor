<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Requests\Api\V1\AcceptQuotationRequest;
use App\Http\Requests\Api\V1\StoreQuotationRequest;
use App\Http\Resources\Api\V1\QuotationResource;
use App\Http\Resources\Api\V1\RfqResource;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Repositories\QuotationRepository;
use App\Services\QuotationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RfqQuotationController extends BaseController
{
    public function __construct(
        private readonly QuotationRepository $quotationRepository,
        private readonly QuotationService $quotationService,
    ) {
    }

    public function index(Request $request, Rfq $rfq): JsonResponse
    {
        $this->authorize('view', $rfq);

        $quotations = $this->quotationRepository->listForRfqVisibleTo(
            $request->user(),
            $rfq->id,
            $request->only(['page', 'per_page']),
        );

        return $this->sendSuccess(
            QuotationResource::collection($quotations),
            'تم جلب عروض الأسعار بنجاح',
            200,
        );
    }

    public function store(StoreQuotationRequest $request, Rfq $rfq): JsonResponse
    {
        try {
            $quotation = $this->quotationService->upsertForRfq($request->user(), $rfq, $request->validated());
        } catch (ValidationException $e) {
            return $this->sendError(
                ErrorCode::WORKFLOW_INVALID_TRANSITION->value,
                'تعذر تقديم عرض السعر',
                $e->errors(),
                422,
            );
        }

        return $this->sendSuccess(
            new QuotationResource($quotation),
            'تم حفظ عرض السعر بنجاح',
            201,
        );
    }

    public function accept(AcceptQuotationRequest $request, Rfq $rfq, Quotation $quotation): JsonResponse
    {
        try {
            $rfq = $this->quotationService->accept($request->user(), $rfq, $quotation);
        } catch (ValidationException $e) {
            return $this->sendError(
                ErrorCode::WORKFLOW_INVALID_TRANSITION->value,
                'تعذر إرساء طلب التسعير',
                $e->errors(),
                422,
            );
        }

        return $this->sendSuccess(
            new RfqResource($rfq),
            'تم إرساء طلب التسعير بنجاح',
            200,
        );
    }
}
