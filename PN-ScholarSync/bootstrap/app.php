<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
    })
    ->withSchedule(function ($schedule) {
        // Run Logify import every minute (60 seconds)
        // Use database import instead of API sync for better reliability
        $schedule->command('logify:import')
                 ->everyMinute()
                 ->withoutOverlapping(10)
                 ->runInBackground()
                 ->appendOutputTo(storage_path('logs/logify-sync.log'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
