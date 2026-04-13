<?php

namespace App\Repositories;

use App\Enums\QuotationStatus;
use App\Enums\UserRole;
use App\Models\Quotation;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class QuotationRepository extends BaseRepository
{
    protected function model(): string
    {
        return Quotation::class;
    }

    public function findForRfqAndSupplier(int $rfqId, int $supplierProfileId): ?Quotation
    {
        /** @var ?Quotation */
        return $this->newQuery()
            ->where('rfq_id', $rfqId)
            ->where('supplier_id', $supplierProfileId)
            ->with(['items.rfqItem', 'supplierProfile'])
            ->first();
    }

    public function listForRfqVisibleTo(User $user, int $rfqId, array $filters = []): LengthAwarePaginator
    {
        $perPage = min((int) ($filters['per_page'] ?? 15), 50);

        $query = $this->newQuery()
            ->where('rfq_id', $rfqId)
            ->with(['supplierProfile', 'items.rfqItem'])
            ->orderBy('total_price')
            ->orderBy('submitted_at')
            ->orderBy('id');

        if ($user->role === UserRole::Contractor) {
            $supplierId = $user->supplierProfile?->id ?? 0;
            $query->where('supplier_id', $supplierId);
        }

        return $query->paginate($perPage);
    }

    /**
     * @return Collection<int, Quotation>
     */
    public function listTopForCompare(int $rfqId, int $limit = 50): Collection
    {
        /** @var Collection<int, Quotation> */
        return $this->newQuery()
            ->where('rfq_id', $rfqId)
            ->with(['supplierProfile'])
            ->orderBy('total_price')
            ->orderBy('submitted_at')
            ->orderBy('id')
            ->limit($limit)
            ->get();
    }

    public function updateQuotation(Quotation $quotation, array $data): Quotation
    {
        /** @var Quotation */
        return $this->update($quotation, $data);
    }

    public function rejectAllForRfqExcept(int $rfqId, int $acceptedQuotationId): void
    {
        $this->newQuery()
            ->where('rfq_id', $rfqId)
            ->where('id', '!=', $acceptedQuotationId)
            ->update(['status' => QuotationStatus::Rejected->value]);
    }
}
