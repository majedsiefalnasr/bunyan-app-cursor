<?php

namespace App\Repositories;

use App\Enums\DocumentCategory;
use App\Models\Document;
use App\Models\Project;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DocumentRepository
{
    public function paginateForProject(Project $project, int $perPage, ?DocumentCategory $category): LengthAwarePaginator
    {
        return Document::query()
            ->with('uploadedBy')
            ->where('documentable_type', Project::class)
            ->where('documentable_id', $project->id)
            ->when($category !== null, fn ($q) => $q->where('category', $category->value))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function findForProjectOrFail(Project $project, int $documentId): Document
    {
        return Document::query()
            ->whereKey($documentId)
            ->where('documentable_type', Project::class)
            ->where('documentable_id', $project->id)
            ->firstOrFail();
    }

    public function create(array $attributes): Document
    {
        return Document::query()->create($attributes);
    }

    public function save(Document $document): void
    {
        $document->save();
    }
}
