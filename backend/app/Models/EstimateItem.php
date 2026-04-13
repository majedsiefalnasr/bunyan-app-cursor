<?php

namespace App\Models;

use App\Enums\EstimateItemCategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstimateItem extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'estimate_id',
        'product_id',
        'description_ar',
        'description_en',
        'category',
        'quantity',
        'unit',
        'unit_price',
        'total_price',
        'sort_order',
    ];

    protected $casts = [
        'category' => EstimateItemCategory::class,
        'quantity' => 'decimal:4',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    public function estimate(): BelongsTo
    {
        return $this->belongsTo(Estimate::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
