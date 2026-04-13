<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\User;

class InvoicePolicy
{
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Customer, UserRole::Contractor], true);
    }

    public function view(User $user, Invoice $invoice): bool
    {
        if ($user->role === UserRole::Admin) {
            return true;
        }

        if ($user->role === UserRole::Customer) {
            return (int) $invoice->customer_id === (int) $user->id;
        }

        if ($user->role === UserRole::Contractor) {
            $supplierId = $user->supplierProfile?->id;

            return $supplierId !== null && (int) $invoice->supplier_id === (int) $supplierId;
        }

        return false;
    }

    public function create(User $user): bool
    {
        return in_array($user->role, [UserRole::Admin, UserRole::Customer], true);
    }

    public function send(User $user, Invoice $invoice): bool
    {
        return $this->view($user, $invoice);
    }

    public function void(User $user, Invoice $invoice): bool
    {
        return $user->role === UserRole::Admin;
    }

    public function downloadPdf(User $user, Invoice $invoice): bool
    {
        return $this->view($user, $invoice);
    }
}
