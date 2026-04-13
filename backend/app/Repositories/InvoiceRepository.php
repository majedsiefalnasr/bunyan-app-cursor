<?php

namespace App\Repositories;

use App\Enums\UserRole;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class InvoiceRepository extends BaseRepository
{
    protected function model(): string
    {
        return Invoice::class;
    }

    public function findByIdOrFail(int $id): Model
    {
        return $this->newQuery()
            ->with(['items', 'order', 'customer', 'supplierProfile'])
            ->findOrFail($id);
    }

    public function findByOrderIdForUpdate(int $orderId): ?Invoice
    {
        /** @var Invoice|null */
        return $this->newQuery()
            ->where('order_id', $orderId)
            ->lockForUpdate()
            ->first();
    }

    public function lockByIdForUpdate(int $id): Invoice
    {
        /** @var Invoice */
        return $this->newQuery()->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function createInvoice(array $attributes): Invoice
    {
        /** @var Invoice */
        return $this->newQuery()->create($attributes);
    }

    /**
     * Next 4-digit suffix for INV-YYYYMMDD-XXXX on a given calendar day.
     */
    public function nextDisplaySequenceForDate(string $ymd): int
    {
        $prefix = 'INV-'.$ymd.'-';
        $numbers = $this->newQuery()
            ->where('invoice_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->pluck('invoice_number');

        $max = 0;
        foreach ($numbers as $invoiceNumber) {
            if (preg_match('/^INV-\d{8}-(\d{4})$/', (string) $invoiceNumber, $m)) {
                $max = max($max, (int) $m[1]);
            }
        }

        return $max + 1;
    }

    public function paginateForActor(User $user, array $filters = []): LengthAwarePaginator
    {
        $query = $this->newQuery()->with(['order', 'customer', 'supplierProfile']);

        if ($user->role === UserRole::Admin) {
            // all
        } elseif ($user->role === UserRole::Customer) {
            $query->where('customer_id', $user->id);
        } elseif ($user->role === UserRole::Contractor) {
            $supplierId = $user->supplierProfile?->id;
            if ($supplierId === null) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('supplier_id', $supplierId);
            }
        } else {
            $query->whereRaw('1 = 0');
        }

        if (! empty($filters['status'])) {
            $query->where('status', (string) $filters['status']);
        }

        return $query->orderByDesc('created_at')->paginate((int) ($filters['per_page'] ?? 15));
    }
}
