<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqTarget extends BaseModel
{
    protected $fillable = [
        'rfq_id',
        'supplier_id',
        'invited_at',
    ];

    protected $casts = [
        'invited_at' => 'datetime',
    ];

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function supplierProfile(): BelongsTo
    {
        return $this->belongsTo(SupplierProfile::class, 'supplier_id');
    }
}
