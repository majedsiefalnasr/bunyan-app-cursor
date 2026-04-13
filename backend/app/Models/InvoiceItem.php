<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends BaseModel
{
    protected $fillable = [
        'invoice_id',
        'description_ar',
        'description_en',
        'quantity',
        'unit_price',
        'vat_rate',
        'line_subtotal',
        'line_vat',
        'line_total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'vat_rate' => 'decimal:2',
        'line_subtotal' => 'decimal:2',
        'line_vat' => 'decimal:2',
        'line_total' => 'decimal:2',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}
