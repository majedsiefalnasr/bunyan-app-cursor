<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersion extends BaseModel
{
    protected $fillable = [
        'document_id',
        'version',
        'storage_path',
        'size_bytes',
        'uploaded_by',
    ];

    protected $casts = [
        'version' => 'integer',
        'size_bytes' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
