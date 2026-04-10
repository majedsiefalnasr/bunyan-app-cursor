<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'task_id',
        'phase_id',
        'project_id',
        'created_by',
        'title',
        'content',
        'description',
        'attachments',
        'status',
    ];

    protected $casts = [
        'attachments' => 'json',
    ];

    // Relationships
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class)->withTrashed();
    }

    public function phase(): BelongsTo
    {
        return $this->belongsTo(Phase::class)->withTrashed();
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class)->withTrashed();
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeByProject(Builder $query, int $projectId): Builder
    {
        return $query->where('project_id', $projectId);
    }

    public function scopeByPhase(Builder $query, int $phaseId): Builder
    {
        return $query->where('phase_id', $phaseId);
    }

    public function scopeByTask(Builder $query, int $taskId): Builder
    {
        return $query->where('task_id', $taskId);
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
