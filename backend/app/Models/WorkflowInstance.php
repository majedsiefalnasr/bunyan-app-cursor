<?php

namespace App\Models;

use App\Enums\WorkflowInstanceStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class WorkflowInstance extends BaseModel
{
    protected $fillable = [
        'workflow_configuration_id',
        'workflowable_type',
        'workflowable_id',
        'status',
    ];

    protected $casts = [
        'status' => WorkflowInstanceStatus::class,
    ];

    public function workflowConfiguration(): BelongsTo
    {
        return $this->belongsTo(WorkflowConfiguration::class);
    }

    public function workflowable(): MorphTo
    {
        return $this->morphTo();
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(WorkflowApproval::class);
    }
}
