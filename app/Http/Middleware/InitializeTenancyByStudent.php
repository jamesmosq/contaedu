<?php

namespace App\Http\Middleware;

use App\Models\Central\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InitializeTenancyByStudent
{
    public function handle(Request $request, Closure $next): Response
    {
        // Modo normal: estudiante autenticado
        $student = auth('student')->user();
        if ($student instanceof Tenant) {
            tenancy()->initialize($student);

            return $next($request);
        }

        // Modo auditoría: solo activo en rutas de auditoría y peticiones Livewire AJAX
        // originadas desde una ruta de auditoría (verificado por el header Referer).
        // No debe activarse en rutas centrales (dashboard docente, admin, etc.).
        if (auth('web')->check() && session('audit_mode') && session('audit_tenant_id')) {
            $isAuditRoute = $request->routeIs('teacher.auditoria.*');
            $referer = $request->header('referer', '');
            $isLivewireFromAudit = str_starts_with($request->path(), 'livewire')
                && str_contains($referer, '/docente/auditoria/');

            if ($isAuditRoute || $isLivewireFromAudit) {
                $centralConn = config('tenancy.database.central_connection', 'pgsql');
                $tenant = Tenant::on($centralConn)->find(session('audit_tenant_id'));
                if ($tenant) {
                    tenancy()->initialize($tenant);
                }
            }
        }

        return $next($request);
    }
}
