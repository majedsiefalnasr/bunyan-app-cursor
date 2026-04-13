<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Quotation;
use App\Models\Rfq;
use App\Models\User;

class QuotationPolicy
{
    public function viewAnyForRfq(User $user, Rfq $rfq): bool
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

    public function submitForRfq(User $user, Rfq $rfq): bool
    {
        return $user->role === UserRole::Contractor && $this->viewAnyForRfq($user, $rfq);
    }

    public function view(User $user, Quotation $quotation): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($user->role === UserRole::Customer) {
            return $quotation->rfq?->created_by === $user->id;
        }

        if ($user->role === UserRole::Contractor) {
            $supplierId = $user->supplierProfile?->id;

            return $supplierId !== null && $quotation->supplier_id === $supplierId;
        }

        return false;
    }

    public function accept(User $user, Rfq $rfq, Quotation $quotation): bool
    {
        return $user->role === UserRole::Customer
            && $rfq->created_by === $user->id
            && $quotation->rfq_id === $rfq->id;
    }
}
