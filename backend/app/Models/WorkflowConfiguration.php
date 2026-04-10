<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkflowConfiguration extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'status_transitions',
        'approval_requirements',
        'is_global',
    ];

    protected $casts = [
        'status_transitions' => 'json',
        'approval_requirements' => 'json',
        'is_global' => 'boolean',
    ];

    // Relationships
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function approvalRules(): HasMany
    {
        return $this->hasMany(ApprovalRule::class);
    }
}
