<?php

namespace App\Repositories;

use App\Models\RfqTarget;
use Illuminate\Database\Eloquent\Collection;

class RfqTargetRepository extends BaseRepository
{
    protected function model(): string
    {
        return RfqTarget::class;
    }

    /**
     * @return Collection<int, RfqTarget>
     */
    public function byRfqId(int $rfqId): Collection
    {
        /** @var Collection<int, RfqTarget> */
        return $this->newQuery()
            ->where('rfq_id', $rfqId)
            ->get();
    }

    /**
     * @param  array<int>  $supplierProfileIds
     */
    public function createTargets(int $rfqId, array $supplierProfileIds): void
    {
        if ($supplierProfileIds === []) {
            return;
        }

        $now = now();
        $rows = array_map(fn (int $supplierId) => [
            'rfq_id' => $rfqId,
            'supplier_id' => $supplierId,
            'invited_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ], $supplierProfileIds);

        $this->newQuery()->insertOrIgnore($rows);
    }
}
