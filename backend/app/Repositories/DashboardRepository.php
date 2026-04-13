<?php

namespace App\Repositories;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Order;
use App\Models\Project;
use App\Models\Report;
use App\Models\SupplierProfile;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class DashboardRepository
{
    public function countActiveUsers(): int
    {
        return User::query()->where('active', true)->count();
    }

    public function countProjects(): int
    {
        return Project::query()->count();
    }

    public function countOrders(): int
    {
        return Order::query()->count();
    }

    public function sumRevenueCompletedOrders(?int $supplierProfileId = null): float
    {
        $query = Order::query()->whereIn('status', [
            OrderStatus::Completed,
            OrderStatus::Delivered,
        ]);

        if ($supplierProfileId !== null) {
            $query->where('supplier_id', $supplierProfileId);
        }

        return (float) $query->sum('total_amount');
    }

    public function resolveSupplierProfileId(int $userId): ?int
    {
        $id = SupplierProfile::query()->where('user_id', $userId)->value('id');

        return $id !== null ? (int) $id : null;
    }

    /**
     * @return array<string, int|float|null>
     */
    public function kpisForUser(User $user): array
    {
        $role = $user->role;

        return match ($role) {
            UserRole::Admin => [
                'users' => $this->countActiveUsers(),
                'projects' => $this->countProjects(),
                'orders' => $this->countOrders(),
                'revenue_sar' => $this->sumRevenueCompletedOrders(null),
                'tasks_assigned' => null,
                'reports' => null,
            ],
            UserRole::Customer => [
                'users' => null,
                'projects' => Project::query()->where('customer_id', $user->id)->count(),
                'orders' => Order::query()->where('customer_id', $user->id)->count(),
                'revenue_sar' => null,
                'tasks_assigned' => null,
                'reports' => null,
            ],
            UserRole::Contractor => [
                'users' => null,
                'projects' => Project::query()->where('contractor_id', $user->id)->count(),
                'orders' => $this->contractorOrdersCount($user->id),
                'revenue_sar' => $this->contractorRevenue($user->id),
                'tasks_assigned' => null,
                'reports' => null,
            ],
            UserRole::SupervisingArchitect => [
                'users' => null,
                'projects' => Project::query()->where('supervising_architect_id', $user->id)->count(),
                'orders' => null,
                'revenue_sar' => null,
                'tasks_assigned' => null,
                'reports' => null,
            ],
            UserRole::FieldEngineer => [
                'users' => null,
                'projects' => null,
                'orders' => null,
                'revenue_sar' => null,
                'tasks_assigned' => Task::query()->where('assigned_to', $user->id)->count(),
                'reports' => Report::query()->where('created_by', $user->id)->count(),
            ],
        };
    }

    public function paginateRecentActivity(bool $global, int $userId, int $perPage): LengthAwarePaginator
    {
        $query = ActivityLog::query()->with('actor')->orderByDesc('created_at');

        if (! $global) {
            $query->where('user_id', $userId);
        }

        return $query->paginate($perPage);
    }

    private function contractorOrdersCount(int $userId): int
    {
        $supplierId = $this->resolveSupplierProfileId($userId);
        if ($supplierId === null) {
            return 0;
        }

        return Order::query()->where('supplier_id', $supplierId)->count();
    }

    private function contractorRevenue(int $userId): float
    {
        $supplierId = $this->resolveSupplierProfileId($userId);
        if ($supplierId === null) {
            return 0.0;
        }

        return $this->sumRevenueCompletedOrders($supplierId);
    }
}
