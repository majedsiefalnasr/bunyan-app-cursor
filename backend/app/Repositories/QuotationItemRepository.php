<?php

namespace App\Repositories;

use App\Models\QuotationItem;
use Illuminate\Database\Eloquent\Collection;

class QuotationItemRepository extends BaseRepository
{
    protected function model(): string
    {
        return QuotationItem::class;
    }

    /**
     * @return Collection<int, QuotationItem>
     */
    public function byQuotationId(int $quotationId): Collection
    {
        /** @var Collection<int, QuotationItem> */
        return $this->newQuery()
            ->where('quotation_id', $quotationId)
            ->with(['rfqItem'])
            ->get();
    }

    /**
     * @return Collection<int, QuotationItem>
     */
    public function byQuotationIds(array $quotationIds): Collection
    {
        /** @var Collection<int, QuotationItem> */
        return $this->newQuery()
            ->whereIn('quotation_id', $quotationIds)
            ->get();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function replaceForQuotation(int $quotationId, array $rows): void
    {
        $this->newQuery()->where('quotation_id', $quotationId)->delete();

        if ($rows === []) {
            return;
        }

        $this->newQuery()->insert($rows);
    }
}
