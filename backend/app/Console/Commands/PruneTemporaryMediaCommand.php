<?php

namespace App\Console\Commands;

use App\Services\MediaService;
use Illuminate\Console\Command;

class PruneTemporaryMediaCommand extends Command
{
    protected $signature = 'media:prune-temporary {--hours=24 : Delete temporary media older than this many hours}';

    protected $description = 'Remove temporary media records and files past the retention window';

    public function handle(MediaService $mediaService): int
    {
        $hours = (int) $this->option('hours');
        if ($hours < 1) {
            $this->error('hours must be at least 1');

            return self::FAILURE;
        }

        $removed = $mediaService->pruneTemporaryOlderThanHours($hours);
        $this->info("Pruned {$removed} temporary media item(s).");

        return self::SUCCESS;
    }
}
