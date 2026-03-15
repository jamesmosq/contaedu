<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditModeOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! session('audit_mode')) {
            abort(403, 'Acceso no autorizado.');
        }

        return $next($request);
    }
}
