<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// PHP 8.5: see `artisan` for rationale (vendor framework `config/database.php` + PDO constants).
if (PHP_VERSION_ID >= 80500) {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
}

// Bootstrap Laravel and handle the request...
(require_once __DIR__.'/../bootstrap/app.php')
    ->handleRequest(Request::capture());
