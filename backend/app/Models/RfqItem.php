<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfqItem extends BaseModel
{
    protected $fillable = [
        'rfq_id',
        'product_id',
        'description',
        'quantity',
        'unit',
        'specifications',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
        'specifications' => 'array',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function quotationItems(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }
}
