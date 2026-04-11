<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ErrorLog extends Model
{
    protected $fillable = [
        'correlation_id',
        'error_code',
        'message',
        'details',
        'context',
        'severity',
        'http_status',
        'exception_class',
        'stack_trace',
        'user_id',
        'user_role',
        'request_method',
        'request_path',
        'request_ip',
        'response_time_ms',
    ];

    protected $casts = [
        'details' => 'array',
        'context' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
