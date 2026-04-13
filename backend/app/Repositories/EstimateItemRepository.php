<?php

namespace App\Repositories;

use App\Models\Estimate;
use App\Models\EstimateItem;
use Illuminate\Database\Eloquent\Collection;

class EstimateItemRepository
{
    /**
     * @return Collection<int, EstimateItem>
     */
    public function forEstimateOrdered(Estimate $estimate)
    {
        return EstimateItem::query()
            ->where('estimate_id', $estimate->id)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): EstimateItem
    {
        return EstimateItem::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(EstimateItem $item, array $data): EstimateItem
    {
        $item->update($data);

        return $item->fresh() ?? $item;
    }

    public function delete(EstimateItem $item): void
    {
        $item->delete();
    }

    public function countForEstimate(Estimate $estimate): int
    {
        return EstimateItem::query()->where('estimate_id', $estimate->id)->count();
    }
}
