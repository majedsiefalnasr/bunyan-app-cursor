<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Models\Concerns\LogsModelActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends BaseModel
{
    use LogsModelActivity;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'name_ar',
        'name_en',
        'description',
        'customer_id',
        'contractor_id',
        'supervising_architect_id',
        'status',
        'budget',
        'budget_estimated',
        'budget_actual',
        'location',
        'city',
        'district',
        'location_lat',
        'location_lng',
        'project_type',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'budget_estimated' => 'decimal:2',
        'budget_actual' => 'decimal:2',
        'location_lat' => 'decimal:7',
        'location_lng' => 'decimal:7',
        'status' => ProjectStatus::class,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function contractor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contractor_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function supervisingArchitect(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervising_architect_id');
    }

    /**
     * @return HasMany<Phase, $this>
     */
    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class);
    }

    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, Phase::class);
    }

    /**
     * @return HasMany<Report, $this>
     */
    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function estimates(): HasMany
    {
        return $this->hasMany(Estimate::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ProjectStatus::InProgress->value);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            UserRole::Customer => $query->where('customer_id', $user->id),
            UserRole::Contractor => $query->where('contractor_id', $user->id),
            UserRole::SupervisingArchitect => $query->where('supervising_architect_id', $user->id),
            UserRole::FieldEngineer => $query->where(function (Builder $q) use ($user): void {
                $q->whereHas('members', function (Builder $m) use ($user): void {
                    $m->where('user_id', $user->id);
                })->orWhereHas('reports', function (Builder $r) use ($user): void {
                    $r->where('created_by', $user->id);
                });
            }),
            UserRole::Admin => $query,
        };
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
