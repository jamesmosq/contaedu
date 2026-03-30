<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboard;
use App\Models\Central\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AuditController extends Controller
{
    /** Coordinador inicia auditoría de una empresa dentro de su IE. */
    public function start(string $tenantId): RedirectResponse
    {
        $coordinator = auth()->user();
        $institution = $coordinator->coordinatedInstitution;

        abort_if(! $institution, 403, 'No tienes una institución asignada.');

        $centralConn = config('tenancy.database.central_connection', 'pgsql');
        $tenant = Tenant::on($centralConn)
            ->whereHas('group', fn ($q) => $q->where('institution_id', $institution->id))
            ->findOrFail($tenantId);

        // Limpiar cualquier modo demo activo antes de entrar a auditoría
        session()->forget(['demo_mode', 'demo_tenant_id', 'demo_company_name']);

        session([
            'audit_mode' => true,
            'audit_tenant_id' => $tenant->id,
            'audit_student_name' => $tenant->student_name,
            'audit_company_name' => $tenant->company_name,
        ]);

        return redirect()->route('coordinator.auditoria.dashboard', $tenantId);
    }

    public function stop(): RedirectResponse
    {
        session()->forget(['audit_mode', 'audit_tenant_id', 'audit_student_name', 'audit_company_name']);

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        return redirect()->route('coordinator.dashboard');
    }

    public function dashboard(string $tenantId): View
    {
        $tenant = tenancy()->tenant;
        abort_if(! $tenant, 403, 'Tenancy no inicializada.');

        return app(TenantDashboard::class)->index();
    }
}
