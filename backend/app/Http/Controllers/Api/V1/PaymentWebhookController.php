<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ErrorCode;
use App\Http\Resources\Api\V1\PaymentResource;
use App\Services\Payments\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentWebhookController extends BaseController
{
    public function __construct(private PaymentService $payments)
    {
    }

    public function handle(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'gateway_reference' => ['required', 'string', 'max:255'],
            'status' => ['required', 'string', 'in:completed,failed'],
        ]);

        try {
            $payment = $this->payments->applyWebhookPayload($validated);
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
            'تم تحديث حالة الدفع',
            200,
        );
    }
}
