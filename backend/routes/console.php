<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('media:prune-temporary')->hourly();
Schedule::command('inventory:check-low-stock')->daily();

Schedule::command('analytics:aggregate --days=14 --bucket=day')->everyFiveMinutes();
Schedule::command('analytics:prune')->daily();
