<?php

namespace App\Services;

use App\Enums\ErrorCode;
use App\Enums\QuotationStatus;
use App\Enums\RfqStatus;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\User;
use App\Repositories\QuotationItemRepository;
use App\Repositories\QuotationRepository;
use App\Repositories\RfqItemRepository;
use App\Repositories\RfqRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class QuotationService
{
    public function __construct(
        private readonly QuotationRepository $quotationRepository,
        private readonly QuotationItemRepository $quotationItemRepository,
        private readonly RfqRepository $rfqRepository,
        private readonly RfqItemRepository $rfqItemRepository,
    ) {
    }

    /**
     * Upsert quotation for contractor supplier profile.
     *
     * @param  array<string, mixed>  $data
     */
    public function upsertForRfq(User $user, Rfq $rfq, array $data): Quotation
    {
        if ($rfq->status !== RfqStatus::Quoting) {
            throw ValidationException::withMessages([
                'status' => [ErrorCode::WORKFLOW_INVALID_TRANSITION->description()],
            ]);
        }

        if ($rfq->response_deadline !== null && now()->greaterThanOrEqualTo($rfq->response_deadline)) {
            throw ValidationException::withMessages([
                'response_deadline' => ['انتهت مهلة تقديم عروض الأسعار'],
            ]);
        }

        $supplierId = $user->supplierProfile?->id;
        if ($supplierId === null) {
            throw ValidationException::withMessages([
                'supplier_profile' => ['لا يوجد ملف مورد مرتبط بهذا المستخدم'],
            ]);
        }

        $rfqItems = $this->rfqItemRepository->byRfqId($rfq->id);
        $qtyByItemId = $rfqItems->mapWithKeys(fn ($i) => [$i->id => (float) $i->quantity])->all();

        return DB::transaction(function () use ($rfq, $supplierId, $data, $qtyByItemId) {
            $existing = $this->quotationRepository->findForRfqAndSupplier($rfq->id, $supplierId);
            $status = $existing === null ? QuotationStatus::Submitted : QuotationStatus::Revised;

            $quotation = $existing ?? $this->quotationRepository->create([
                'rfq_id' => $rfq->id,
                'supplier_id' => $supplierId,
                'status' => $status->value,
                'total_price' => 0,
                'submitted_at' => now(),
            ]);

            if (! $quotation instanceof Quotation) {
                throw new \LogicException('Expected Quotation model instance.');
            }

            if ($existing !== null) {
                $quotation = $this->quotationRepository->updateQuotation($quotation, [
                    'status' => $status->value,
                ]);
            }

            $items = $data['items'] ?? [];
            $this->replaceQuotationItems($quotation->id, $items, $qtyByItemId);

            $total = $this->recalculateTotals($quotation->id);
            $quotation = $this->quotationRepository->updateQuotation($quotation, [
                'total_price' => $total,
                'delivery_days' => $data['delivery_days'] ?? null,
                'notes' => $data['notes'] ?? null,
                'valid_until' => $data['valid_until'] ?? null,
                'submitted_at' => $quotation->submitted_at ?? now(),
            ]);

            Log::info('quotation.submitted', [
                'quotation_id' => $quotation->id,
                'rfq_id' => $rfq->id,
                'supplier_id' => $supplierId,
                'status' => $quotation->status,
            ]);

            return $quotation->fresh(['items.rfqItem', 'supplierProfile']) ?? $quotation;
        });
    }

    public function accept(User $user, Rfq $rfq, Quotation $quotation): Rfq
    {
        if ($rfq->status !== RfqStatus::Evaluation) {
            throw ValidationException::withMessages([
                'status' => [ErrorCode::WORKFLOW_INVALID_TRANSITION->description()],
            ]);
        }

        return DB::transaction(function () use ($user, $rfq, $quotation) {
            // Mark accepted + reject others
            $this->quotationRepository->updateQuotation($quotation, [
                'status' => QuotationStatus::Accepted->value,
            ]);

            $this->quotationRepository->rejectAllForRfqExcept($rfq->id, $quotation->id);

            $rfq = $this->rfqRepository->updateRfq($rfq, [
                'status' => RfqStatus::Awarded->value,
                'awarded_quotation_id' => $quotation->id,
                'awarded_by' => $user->id,
                'awarded_at' => now(),
            ]);

            Log::info('quotation.awarded', [
                'rfq_id' => $rfq->id,
                'quotation_id' => $quotation->id,
                'awarded_by' => $user->id,
            ]);

            return $rfq->fresh(['items', 'quotations']) ?? $rfq;
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function buildComparison(Rfq $rfq): array
    {
        $items = $this->rfqItemRepository->byRfqId($rfq->id);
        $quotations = $this->quotationRepository->listTopForCompare($rfq->id, 50);
        $quotationIds = $quotations->pluck('id')->map(fn ($v) => (int) $v)->all();
        $quotationItems = $this->quotationItemRepository->byQuotationIds($quotationIds);

        $cells = [];
        foreach ($quotationItems as $qi) {
            $cells[(string) $qi->quotation_id][(string) $qi->rfq_item_id] = [
                'unit_price' => (float) $qi->unit_price,
                'total_price' => (float) $qi->total_price,
                'notes' => $qi->notes,
            ];
        }

        return [
            'items' => $items,
            'quotations' => $quotations,
            'cells' => $cells,
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<int, float>  $qtyByItemId
     */
    private function replaceQuotationItems(int $quotationId, array $items, array $qtyByItemId): void
    {
        $now = now();

        $rows = [];
        foreach ($items as $item) {
            $rfqItemId = (int) $item['rfq_item_id'];
            $qty = $qtyByItemId[$rfqItemId] ?? null;
            if ($qty === null) {
                throw ValidationException::withMessages([
                    'items' => ['عنصر RFQ غير صالح'],
                ]);
            }

            $unitPrice = (float) $item['unit_price'];
            $rows[] = [
                'quotation_id' => $quotationId,
                'rfq_item_id' => $rfqItemId,
                'unit_price' => $unitPrice,
                'total_price' => round($unitPrice * $qty, 2),
                'notes' => $item['notes'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->quotationItemRepository->replaceForQuotation($quotationId, $rows);
    }

    private function recalculateTotals(int $quotationId): float
    {
        $items = $this->quotationItemRepository->byQuotationId($quotationId);

        return (float) $items->sum('total_price');
    }
}
