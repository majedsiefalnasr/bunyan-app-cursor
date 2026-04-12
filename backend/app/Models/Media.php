<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'collection',
        'filename',
        'original_filename',
        'mime_type',
        'disk',
        'path',
        'thumb_path',
        'size_bytes',
        'dimensions_json',
        'alt_text_ar',
        'alt_text_en',
        'sort_order',
        'uploaded_by',
        'is_temporary',
    ];

    protected $casts = [
        'dimensions_json' => 'array',
        'is_temporary' => 'boolean',
        'size_bytes' => 'integer',
        'sort_order' => 'integer',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
