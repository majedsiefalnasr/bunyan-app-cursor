<?php

namespace App\Console\Commands;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class AnalyticsPruneCommand extends Command
{
    protected $signature = 'analytics:prune';

    protected $description = 'Prune analytics raw events and rollups by retention';

    public function handle(): int
    {
        $now = CarbonImmutable::now('UTC');

        $rawCutoff = $now->subDays(90);
        $rollupCutoff = $now->subYears(2)->toDateString();

        $rawDeleted = DB::table('analytics_events')
            ->where('occurred_at', '<', $rawCutoff)
            ->delete();

        $rollupsDeleted = DB::table('analytics_metric_rollups')
            ->where('bucket_end', '<', $rollupCutoff)
            ->delete();

        $this->info("raw_deleted={$rawDeleted} rollups_deleted={$rollupsDeleted}");

        return self::SUCCESS;
    }
}
