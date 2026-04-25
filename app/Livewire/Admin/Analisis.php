<?php

namespace App\Livewire\Admin;

use App\Models\Central\Institution;
use App\Models\Central\SessionLog;
use App\Models\Central\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Análisis')]
class Analisis extends Component
{
    public int $institutionId = 0;

    public int $period = 30;

    public function render(): mixed
    {
        $institutions = Institution::orderBy('name')->get();
        $data = $this->institutionId ? $this->buildData() : null;

        return view('livewire.admin.analisis', compact('institutions', 'data'));
    }

    private function buildData(): array
    {
        $instId = $this->institutionId;
        $days = $this->period;

        $institution = Institution::with('coordinator')->find($instId);

        // IDs de estudiantes de la institución
        $studentIds = Tenant::where('type', 'student')
            ->whereHas('group', fn ($q) => $q->where('institution_id', $instId))
            ->pluck('id');

        $totalStudents = $studentIds->count();

        // ── Métricas de sesión (estudiantes) ──────────────────────────────
        $sessions = SessionLog::students()
            ->forInstitution($instId)
            ->period($days)
            ->completed()
            ->get();

        $totalSessions = $sessions->count();
        $uniqueStudents = $sessions->pluck('user_id')->unique()->count();
        $avgDuration = $totalSessions > 0 ? round($sessions->avg('duration_minutes')) : 0;
        $totalHours = round($sessions->sum('duration_minutes') / 60, 1);
        $activationRate = $totalStudents > 0 ? round(($uniqueStudents / $totalStudents) * 100) : 0;

        // Sesiones por semana (últimas N semanas según período)
        $weeks = (int) ceil($days / 7);
        $sessionsByWeek = SessionLog::students()
            ->forInstitution($instId)
            ->period($days)
            ->selectRaw("DATE_TRUNC('week', started_at) as semana, COUNT(*) as sesiones, AVG(duration_minutes) as avg_min")
            ->groupBy('semana')
            ->orderBy('semana')
            ->get()
            ->map(fn ($r) => [
                'semana' => Carbon::parse($r->semana)->format('d/m'),
                'sesiones' => (int) $r->sesiones,
                'avg_min' => (int) $r->avg_min,
            ]);

        // Distribución de duración
        $durDist = [
            'muy_corta' => $sessions->where('duration_minutes', '<', 5)->count(),
            'corta' => $sessions->whereBetween('duration_minutes', [5, 20])->count(),
            'media' => $sessions->whereBetween('duration_minutes', [20, 60])->count(),
            'larga' => $sessions->where('duration_minutes', '>', 60)->count(),
        ];

        // Distribución horaria (hora del día con más actividad)
        $horasPico = SessionLog::students()
            ->forInstitution($instId)
            ->period($days)
            ->selectRaw('EXTRACT(HOUR FROM started_at) as hora, COUNT(*) as total')
            ->groupBy('hora')
            ->orderBy('hora')
            ->pluck('total', 'hora')
            ->toArray();

        // Estudiantes que nunca han iniciado sesión
        $conSesion = SessionLog::students()->forInstitution($instId)->pluck('user_id')->unique();
        $sinSesion = $studentIds->diff($conSesion);
        $sinSesionInfo = Tenant::whereIn('id', $sinSesion->take(10))
            ->select('id', 'student_name', 'company_name', 'group_id')
            ->get();

        // Inactivos en el período
        $conSesionPeriodo = SessionLog::students()
            ->forInstitution($instId)
            ->period($days)
            ->pluck('user_id')
            ->unique();
        $inactivos = $studentIds->diff($conSesionPeriodo)->count();

        // Métricas de docentes de la institución
        $teacherSessions = SessionLog::teachers()
            ->forInstitution($instId)
            ->period($days)
            ->completed()
            ->get();

        $teacherStats = [
            'total_sesiones' => $teacherSessions->count(),
            'docentes_activos' => $teacherSessions->pluck('user_id')->unique()->count(),
            'avg_duracion' => $teacherSessions->count() > 0 ? round($teacherSessions->avg('duration_minutes')) : 0,
            'total_horas' => round($teacherSessions->sum('duration_minutes') / 60, 1),
        ];

        // Datos operacionales (facturas + compras + asientos)
        $opsData = $this->buildOpsData($studentIds, $days);

        return compact(
            'institution',
            'totalStudents',
            'totalSessions',
            'uniqueStudents',
            'avgDuration',
            'totalHours',
            'activationRate',
            'sessionsByWeek',
            'durDist',
            'horasPico',
            'sinSesionInfo',
            'inactivos',
            'teacherStats',
            'opsData',
        );
    }

    private function buildOpsData(Collection $studentIds, int $days): array
    {
        if ($studentIds->isEmpty()) {
            return ['facturas' => 0, 'compras' => 0, 'asientos' => 0, 'total' => 0, 'por_estudiante' => 0];
        }

        $makeUnion = fn (string $table) => $studentIds
            ->map(fn ($id) => "SELECT COUNT(*) as n FROM \"tenant_{$id}\".{$table} WHERE created_at >= NOW() - INTERVAL '{$days} days'")
            ->join(' UNION ALL ');

        $facturas = (int) DB::selectOne('SELECT SUM(n) as t FROM ('.$makeUnion('invoices').') x')?->t;
        $compras = (int) DB::selectOne('SELECT SUM(n) as t FROM ('.$makeUnion('purchase_invoices').') x')?->t;
        $asientos = (int) DB::selectOne('SELECT SUM(n) as t FROM ('.$makeUnion('journal_entries').') x')?->t;
        $total = $facturas + $compras + $asientos;
        $porEstudiante = $studentIds->count() > 0 ? round($total / $studentIds->count(), 1) : 0;

        return compact('facturas', 'compras', 'asientos', 'total', 'porEstudiante');
    }
}
