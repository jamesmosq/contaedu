<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReferenceController extends Controller
{
    /** Estudiante entra a una empresa de referencia del docente (solo lectura). */
    public function enter(string $demoId): RedirectResponse
    {
        $student = auth('student')->user();
        $centralConn = config('tenancy.database.central_connection', 'pgsql');

        // La empresa demo debe estar publicada y pertenecer al docente del grupo del estudiante
        $demo = Tenant::on($centralConn)
            ->where('id', $demoId)
            ->where('type', 'demo')
            ->where('published', true)
            ->first();

        abort_if(! $demo, 404, 'Empresa de referencia no encontrada.');

        // Verificar que el docente de la empresa demo es el docente del grupo del estudiante
        $studentGroup = \App\Models\Central\Group::on($centralConn)->find($student->group_id);
        abort_if(! $studentGroup || $studentGroup->teacher_id !== $demo->teacher_id, 403, 'Sin acceso.');

        $teacher = \App\Models\User::on($centralConn)->find($demo->teacher_id);

        session([
            'reference_mode'         => true,
            'reference_tenant_id'    => $demo->id,
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

        return view('tenant.dashboard', ['student' => $tenant]);
    }
}
