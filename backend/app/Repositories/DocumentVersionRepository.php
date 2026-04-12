<?php

namespace App\Repositories;

use App\Models\Document;
use App\Models\DocumentVersion;

class DocumentVersionRepository
{
    public function createForDocument(Document $document, array $attributes): DocumentVersion
    {
        return $document->versions()->create($attributes);
    }

    /**
     * @return list<DocumentVersion>
     */
    public function listForDocument(Document $document): array
    {
        return $document->versions()
            ->with('uploadedBy')
            ->orderByDesc('version')
            ->get()
            ->all();
    }
}
