<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\User;

class RfqPolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Customer, UserRole::Contractor, UserRole::Admin], true);
    }

    public function view(User $user, Rfq $rfq): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($user->role === UserRole::Customer) {
            return $rfq->created_by === $user->id;
        }

        if ($user->role === UserRole::Contractor) {
            $supplierId = $user->supplierProfile?->id;
            if ($supplierId === null) {
                return false;
            }

            return $rfq->targets()->where('supplier_id', $supplierId)->exists();
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::Customer;
    }

    public function send(User $user, Rfq $rfq): bool
    {
        return $user->role === UserRole::Customer && $rfq->created_by === $user->id;
    }

    public function submitQuotation(User $user, Rfq $rfq): bool
    {
        if ($user->role !== UserRole::Contractor) {
            return false;
        }

        $supplierId = $user->supplierProfile?->id;
        if ($supplierId === null) {
            return false;
        }

        return $rfq->targets()->where('supplier_id', $supplierId)->exists();
    }

    public function close(User $user, Rfq $rfq): bool
    {
        return $user->role === UserRole::Customer && $rfq->created_by === $user->id;
    }

    public function beginEvaluation(User $user, Rfq $rfq): bool
    {
        return $user->role === UserRole::Customer && $rfq->created_by === $user->id;
    }

    public function acceptQuotation(User $user, Rfq $rfq, Quotation $quotation): bool
    {
        return $user->role === UserRole::Customer
            && $rfq->created_by === $user->id
            && $quotation->rfq_id === $rfq->id;
    }
}
