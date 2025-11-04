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
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        // Cleanup expired verification code mappings monthly
        $schedule->command('verification:cleanup --force')
            ->monthly()
            ->at('02:00')
            ->timezone('Asia/Jakarta')
            ->emailOutputOnFailure(config('mail.admin_email', 'admin@example.com'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
