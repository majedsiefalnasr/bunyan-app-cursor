<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Models\Concerns\HasBaseModelBehavior;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasBaseModelBehavior;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'active' => 'boolean',
        'role' => UserRole::class,
    ];

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class, 'customer_id');
    }

    public function contractorProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'contractor_id');
    }

    public function supervisedProjects(): HasMany
    {
        return $this->hasMany(Project::class, 'supervising_architect_id');
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'created_by');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'customer_id');
    }

    public function conversationParticipants(): HasMany
    {
        return $this->hasMany(ConversationParticipant::class);
    }

    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function uploadedMedia(): HasMany
    {
        return $this->hasMany(Media::class, 'uploaded_by');
    }

    public function supplierProfile(): HasOne
    {
        return $this->hasOne(SupplierProfile::class);
    }

    public function notifications(): MorphMany
    {
        return $this->morphMany(PlatformDatabaseNotification::class, 'notifiable')
            ->orderByDesc('created_at');
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user')
            ->withPivot(['assigned_at', 'assigned_by'])
            ->withTimestamps();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }
}
