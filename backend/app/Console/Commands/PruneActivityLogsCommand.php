<?php

namespace App\Console\Commands;

use App\Services\ActivityLogService;
use Illuminate\Console\Command;

class PruneActivityLogsCommand extends Command
{
    protected $signature = 'activity-log:prune {--days= : Override retention days from config} {--dry-run : Count rows that would be deleted}';

    protected $description = 'Delete activity log rows older than the retention window';

    public function handle(ActivityLogService $activityLogService): int
    {
        $days = $this->option('days');
        $retention = $days !== null && $days !== ''
            ? (int) $days
            : (int) config('activity_log.retention_days', 365);

        if ($retention < 1) {
            $this->error('Retention days must be at least 1.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $removed = $activityLogService->pruneOlderThanDays($retention, $dryRun);

        if ($dryRun) {
            $this->info("Dry run: {$removed} row(s) would be pruned (older than {$retention} days).");
        } else {
            $this->info("Pruned {$removed} activity log row(s) older than {$retention} days.");
        }

        return self::SUCCESS;
    }
}
