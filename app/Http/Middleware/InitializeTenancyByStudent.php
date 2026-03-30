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
        // ── 1. Rutas de autenticación central (docente/admin) ─────────────────
        // Se usa is() porque POST /login no tiene nombre de ruta en Breeze.
        if ($request->is('login', 'logout', 'register', 'forgot-password', 'confirm-password', 'password', 'reset-password/*', 'email/verification-notification')
            || $request->routeIs('password.*', 'verification.*')) {
            return $next($request);
        }

        // ── 2. Rutas de autenticación del estudiante ───────────────────────────
        if ($request->is('estudiante/login', 'estudiante/logout')) {
            return $next($request);
        }

        // ── 3. Usuario web autenticado (docente/superadmin) ───────────────────
        // Nunca inicializar tenancy del estudiante aunque haya sesión paralela.
        // Manejar modo auditoría y modo demo del docente.
        if (auth('web')->check()) {
            $centralConn = config('tenancy.database.central_connection', 'pgsql');
            $referer = $request->header('referer', '');

            // Modo demo: docente accediendo a su propia empresa de demostración (escritura)
            if (session('demo_mode') && session('demo_tenant_id')) {
                $isDemoRoute = $request->routeIs('teacher.demo.*');
                $isLivewireFromDemo = str_starts_with($request->path(), 'livewire')
                    && str_contains($referer, '/docente/demo/');

                if ($isDemoRoute || $isLivewireFromDemo) {
                    $tenant = Tenant::on($centralConn)->find(session('demo_tenant_id'));
                    if ($tenant) {
                        tenancy()->initialize($tenant);
                    }
                }
            }

            // Modo auditoría: docente o coordinador auditando empresa (solo lectura)
            if (session('audit_mode') && session('audit_tenant_id')) {
                $isAuditRoute = $request->routeIs('teacher.auditoria.*')
                    || $request->routeIs('coordinator.auditoria.*');
                $isLivewireFromAudit = str_starts_with($request->path(), 'livewire')
                    && (str_contains($referer, '/docente/auditoria/')
                        || str_contains($referer, '/coordinador/auditoria/'));

                if ($isAuditRoute || $isLivewireFromAudit) {
                    $tenant = Tenant::on($centralConn)->find(session('audit_tenant_id'));
                    if ($tenant) {
                        tenancy()->initialize($tenant);
                    }
                }
            }

            return $next($request);
        }

        // ── 4. Estudiante autenticado ──────────────────────────────────────────
        $student = auth('student')->user();
        if ($student instanceof Tenant) {
            $centralConn = config('tenancy.database.central_connection', 'pgsql');
            $referer = $request->header('referer', '');

            // Modo referencia: estudiante viendo empresa demo del docente (solo lectura)
            if (session('reference_mode') && session('reference_tenant_id')) {
                $isRefRoute = $request->routeIs('student.referencia.*');
                $isLivewireFromRef = str_starts_with($request->path(), 'livewire')
                    && str_contains($referer, '/empresa/referencias/');

                if ($isRefRoute || $isLivewireFromRef) {
                    $tenant = Tenant::on($centralConn)->find(session('reference_tenant_id'));
                    if ($tenant) {
                        tenancy()->initialize($tenant);
                    }

                    return $next($request);
                }
            }

            // Modo normal: inicializar con la empresa propia del estudiante
            tenancy()->initialize($student);
        }

        return $next($request);
    }
}
