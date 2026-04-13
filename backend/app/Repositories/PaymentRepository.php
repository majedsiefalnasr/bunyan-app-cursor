<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class PaymentRepository extends BaseRepository
{
    protected function model(): string
    {
        return Payment::class;
    }

    public function findByIdForUser(int $id, User $user): ?Payment
    {
        /** @var Payment|null */
        return $this->newQuery()
            ->whereKey($id)
            ->when($user->role !== UserRole::Admin, fn ($q) => $q->where('user_id', $user->id))
            ->first();
    }

    public function findByGatewayReference(string $reference): ?Payment
    {
        /** @var Payment|null */
        return $this->newQuery()->where('gateway_reference', $reference)->first();
    }

    public function paginateForUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with(['attempts'])
            ->when($user->role !== UserRole::Admin, fn ($q) => $q->where('user_id', $user->id))
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    public function updatePayment(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        return $payment->fresh();
    }
}
