<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Central\Group;
use App\Models\Central\StudentScore;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportCalificacionesController extends Controller
{
    public function __invoke(int $groupId): StreamedResponse
    {
        $group = Group::where('id', $groupId)
            ->where('teacher_id', auth()->id())
            ->with('tenants')
            ->firstOrFail();

        $tenantIds = $group->tenants->pluck('id');

        $scores = StudentScore::whereIn('tenant_id', $tenantIds)
            ->current()
            ->get()
            ->groupBy('tenant_id')
            ->map(fn ($rows) => $rows->keyBy('module'));

        $modules = ['maestros', 'facturacion', 'compras', 'cierre'];
        $filename = 'calificaciones_'.str($group->name)->slug().'_'.now()->format('Y-m-d').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($group, $scores, $modules) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Cédula', 'Nombre', 'Empresa', 'Maestros', 'Facturación', 'Compras', 'Cierre', 'Promedio']);

            foreach ($group->tenants->sortBy('student_name') as $tenant) {
                $tenantScores = $scores->get($tenant->id, collect());
                $values = [];

                foreach ($modules as $module) {
                    $values[] = $tenantScores->has($module)
                        ? number_format((float) $tenantScores[$module]->score, 1, '.', '')
                        : '';
                }

                $filled = array_filter($values, fn ($v) => $v !== '');
                $promedio = count($filled) > 0
                    ? number_format(array_sum($filled) / count($filled), 1, '.', '')
                    : '';

                fputcsv($handle, [
                    $tenant->id,
                    $tenant->student_name,
                    $tenant->company_name,
                    ...$values,
                    $promedio,
                ]);
            }

            fclose($handle);
        }, 200, $headers);
    }
}
