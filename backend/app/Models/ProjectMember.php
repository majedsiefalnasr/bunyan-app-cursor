<?php

namespace App\Models;

use App\Enums\ProjectRole;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectMember extends BaseModel
{
    use LogsModelActivity;

    protected $fillable = [
        'project_id',
        'user_id',
        'project_role',
        'joined_at',
    ];

    protected $casts = [
        'project_role' => ProjectRole::class,
        'joined_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
