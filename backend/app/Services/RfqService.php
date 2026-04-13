<?php

namespace App\Services;

use App\Enums\ErrorCode;
use App\Enums\RfqStatus;
use App\Models\Rfq;
use App\Models\User;
use App\Repositories\ProductRepository;
use App\Repositories\RfqItemRepository;
use App\Repositories\RfqRepository;
use App\Repositories\RfqTargetRepository;
use App\Repositories\SupplierProfileRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RfqService
{
    public function __construct(
        private readonly RfqRepository $rfqRepository,
        private readonly RfqItemRepository $rfqItemRepository,
        private readonly RfqTargetRepository $rfqTargetRepository,
        private readonly ProductRepository $productRepository,
        private readonly SupplierProfileRepository $supplierProfileRepository,
    ) {
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createDraft(User $user, array $data): Rfq
    {
        return DB::transaction(function () use ($user, $data) {
            $rfq = $this->rfqRepository->createRfq([
                'project_id' => $data['project_id'] ?? null,
                'created_by' => $user->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'status' => RfqStatus::Draft->value,
                'delivery_deadline' => $data['delivery_deadline'] ?? null,
                'response_deadline' => $data['response_deadline'] ?? null,
            ]);

            $this->rfqItemRepository->createManyForRfq($rfq->id, $data['items'] ?? []);

            Log::info('rfq.created', [
                'rfq_id' => $rfq->id,
                'created_by' => $user->id,
            ]);

            return $rfq->fresh(['items']) ?? $rfq;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function send(User $user, Rfq $rfq, array $data = []): Rfq
    {
        if ($rfq->status !== RfqStatus::Draft) {
            throw ValidationException::withMessages([
                'status' => [ErrorCode::WORKFLOW_INVALID_TRANSITION->description()],
            ]);
        }

        $deadline = $data['response_deadline'] ?? $rfq->response_deadline;
        if ($deadline === null) {
            throw ValidationException::withMessages([
                'response_deadline' => ['مهلة الرد مطلوبة قبل الإرسال'],
            ]);
        }

        return DB::transaction(function () use ($rfq, $deadline) {
            $rfq = $this->rfqRepository->updateRfq($rfq, [
                'status' => RfqStatus::Sent->value,
                'response_deadline' => $deadline,
                'sent_at' => now(),
            ]);

            // Snapshot eligible suppliers at send-time.
            $productIds = $this->rfqItemRepository->productIdsForRfq($rfq->id);

            $supplierIds = $productIds !== []
                ? $this->productRepository->supplierIdsForProductIds($productIds)
                : $this->supplierProfileRepository->allVerifiedIds();

            $supplierIds = $this->supplierProfileRepository->verifiedIdsByIds($supplierIds);
            $this->rfqTargetRepository->createTargets($rfq->id, $supplierIds);

            // Move immediately to QUOTING once targets are snapshot.
            $rfq = $this->rfqRepository->updateRfq($rfq, [
                'status' => RfqStatus::Quoting->value,
            ]);

            Log::info('rfq.sent', [
                'rfq_id' => $rfq->id,
                'targets_count' => count($supplierIds),
            ]);

            return $rfq->fresh(['items', 'targets']) ?? $rfq;
        });
    }

    public function beginEvaluation(User $user, Rfq $rfq): Rfq
    {
        if ($rfq->status !== RfqStatus::Quoting) {
            throw ValidationException::withMessages([
                'status' => [ErrorCode::WORKFLOW_INVALID_TRANSITION->description()],
            ]);
        }

        $rfq = $this->rfqRepository->updateRfq($rfq, [
            'status' => RfqStatus::Evaluation->value,
        ]);

        Log::info('rfq.evaluation_started', ['rfq_id' => $rfq->id]);

        return $rfq->fresh(['items']) ?? $rfq;
    }

    public function close(User $user, Rfq $rfq): Rfq
    {
        if ($rfq->status !== RfqStatus::Awarded) {
            throw ValidationException::withMessages([
                'status' => [ErrorCode::WORKFLOW_INVALID_TRANSITION->description()],
            ]);
        }

        $rfq = $this->rfqRepository->updateRfq($rfq, [
            'status' => RfqStatus::Closed->value,
            'closed_at' => now(),
        ]);

        Log::info('rfq.closed', ['rfq_id' => $rfq->id]);

        return $rfq;
    }
}
