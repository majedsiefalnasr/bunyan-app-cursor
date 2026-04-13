<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BoqTemplate extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'name_ar',
        'name_en',
        'project_type',
        'items_json',
        'created_by',
    ];

    protected $casts = [
        'items_json' => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
