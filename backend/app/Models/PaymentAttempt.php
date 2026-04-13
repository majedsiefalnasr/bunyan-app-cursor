<?php

namespace App\Models;

use App\Enums\PaymentAttemptStatus;
use App\Enums\PaymentAttemptType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentAttempt extends BaseModel
{
    protected $fillable = [
        'payment_id',
        'type',
        'amount',
        'status',
        'gateway_id',
        'gateway_response',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'type' => PaymentAttemptType::class,
        'status' => PaymentAttemptStatus::class,
        'gateway_response' => 'array',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
