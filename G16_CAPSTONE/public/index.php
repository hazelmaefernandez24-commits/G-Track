<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Ensure a sane memory limit before loading Composer autoloader. Some environments default to 2M,
// which is far too low and causes fatal errors in vendor/composer/autoload_static.php.
// If memory_limit is set and lower than 256M, bump it to 512M.
try {
    $currentLimit = ini_get('memory_limit');
    // Convert shorthand like 128M, 1G to bytes for comparison
    $toBytes = function($val) {
        $val = trim((string)$val);
        if ($val === '' || $val === '-1') return -1; // unlimited
        $unit = strtolower(substr($val, -1));
        $num = (int)$val;
        switch ($unit) {
            case 'g': return $num * 1024 * 1024 * 1024;
            case 'm': return $num * 1024 * 1024;
            case 'k': return $num * 1024;
            default: return (int)$val;
        }
    };
    $bytes = $toBytes($currentLimit);
    if ($bytes !== -1 && $bytes < 268435456) { // 256M
        @ini_set('memory_limit', '512M');
    }
} catch (\Throwable $e) {
    // Ignore errors; better to proceed than fail here.
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
