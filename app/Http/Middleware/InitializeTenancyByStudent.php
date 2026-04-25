<?php

namespace App\Http\Middleware;

use App\Models\Central\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            $isAuditRoute = $request->routeIs('teacher.auditoria.*')
                || $request->routeIs('coordinator.auditoria.*');
            $isLivewireFromAudit = str_starts_with($request->path(), 'livewire')
                && (str_contains($referer, '/docente/auditoria/')
                    || str_contains($referer, '/coordinador/auditoria/'));

            if ($isAuditRoute || $isLivewireFromAudit) {
                // Usar el parámetro de ruta como fuente primaria; la sesión es secundaria
                $routeTenantId = $request->route('tenantId');
                $auditTenantId = $routeTenantId ?? session('audit_tenant_id');

                if ($auditTenantId) {
                    $tenant = Tenant::on($centralConn)->find($auditTenantId);

                    if ($tenant) {
                        // Si el tenant difiere del que está en sesión, re-validar autorización
                        if ($auditTenantId !== session('audit_tenant_id')) {
                            $user = auth('web')->user();
                            $hasAccess = false;

                            if ($user?->role?->value === 'coordinator') {
                                $hasAccess = $user->institution?->groups()
                                    ->whereHas('tenants', fn ($q) => $q->where('id', $auditTenantId))
                                    ->exists() ?? false;
                            } else {
                                $hasAccess = $user?->teacherGroups()
                                    ->whereHas('tenants', fn ($q) => $q->where('id', $auditTenantId))
                                    ->exists() ?? false;
                            }

                            if (! $hasAccess) {
                                return $next($request);
                            }

                            // Restaurar sesión de auditoría
                            session([
                                'audit_mode' => true,
                                'audit_tenant_id' => $tenant->id,
                                'audit_student_name' => $tenant->student_name,
                                'audit_company_name' => $tenant->company_name,
                            ]);
                        }

                        tenancy()->initialize($tenant);
                    }
                }
            }

            return $next($request);
        }

        // ── 4. Estudiante autenticado ──────────────────────────────────────────
        $student = auth('student')->user();
        if ($student instanceof Tenant) {
            // Bloquear si la institución fue deshabilitada con sesión activa
            if ($student->group_id) {
                $institutionActive = DB::table('groups')
                    ->join('institutions', 'groups.institution_id', '=', 'institutions.id')
                    ->where('groups.id', $student->group_id)
                    ->value('institutions.active');

                if ($institutionActive === false) {
                    auth('student')->logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return redirect()->route('student.login')->withErrors([
                        'cedula' => 'Tu institución no tiene acceso activo a ContaEdu. Contacta a tu coordinador.',
                    ]);
                }
            }

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
