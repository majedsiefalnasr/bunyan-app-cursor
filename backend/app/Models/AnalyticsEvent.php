<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AnalyticsEvent extends BaseModel
{
    protected $table = 'analytics_events';

    protected $fillable = [
        'event_name',
        'occurred_at',
        'user_id',
        'role',
        'metadata',
        'session_id',
        'thread_id',
        'request_id',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'metadata' => AsArrayObject::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
