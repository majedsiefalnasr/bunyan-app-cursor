<?php

namespace App\Models;

use App\Enums\ProjectRole;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectInvitation extends BaseModel
{
    use LogsModelActivity;

    protected $fillable = [
        'project_id',
        'email',
        'project_role',
        'token_hash',
        'invited_by',
        'accepted_at',
        'expires_at',
    ];

    protected $casts = [
        'project_role' => ProjectRole::class,
        'accepted_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function invitedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }
}
