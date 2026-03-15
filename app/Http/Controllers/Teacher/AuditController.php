<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuditController extends Controller
{
    public function start(string $tenantId): RedirectResponse
    {
        $teacher = auth()->user();

        // Verificar que el tenant pertenece a un grupo de este docente
        // (los modelos Group y User son centrales, usan la conexión pgsql)
        $group = $teacher->teacherGroups()->whereHas('tenants', fn ($q) => $q->where('id', $tenantId))->first();

        abort_if(! $group, 403, 'No tienes acceso a esta empresa.');

        // Forzar conexión central para evitar consultar en el schema del tenant activo
        $centralConn = config('tenancy.database.central_connection', 'pgsql');
        $tenant = Tenant::on($centralConn)->findOrFail($tenantId);

        session([
            'audit_mode'         => true,
            'audit_tenant_id'    => $tenant->id,
            'audit_student_name' => $tenant->student_name,
            'audit_company_name' => $tenant->company_name,
        ]);

        return redirect()->route('teacher.auditoria.dashboard', $tenantId);
    }

    public function stop(): RedirectResponse
    {
        session()->forget(['audit_mode', 'audit_tenant_id', 'audit_student_name', 'audit_company_name']);

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        return redirect()->route('teacher.dashboard');
    }

    public function dashboard(string $tenantId): View
    {
        // tenancy()->tenant ya contiene el tenant inicializado por el middleware,
        // así evitamos consultar la tabla tenants dentro del schema del estudiante.
        $tenant = tenancy()->tenant;
        abort_if(! $tenant, 403, 'Tenancy no inicializada.');

        return view('tenant.dashboard', ['student' => $tenant]);
    }
}
