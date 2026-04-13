<?php

namespace App\Models;

use App\Enums\RfqStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Rfq extends BaseModel
{
    protected $fillable = [
        'project_id',
        'created_by',
        'title',
        'description',
        'status',
        'delivery_deadline',
        'response_deadline',
        'sent_at',
        'awarded_quotation_id',
        'awarded_by',
        'awarded_at',
        'closed_at',
    ];

    protected $casts = [
        'status' => RfqStatus::class,
        'delivery_deadline' => 'date',
        'response_deadline' => 'datetime',
        'sent_at' => 'datetime',
        'awarded_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RfqItem::class);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(RfqTarget::class);
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(Quotation::class);
    }

    public function awardedQuotation(): HasOne
    {
        return $this->hasOne(Quotation::class, 'id', 'awarded_quotation_id');
    }

    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }
}
