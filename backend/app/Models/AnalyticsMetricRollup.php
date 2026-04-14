<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;

final class AnalyticsMetricRollup extends BaseModel
{
    protected $table = 'analytics_metric_rollups';

    protected $fillable = [
        'metric_key',
        'bucket',
        'bucket_start',
        'bucket_end',
        'value',
        'dimensions',
        'dimensions_hash',
        'computed_at',
    ];

    protected $casts = [
        'bucket_start' => 'date',
        'bucket_end' => 'date',
        'computed_at' => 'datetime',
        'dimensions' => AsArrayObject::class,
        'value' => 'decimal:4',
    ];
}
