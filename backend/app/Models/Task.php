<?php

namespace App\Models;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends BaseModel
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'phase_id',
        'name',
        'title_ar',
        'title_en',
        'description',
        'status',
        'priority',
        'budget',
        'assigned_to',
        'start_date',
        'end_date',
        'due_date',
        'estimated_hours',
        'actual_hours',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'due_date' => 'date',
        'budget' => 'decimal:2',
        'estimated_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'sort_order' => 'integer',
        'status' => TaskStatus::class,
        'priority' => TaskPriority::class,
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TaskComment::class)->orderBy('created_at');
    }

    public function dependencyRecords(): HasMany
    {
        return $this->hasMany(TaskDependency::class, 'task_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', TaskStatus::InProgress->value);
    }

    public function scopeByPhase(Builder $query, int $phaseId): Builder
    {
        return $query->where('phase_id', $phaseId);
    }

    public function scopeByProject(Builder $query, int $projectId): Builder
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeAssignedTo(Builder $query, int $userId): Builder
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
