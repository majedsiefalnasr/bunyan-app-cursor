<?php

namespace App\Services\Analytics;

use App\Repositories\Analytics\BusinessAnalyticsReportRepository;
use Illuminate\Support\Facades\Log;

class BusinessAnalyticsReportService
{
    public const REPORT_TYPES = [
        'sales_summary',
        'orders_summary',
        'project_status',
        'inventory_low_stock',
        'supplier_performance',
        'financial_summary',
    ];

    public function __construct(
        private readonly BusinessAnalyticsReportRepository $repository,
    ) {
    }

    /**
     * @return list<array{type: string, label_key: string, exports: list<string>}>
     */
    public function reportTypesMetadata(): array
    {
        return [
            ['type' => 'sales_summary', 'label_key' => 'admin.reports.types.sales_summary', 'exports' => ['json', 'pdf', 'xlsx']],
            ['type' => 'orders_summary', 'label_key' => 'admin.reports.types.orders_summary', 'exports' => ['json', 'pdf', 'xlsx']],
            ['type' => 'project_status', 'label_key' => 'admin.reports.types.project_status', 'exports' => ['json', 'pdf', 'xlsx']],
            ['type' => 'inventory_low_stock', 'label_key' => 'admin.reports.types.inventory_low_stock', 'exports' => ['json', 'pdf', 'xlsx']],
            ['type' => 'supplier_performance', 'label_key' => 'admin.reports.types.supplier_performance', 'exports' => ['json', 'pdf', 'xlsx']],
            ['type' => 'financial_summary', 'label_key' => 'admin.reports.types.financial_summary', 'exports' => ['json', 'pdf', 'xlsx']],
        ];
    }

    /**
     * @param  array{date_from?: string|null, date_to?: string|null, category_id?: int|null, supplier_id?: int|null, status?: string|null}  $filters
     * @return array{meta: array<string, mixed>, summary: array<string, mixed>, rows: array<int, mixed>}
     */
    public function generate(string $type, array $filters, int $userId): array
    {
        $payload = match ($type) {
            'sales_summary' => $this->repository->salesSummary($filters),
            'orders_summary' => $this->repository->ordersSummary($filters),
            'project_status' => $this->repository->projectStatus($filters),
            'inventory_low_stock' => $this->repository->inventoryLowStock($filters),
            'supplier_performance' => $this->repository->supplierPerformance($filters),
            'financial_summary' => $this->repository->financialSummary($filters),
            default => throw new \InvalidArgumentException('Invalid report type'),
        };

        $meta = [
            'report_type' => $type,
            'generated_at' => now()->toIso8601String(),
            'truncated' => $payload['truncated'],
            'stub' => ($payload['summary']['stub'] ?? false) === true,
        ];

        Log::info('analytics.report.generated', [
            'action' => 'analytics.report.generated',
            'report_type' => $type,
            'user_id' => $userId,
            'truncated' => $payload['truncated'],
        ]);

        return [
            'meta' => $meta,
            'summary' => $payload['summary'],
            'rows' => $payload['rows'],
        ];
    }

    /**
     * @param  array<int, mixed>  $rows
     * @return array{headers: list<string>, matrix: list<list<string|int|float|null>>}
     */
    public function tabularFromRows(array $rows): array
    {
        if ($rows === []) {
            return ['headers' => [], 'matrix' => []];
        }

        /** @var array<string, mixed> $first */
        $first = $rows[0];
        $headers = array_keys($first);
        $matrix = [];
        foreach ($rows as $row) {
            $line = [];
            foreach ($headers as $h) {
                $line[] = is_array($row[$h] ?? null) ? json_encode($row[$h], JSON_UNESCAPED_UNICODE) : ($row[$h] ?? null);
            }
            $matrix[] = $line;
        }

        return ['headers' => $headers, 'matrix' => $matrix];
    }

    /**
     * @param  array{date_from?: string|null, date_to?: string|null, category_id?: int|null, supplier_id?: int|null, status?: string|null}  $filters
     * @return array{headers: list<string>, matrix: list<list<string|int|float|null>>}
     */
    public function tabular(string $type, array $filters, int $userId): array
    {
        $data = $this->generate($type, $filters, $userId);

        return $this->tabularFromRows($data['rows']);
    }
}
