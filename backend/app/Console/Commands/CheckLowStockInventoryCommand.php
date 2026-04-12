<?php

namespace App\Console\Commands;

use App\Services\InventoryService;
use Illuminate\Console\Command;

class CheckLowStockInventoryCommand extends Command
{
    protected $signature = 'inventory:check-low-stock';

    protected $description = 'Log structured low-stock inventory snapshot (count of rows at or below threshold)';

    public function handle(InventoryService $inventoryService): int
    {
        $count = $inventoryService->logLowStockSnapshot();
        $this->info('Low stock inventory rows: '.$count);

        return self::SUCCESS;
    }
}
