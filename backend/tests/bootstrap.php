<?php

declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;

if (!defined('LARAVEL_START')) {
    define('LARAVEL_START', microtime(true));
}

require dirname(__DIR__) . '/vendor/autoload.php';

/** @var \Illuminate\Foundation\Application $app */
$app = require dirname(__DIR__) . '/bootstrap/app.php';

$app->make(Kernel::class)->bootstrap();
