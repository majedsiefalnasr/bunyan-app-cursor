<?php

namespace App\Models;

use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Payment extends BaseModel
{
    protected $fillable = [
        'payable_type',
        'payable_id',
        'user_id',
        'amount',
        'currency',
        'method',
        'status',
        'gateway_reference',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'method' => PaymentMethod::class,
        'status' => PaymentStatus::class,
        'paid_at' => 'datetime',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(PaymentAttempt::class);
    }
}
