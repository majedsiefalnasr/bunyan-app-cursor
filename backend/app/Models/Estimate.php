<?php

namespace App\Models;

use App\Enums\EstimateStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Estimate extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'total_materials',
        'total_labor',
        'total_overhead',
        'grand_total',
        'markup_percentage',
        'approved_by',
        'approved_at',
        'created_by',
    ];

    protected $casts = [
        'status' => EstimateStatus::class,
        'total_materials' => 'decimal:2',
        'total_labor' => 'decimal:2',
        'total_overhead' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'markup_percentage' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EstimateItem::class)->orderBy('sort_order')->orderBy('id');
    }
}
