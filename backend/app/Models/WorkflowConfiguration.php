<?php

namespace App\Models;

use App\Enums\WorkflowType;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowConfiguration extends BaseModel
{
    protected $fillable = [
        'project_id',
        'name',
        'description',
        'type',
        'status_transitions',
        'approval_requirements',
        'is_global',
    ];

    protected $casts = [
        'status_transitions' => 'json',
        'approval_requirements' => 'json',
        'is_global' => 'boolean',
        'type' => WorkflowType::class,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function approvalRules(): HasMany
    {
        return $this->hasMany(ApprovalRule::class);
    }
}
