<?php

use App\Http\Middleware\AuditModeOnly;
use App\Http\Middleware\InitializeTenancyByStudent;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'tenant.initialize' => InitializeTenancyByStudent::class,
            'audit.only' => AuditModeOnly::class,
            'role' => App\Http\Middleware\CheckRole::class,
        ]);

        // Run tenancy initialization on every web request (including Livewire AJAX updates)
        $middleware->appendToGroup('web', InitializeTenancyByStudent::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
