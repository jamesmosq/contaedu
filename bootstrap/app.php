<?php

use App\Http\Middleware\AuditModeOnly;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\InitializeTenancyByStudent;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Middleware\ValidatePathEncoding;
use Illuminate\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

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
                return match ($user->role->value) {
                    'superadmin' => route('admin.dashboard'),
                    'coordinator' => route('coordinator.dashboard'),
                    default => route('teacher.dashboard'),
                };
            }

            return route('login');
        });

        // Tenancy must initialize BEFORE SubstituteBindings (route model binding),
        // otherwise Eloquent tries to resolve e.g. FeFactura from the public schema.
        // We also need it AFTER StartSession/auth, so we set priority explicitly.
        $middleware->appendToGroup('web', InitializeTenancyByStudent::class);
        $middleware->priority([
            InvokeDeferredCallbacks::class,
            HandleCors::class,
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
            TrustProxies::class,
            ValidatePathEncoding::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            Authenticate::class,
            InitializeTenancyByStudent::class,
            SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
