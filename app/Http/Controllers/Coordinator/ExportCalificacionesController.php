<?php

namespace App\Http\Controllers\Coordinator;

use App\Exports\CalificacionesInstitutionExport;
use App\Http\Controllers\Controller;
use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Models\Central\StudentScore;
use App\Models\Central\Tenant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportCalificacionesController extends Controller
{
    public function __invoke(Request $request): Response|BinaryFileResponse
    {
        $institution = auth()->user()->coordinatedInstitution;
        abort_if(! $institution, 403);

        $format = $request->query('formato', 'pdf');
        $groupId = (int) $request->query('grupo', 0);

        $tenants = $this->tenants($institution, $groupId);

        $groupName = $groupId
            ? Group::find($groupId)?->name ?? 'Todos los grupos'
            : 'Todos los grupos';

        if ($format === 'excel') {
            $filename = 'calificaciones_'.str($institution->name)->slug().'_'.now()->format('Y-m-d').'.xlsx';

            return Excel::download(
                new CalificacionesInstitutionExport($tenants, $institution->name),
                $filename
            );
        }

        $modules = ['maestros' => 'Maestros', 'facturacion' => 'Facturación', 'compras' => 'Compras', 'cierre' => 'Cierre'];

        $scores = StudentScore::whereIn('tenant_id', $tenants->pluck('id'))
            ->current()
            ->get()
            ->groupBy('tenant_id')
            ->map(fn ($rows) => $rows->keyBy('module'));

        $rows = $tenants->map(function (Tenant $tenant) use ($scores, $modules) {
            $tenantScores = $scores->get($tenant->id, collect());
            $values = [];

            foreach (array_keys($modules) as $mod) {
                $values[$mod] = $tenantScores->has($mod) ? (float) $tenantScores[$mod]->score : null;
            }

            $filled = array_filter($values, fn ($v) => $v !== null);
            $promedio = count($filled) > 0 ? round(array_sum($filled) / count($filled), 1) : null;

            return ['tenant' => $tenant, 'scores' => $values, 'promedio' => $promedio];
        });

        $pdf = Pdf::loadView('pdf.calificaciones', [
            'institution' => $institution,
            'groupName' => $groupName,
            'modules' => $modules,
            'rows' => $rows,
            'generatedAt' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'landscape');

        $filename = 'calificaciones_'.str($institution->name)->slug().'_'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    private function tenants(Institution $institution, int $groupId)
    {
        $query = Tenant::where('type', 'student')
            ->whereHas('group', fn ($q) => $q->where('institution_id', $institution->id))
            ->with('group')
            ->orderBy('student_name');

        if ($groupId) {
            $query->where('group_id', $groupId);
        }

        return $query->get();
    }
}
