<?php

namespace App\Console\Commands;

use App\Services\Analytics\AnalyticsAggregationService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

final class AnalyticsAggregateCommand extends Command
{
    protected $signature = 'analytics:aggregate {--days=14} {--bucket=day}';

    protected $description = 'Recompute analytics rollups for recent period';

    public function __construct(
        private readonly AnalyticsAggregationService $aggregation,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $bucket = (string) $this->option('bucket');

        $to = CarbonImmutable::now('UTC')->startOfDay();
        $from = $to->subDays($days - 1);

        $this->aggregation->aggregate($from, $to, $bucket);

        $this->info('OK');

        return self::SUCCESS;
    }
}
