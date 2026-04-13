<?php

namespace App\Repositories\Analytics;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BusinessAnalyticsReportRepository
{
    private const ROW_CAP = 5000;

    /**
     * @param  array{date_from?: string|null, date_to?: string|null, category_id?: int|null, supplier_id?: int|null, status?: string|null}  $filters
     * @return array{summary: array<string, mixed>, rows: array<int, array<string, mixed>>, truncated: bool}
     */
    public function salesSummary(array $filters): array
    {
        $query = $this->ordersBetween($filters);

        $summary = [
            'order_count' => (clone $query)->count(),
            'revenue' => (string) (clone $query)->sum('total_amount'),
            'average_order_value' => $this->averageOrderValue($query),
        ];

        $rowsQuery = (clone $query)->orderByDesc('id')->select([
            'id',
            'order_number',
            'status',
            'total_amount',
            'created_at',
            'supplier_id',
        ]);

        return $this->cappedRows($rowsQuery, $summary);
    }

    /**
     * @param  array{date_from?: string|null, date_to?: string|null, category_id?: int|null, supplier_id?: int|null, status?: string|null}  $filters
     * @return array{summary: array<string, mixed>, rows: array<int, array<string, mixed>>, truncated: bool}
     */
    public function ordersSummary(array $filters): array
    {
        $query = $this->ordersBetween($filters);
        $statusCounts = (clone $query)
            ->selectRaw('status, COUNT(*) as c')
            ->groupBy('status')
            ->pluck('c', 'status')
            ->all();

        $summary = [
            'order_count' => (clone $query)->count(),
            'by_status' => $statusCounts,
        ];

        $rowsQuery = (clone $query)->orderByDesc('id')->select([
            'id',
            'order_number',
            'status',
            'total_amount',
            'created_at',
        ]);

        return $this->cappedRows($rowsQuery, $summary);
    }

    /**
     * @param  array{date_from?: string|null, date_to?: string|null, category_id?: int|null, supplier_id?: int|null, status?: string|null}  $filters
     * @return array{summary: array<string, mixed>, rows: array<int, array<string, mixed>>, truncated: bool}
     */
    public function projectStatus(array $filters): array
    {
        $query = Project::query();
        $this->applyProjectDateFilters($query, $filters);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $summary = [
            'project_count' => (clone $query)->count(),
            'by_status' => (clone $query)
                ->selectRaw('status, COUNT(*) as c')
                ->groupBy('status')
                ->pluck('c', 'status')
                ->all(),
        ];

        $rowsQuery = (clone $query)->orderByDesc('id')->select([
            'id',
            'name',
            'status',
            'budget',
            'start_date',
            'end_date',
            'created_at',
        ]);

        return $this->cappedRows($rowsQuery, $summary);
    }

    /**
     * @param  array{date_from?: string|null, date_to?: string|null, category_id?: int|null, supplier_id?: int|null, status?: string|null}  $filters
     * @return array{summary: array<string, mixed>, rows: array<int, array<string, mixed>>, truncated: bool}
     */
    public function inventoryLowStock(array $filters): array
    {
        $query = Product::query()->where('active', true)
            ->whereColumn('quantity_in_stock', '<=', '10');

        if (! empty($filters['category_id'])) {
            $query->where('category_id', (int) $filters['category_id']);
        }

        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', (int) $filters['supplier_id']);
        }

        $summary = [
            'low_stock_count' => (clone $query)->count(),
        ];

        $rowsQuery = (clone $query)->orderBy('quantity_in_stock')->select([
            'id',
            'sku',
            'name',
            'quantity_in_stock',
            'category_id',
            'supplier_id',
        ]);

        return $this->cappedRows($rowsQuery, $summary);
    }

    /**
     * @param  array{date_from?: string|null, date_to?: string|null, category_id?: int|null, supplier_id?: int|null, status?: string|null}  $filters
     * @return array{summary: array<string, mixed>, rows: array<int, array<string, mixed>>, truncated: bool}
     */
    public function supplierPerformance(array $filters): array
    {
        $orders = $this->ordersBetween($filters);

        $query = DB::table('supplier_profiles')
            ->selectRaw('supplier_profiles.id as supplier_profile_id')
            ->selectRaw('COALESCE(supplier_profiles.company_name_en, supplier_profiles.company_name_ar) as supplier_name')
            ->selectRaw('COUNT(orders.id) as order_count')
            ->selectRaw('COALESCE(SUM(orders.total_amount), 0) as revenue')
            ->selectRaw('supplier_profiles.rating_avg as rating_avg')
            ->leftJoin('orders', 'orders.supplier_id', '=', 'supplier_profiles.id');

        if ($this->hasOrderDateFilters($filters)) {
            if (! empty($filters['date_from'])) {
                $query->where('orders.created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
            }
            if (! empty($filters['date_to'])) {
                $query->where('orders.created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
            }
        }

        $rows = $query
            ->groupBy(
                'supplier_profiles.id',
                'supplier_profiles.company_name_en',
                'supplier_profiles.company_name_ar',
                'supplier_profiles.rating_avg',
            )
            ->orderByDesc('revenue')
            ->limit(self::ROW_CAP)
            ->get()
            ->map(fn (object $r) => [
                'supplier_profile_id' => (int) $r->supplier_profile_id,
                'supplier_name' => (string) $r->supplier_name,
                'order_count' => (int) $r->order_count,
                'revenue' => (string) $r->revenue,
                'rating_avg' => $r->rating_avg ?? null,
            ])
            ->values()
            ->all();

        $summary = [
            'supplier_count' => count($rows),
            'total_revenue' => (string) collect($rows)->sum(fn ($row) => $row['revenue']),
        ];

        return [
            'summary' => $summary,
            'rows' => $rows,
            'truncated' => false,
        ];
    }

    /**
     * @param  array{date_from?: string|null, date_to?: string|null, category_id?: int|null, supplier_id?: int|null, status?: string|null}  $filters
     * @return array{summary: array<string, mixed>, rows: array<int, array<string, mixed>>, truncated: bool}
     */
    public function financialSummary(array $filters): array
    {
        $invoiceQuery = Invoice::query();
        if (! empty($filters['date_from'])) {
            $invoiceQuery->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }
        if (! empty($filters['date_to'])) {
            $invoiceQuery->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        $summary = [
            'invoice_count' => (clone $invoiceQuery)->count(),
            'invoiced_total' => (string) (clone $invoiceQuery)->sum('total'),
            'vat_total' => (string) (clone $invoiceQuery)->sum('vat_amount'),
            'stub' => true,
        ];

        $rowsQuery = (clone $invoiceQuery)->orderByDesc('id')->select([
            'id',
            'invoice_number',
            'total',
            'vat_amount',
            'status',
            'created_at',
        ]);

        return $this->cappedRows($rowsQuery, $summary);
    }

    /**
     * @param  array{date_from?: string|null, date_to?: string|null, category_id?: int|null, supplier_id?: int|null, status?: string|null}  $filters
     */
    private function ordersBetween(array $filters): Builder
    {
        $query = Order::query();

        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
        if (! empty($filters['supplier_id'])) {
            $query->where('supplier_id', (int) $filters['supplier_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query;
    }

    /**
     * @param  array{date_from?: string|null, date_to?: string|null, category_id?: int|null, supplier_id?: int|null, status?: string|null}  $filters
     */
    private function applyProjectDateFilters(Builder $query, array $filters): void
    {
        if (! empty($filters['date_from'])) {
            $query->where('created_at', '>=', Carbon::parse($filters['date_from'])->startOfDay());
        }
        if (! empty($filters['date_to'])) {
            $query->where('created_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }
    }

    /**
     * @param  array{date_from?: string|null, date_to?: string|null, category_id?: int|null, supplier_id?: int|null, status?: string|null}  $filters
     */
    private function hasOrderDateFilters(array $filters): bool
    {
        return ! empty($filters['date_from']) || ! empty($filters['date_to']);
    }

    private function averageOrderValue(Builder $base): string
    {
        $count = (clone $base)->count();
        if ($count === 0) {
            return '0.00';
        }

        $sum = (clone $base)->sum('total_amount');

        return number_format(((float) $sum) / $count, 2, '.', '');
    }

    /**
     * @return array{summary: array<string, mixed>, rows: array<int, array<string, mixed>>, truncated: bool}
     */
    private function cappedRows(Builder $rowsQuery, array $summary): array
    {
        $total = (clone $rowsQuery)->count();
        $truncated = $total > self::ROW_CAP;
        $rows = $rowsQuery->limit(self::ROW_CAP)->get()->map(fn ($m) => $m->toArray())->values()->all();

        return [
            'summary' => $summary,
            'rows' => $rows,
            'truncated' => $truncated,
        ];
    }
}
