<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Enums\PaymentMethod;
use App\Http\Requests\Api\V1\Payments\InitiatePaymentRequest;
use App\Http\Requests\Api\V1\Payments\RefundPaymentRequest;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Models\Payment;
use App\Repositories\PaymentRepository;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends BaseController
{
    public function __construct(
        private PaymentService $payments,
        private PaymentRepository $paymentRepository,
    ) {
    }

    public function initiate(InitiatePaymentRequest $request): JsonResponse
    {
        try {
            $payment = $this->payments->initiate(
                $request->user(),
                $request->validated('payable_type'),
                (int) $request->validated('payable_id'),
                PaymentMethod::from($request->validated('method')),
            );
        } catch (\InvalidArgumentException $e) {
            return $this->sendError(
                ErrorCode::PAYMENT_FAILED->value,
                $e->getMessage(),
                null,
                ErrorCode::PAYMENT_FAILED->httpStatus(),
            );
        }

        return $this->sendSuccess(
            new PaymentResource($payment),
            'تم بدء الدفع بنجاح',
            201,
        );
    }

    public function history(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Payment::class);

        $payments = $this->paymentRepository
            ->paginateForUser($request->user(), (int) ($request->query('per_page', 15)));

        return $this->sendSuccess(
            PaymentResource::collection($payments),
            'تم جلب المدفوعات بنجاح',
            200,
        );
    }

    public function show(Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);
        $payment->load(['attempts', 'payable']);

        return $this->sendSuccess(
            new PaymentResource($payment),
            'تم جلب الدفع بنجاح',
            200,
        );
    }

    public function capture(Request $request, Payment $payment): JsonResponse
    {
        $this->authorize('view', $payment);

        try {
            $payment = $this->payments->capture($request->user(), $payment);
        } catch (\InvalidArgumentException $e) {
            return $this->sendError(
                ErrorCode::PAYMENT_FAILED->value,
                $e->getMessage(),
                null,
                ErrorCode::PAYMENT_FAILED->httpStatus(),
            );
        }

        return $this->sendSuccess(
            new PaymentResource($payment),
            'تم تأكيد الدفع بنجاح',
            200,
        );
    }

    public function refund(RefundPaymentRequest $request, Payment $payment): JsonResponse
    {
        try {
            $payment = $this->payments->refund(
                $request->user(),
                $payment,
                $request->validated('amount') !== null ? (string) $request->validated('amount') : null,
            );
        } catch (\InvalidArgumentException $e) {
            return $this->sendError(
                ErrorCode::PAYMENT_FAILED->value,
                $e->getMessage(),
                null,
                ErrorCode::PAYMENT_FAILED->httpStatus(),
            );
        }

        return $this->sendSuccess(
            new PaymentResource($payment),
            'تم استرداد المبلغ بنجاح',
            200,
        );
    }
}
