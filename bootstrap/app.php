<?php

use App\Http\Middleware\AuditModeOnly;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\InitializeTenancyByStudent;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Confiar en todos los proxies (Railway, Nginx, load balancers)
        // Necesario para que Laravel detecte HTTPS correctamente y no genere
        // cookies CSRF con dominio/scheme incorrecto → evita error 419.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'tenant.initialize' => InitializeTenancyByStudent::class,
            'audit.only' => AuditModeOnly::class,
            'role' => CheckRole::class,
        ]);

        // Redirigir usuarios ya autenticados que visiten rutas "guest"
        $middleware->redirectUsersTo(function (Request $request) {
            if (auth()->guard('student')->check()) {
                return route('student.dashboard');
            }

            $user = auth()->guard('web')->user();
            if ($user) {
                return $user->role === 'superadmin'
                    ? route('admin.dashboard')
                    : route('teacher.dashboard');
            }

            return route('login');
        });

        // Run tenancy initialization on every web request (including Livewire AJAX updates)
        $middleware->appendToGroup('web', InitializeTenancyByStudent::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
