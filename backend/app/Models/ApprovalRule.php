<?php

namespace App\Models;

use App\Enums\ApprovalStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRule extends BaseModel
{
    protected $fillable = [
        'workflow_configuration_id',
        'entity_type',
        'status_from',
        'status_to',
        'approver_role',
        'approval_count',
        'status',
    ];

    protected $casts = [
        'status' => ApprovalStatus::class,
    ];

    public function workflowConfiguration(): BelongsTo
    {
        return $this->belongsTo(WorkflowConfiguration::class);
    }
}
