<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'customer_id',
        'contractor_id',
        'supervising_architect_id',
        'status',
        'budget',
        'location',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
    ];

    // Relationships
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

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return match ($user->role) {
            'customer' => $query->where('customer_id', $user->id),
            'contractor' => $query->where('contractor_id', $user->id),
            'supervising_architect' => $query->where('supervising_architect_id', $user->id),
            'admin' => $query,
            default => $query->where('customer_id', $user->id),
        };
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
