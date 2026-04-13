<?php

namespace App\Models;

use App\Enums\QuotationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends BaseModel
{
    protected $fillable = [
        'rfq_id',
        'supplier_id',
        'status',
        'total_price',
        'delivery_days',
        'notes',
        'valid_until',
        'submitted_at',
    ];

    protected $casts = [
        'status' => QuotationStatus::class,
        'total_price' => 'decimal:2',
        'valid_until' => 'date',
        'submitted_at' => 'datetime',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function supplierProfile(): BelongsTo
    {
        return $this->belongsTo(SupplierProfile::class, 'supplier_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
