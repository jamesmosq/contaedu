<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Tenant\DashboardController;
use App\Models\Central\ReferenceAccessLog;
use App\Models\Central\Tenant;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReferenceController extends Controller
{
    /** Estudiante entra a una empresa de referencia del docente (solo lectura). */
    public function enter(string $demoId): RedirectResponse
    {
        $student = auth('student')->user();
        $centralConn = config('tenancy.database.central_connection', 'pgsql');

        // La empresa demo debe existir y estar asignada al grupo del estudiante
        $demo = Tenant::on($centralConn)
            ->where('id', $demoId)
            ->where('type', 'demo')
            ->whereHas('assignedGroups', fn ($q) => $q->where('groups.id', $student->group_id))
            ->first();

        abort_if(! $demo, 404, 'Empresa de referencia no encontrada o sin acceso para tu grupo.');

        $teacher = User::on($centralConn)->find($demo->teacher_id);

        // Registrar acceso (upsert — actualiza timestamp si ya existía)
        ReferenceAccessLog::on($centralConn)->upsert(
            [
                'student_tenant_id' => $student->id,
                'demo_tenant_id' => $demo->id,
                'accessed_at' => now(),
            ],
            ['student_tenant_id', 'demo_tenant_id'],
            ['accessed_at'],
        );

        session([
            'reference_mode' => true,
            'reference_tenant_id' => $demo->id,
            'reference_company_name' => $demo->company_name,
            'reference_teacher_name' => $teacher?->name ?? 'Docente',
        ]);

        return redirect()->route('student.referencia.dashboard', $demoId);
    }

    /** Estudiante sale de la empresa de referencia y vuelve a su panel. */
    public function exit(): RedirectResponse
    {
        session()->forget(['reference_mode', 'reference_tenant_id', 'reference_company_name', 'reference_teacher_name']);

        if (tenancy()->initialized) {
            tenancy()->end();
        }

        // Re-inicializar el tenant del estudiante
        $student = auth('student')->user();
        if ($student instanceof Tenant) {
            tenancy()->initialize($student);
        }

        return redirect()->route('student.referencias');
    }

    /** Dashboard de la empresa de referencia (solo lectura). */
    public function dashboard(string $demoId): View
    {
        $tenant = tenancy()->tenant;
        abort_if(! $tenant, 403, 'Tenancy no inicializada.');

        return app(DashboardController::class)->index();
    }
}
