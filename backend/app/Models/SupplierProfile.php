<?php

namespace App\Models;

use App\Enums\SupplierVerificationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplierProfile extends BaseModel
{
    protected $fillable = [
        'user_id',
        'company_name_ar',
        'company_name_en',
        'commercial_reg',
        'tax_number',
        'city',
        'district',
        'address',
        'phone',
        'verification_status',
        'verified_at',
        'rating_avg',
        'total_ratings',
    ];

    protected $casts = [
        'verification_status' => SupplierVerificationStatus::class,
        'verified_at' => 'datetime',
        'rating_avg' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'supplier_id');
    }

    public function isVerified(): bool
    {
        return $this->verification_status === SupplierVerificationStatus::Verified;
    }

    public function scopeVerified(Builder $query): Builder
    {
        return $query->where('verification_status', SupplierVerificationStatus::Verified->value);
    }
}
