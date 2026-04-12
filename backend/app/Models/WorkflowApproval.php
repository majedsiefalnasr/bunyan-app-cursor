<?php

namespace App\Models;

use App\Enums\WorkflowApprovalAction;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowApproval extends BaseModel
{
    protected $fillable = [
        'workflow_instance_id',
        'approval_rule_id',
        'approver_role',
        'action',
        'notes',
        'acted_by',
        'acted_at',
    ];

    protected $casts = [
        'action' => WorkflowApprovalAction::class,
        'acted_at' => 'datetime',
    ];

    public function workflowInstance(): BelongsTo
    {
        return $this->belongsTo(WorkflowInstance::class);
    }

    public function approvalRule(): BelongsTo
    {
        return $this->belongsTo(ApprovalRule::class);
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }
}
