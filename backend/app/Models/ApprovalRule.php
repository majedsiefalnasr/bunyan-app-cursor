<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_configuration_id',
        'entity_type',
        'status_from',
        'status_to',
        'approver_role',
        'approval_count',
    ];

    // Relationships
    public function workflowConfiguration(): BelongsTo
    {
        return $this->belongsTo(WorkflowConfiguration::class);
    }
}
