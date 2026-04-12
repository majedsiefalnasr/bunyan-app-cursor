<?php

namespace App\Repositories;

use App\Enums\SupplierVerificationStatus;
use App\Models\SupplierProfile;
use Illuminate\Pagination\LengthAwarePaginator;

class SupplierProfileRepository extends BaseRepository
{
    protected function model(): string
    {
        return SupplierProfile::class;
    }

    public function paginateForAdmin(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 20);

        return $this->newQuery()
            ->with(['user:id,name,email,role'])
            ->when($filters['verification_status'] ?? null, fn ($q, $status) => $q->where('verification_status', $status))
            ->when(
                $filters['search'] ?? null,
                fn ($q, $search) => $q->where(function ($inner) use ($search) {
                    $inner->where('company_name_ar', 'like', "%{$search}%")
                        ->orWhere('company_name_en', 'like', "%{$search}%")
                        ->orWhereHas('user', fn ($uq) => $uq->where('email', 'like', "%{$search}%"));
                })
            )
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function paginateVerifiedCatalog(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);

        return $this->newQuery()
            ->with(['user:id,name,email'])
            ->where('verification_status', SupplierVerificationStatus::Verified->value)
            ->when($filters['city'] ?? null, fn ($q, $city) => $q->where('city', $city))
            ->when(
                $filters['search'] ?? null,
                fn ($q, $search) => $q->where(function ($inner) use ($search) {
                    $inner->where('company_name_ar', 'like', "%{$search}%")
                        ->orWhere('company_name_en', 'like', "%{$search}%");
                })
            )
            ->orderByDesc('verified_at')
            ->paginate($perPage);
    }

    public function findWithUser(int $id): ?SupplierProfile
    {
        /** @var SupplierProfile|null */
        return $this->newQuery()->with(['user:id,name,email,phone'])->find($id);
    }

    public function findForUser(int $userId): ?SupplierProfile
    {
        /** @var SupplierProfile|null */
        return $this->newQuery()->where('user_id', $userId)->first();
    }

    public function countByUserId(int $userId): int
    {
        return $this->newQuery()->where('user_id', $userId)->count();
    }

    public function updateVerification(SupplierProfile $profile, SupplierVerificationStatus $status): SupplierProfile
    {
        $profile->verification_status = $status;
        $profile->verified_at = $status === SupplierVerificationStatus::Verified ? now() : null;
        $profile->save();

        return $profile->fresh(['user:id,name,email']) ?? $profile;
    }
}
