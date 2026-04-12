<?php

namespace App\Repositories;

use App\Models\BoqTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BoqTemplateRepository
{
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return BoqTemplate::query()
            ->with('creator')
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findOrFail(int $id): BoqTemplate
    {
        return BoqTemplate::query()->whereKey($id)->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): BoqTemplate
    {
        return BoqTemplate::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(BoqTemplate $template, array $data): BoqTemplate
    {
        $template->update($data);

        return $template->fresh() ?? $template;
    }

    public function delete(BoqTemplate $template): void
    {
        $template->delete();
    }
}
