<?php

namespace App\Services;

use App\Enums\SupplierVerificationStatus;
use App\Enums\UserRole;
use App\Models\SupplierProfile;
use App\Models\User;
use App\Repositories\SupplierProfileRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

class SupplierProfileService
{
    public function __construct(private SupplierProfileRepository $repository)
    {
    }

    public function listCatalog(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateVerifiedCatalog($filters);
    }

    public function listForAdmin(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginateForAdmin($filters);
    }

    public function canView(?User $user, SupplierProfile $profile): bool
    {
        if ($profile->verification_status === SupplierVerificationStatus::Verified) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        if ($user->role === UserRole::Admin) {
            return true;
        }

        return $profile->user_id === $user->id;
    }

    public function getProfileForDisplay(?User $user, int $id): SupplierProfile
    {
        $profile = $this->repository->findWithUser($id);

        if ($profile === null) {
            throw (new ModelNotFoundException)->setModel(SupplierProfile::class, $id);
        }

        if (! $this->canView($user, $profile)) {
            throw (new ModelNotFoundException)->setModel(SupplierProfile::class, $id);
        }

        return $profile;
    }

    public function listProductsForDisplay(?User $user, SupplierProfile $profile): LengthAwarePaginator
    {
        if (! $this->canView($user, $profile)) {
            throw (new ModelNotFoundException)->setModel(SupplierProfile::class, $profile->id);
        }

        return $profile->products()
            ->active()
            ->orderBy('name')
            ->paginate(15);
    }

    public function createForContractor(User $user, array $data): SupplierProfile
    {
        if ($user->role !== UserRole::Contractor) {
            throw new AuthorizationException(__('errors.codes.RBAC_ROLE_DENIED.message'));
        }

        if ($this->repository->countByUserId($user->id) > 0) {
            throw ValidationException::withMessages([
                'supplier' => ['لديك ملف مورد مسجل مسبقاً.'],
            ]);
        }

        /** @var SupplierProfile */
        return $this->repository->create(array_merge($data, [
            'user_id' => $user->id,
            'verification_status' => SupplierVerificationStatus::Pending->value,
            'verified_at' => null,
        ]));
    }

    public function updateProfile(User $user, SupplierProfile $profile, array $data): SupplierProfile
    {
        if ($user->role !== UserRole::Admin && $profile->user_id !== $user->id) {
            throw new AuthorizationException(__('errors.codes.RBAC_ROLE_DENIED.message'));
        }

        /** @var SupplierProfile */
        return $this->repository->update($profile, $data);
    }

    public function verify(User $actor, SupplierProfile $profile, SupplierVerificationStatus $status): SupplierProfile
    {
        if ($actor->role !== UserRole::Admin) {
            throw new AuthorizationException(__('errors.codes.RBAC_ROLE_DENIED.message'));
        }

        if (! in_array($status, [SupplierVerificationStatus::Verified, SupplierVerificationStatus::Suspended], true)) {
            throw ValidationException::withMessages([
                'verification_status' => ['حالة التحقق غير صالحة.'],
            ]);
        }

        return $this->repository->updateVerification($profile, $status);
    }
}
