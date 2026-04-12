<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends BaseModel
{
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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function contractor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'contractor_id');
    }

    public function supervisingArchitect(): BelongsTo
    {
        return $this->belongsTo(User::class, 'supervising_architect_id');
    }

    public function phases(): HasMany
    {
        return $this->hasMany(Phase::class);
    }

    public function tasks(): HasManyThrough
    {
        return $this->hasManyThrough(Task::class, Phase::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
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
            UserRole::FieldEngineer => $query->whereHas('reports', function (Builder $q) use ($user): void {
                $q->where('created_by', $user->id);
            }),
            UserRole::Admin => $query,
        };
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
