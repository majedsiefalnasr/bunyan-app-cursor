<?php

namespace App\Models;

use App\Enums\DocumentCategory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'documentable_type',
        'documentable_id',
        'category',
        'title',
        'original_filename',
        'storage_path',
        'mime_type',
        'size_bytes',
        'version',
        'uploaded_by',
    ];

    protected $casts = [
        'category' => DocumentCategory::class,
        'size_bytes' => 'integer',
        'version' => 'integer',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DocumentVersion::class)->orderByDesc('version');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
