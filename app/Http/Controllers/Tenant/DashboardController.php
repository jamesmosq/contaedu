<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Announcement;
use App\Models\Central\StudentScore;
use App\Models\Tenant\CompanyConfig;
use App\Models\Tenant\CompanySummary;
use App\Models\Tenant\Product;
use App\Models\Tenant\Third;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $student = tenancy()->tenant;
        $centralConn = config('tenancy.database.central_connection', 'pgsql');

        // KPIs del resumen materializado
        $summary = CompanySummary::first();

        // Indicador de progreso — 6 hitos del ciclo contable
        $hasConfig = CompanyConfig::whereNotNull('razon_social')
            ->where('razon_social', '!=', '')
            ->exists();

        $progress = [
            ['label' => 'Configuración de empresa', 'done' => $hasConfig, 'route' => 'student.config'],
            ['label' => 'Terceros registrados', 'done' => Third::exists(), 'route' => 'student.terceros'],
            ['label' => 'Productos registrados', 'done' => Product::exists(), 'route' => 'student.productos'],
            ['label' => 'Facturas de venta emitidas', 'done' => ($summary?->total_facturas_venta ?? 0) > 0, 'route' => 'student.facturas'],
            ['label' => 'Compras registradas', 'done' => ($summary?->total_facturas_compra ?? 0) > 0, 'route' => 'student.compras'],
            ['label' => 'Balance cuadrado', 'done' => $summary?->balance_cuadrado ?? false, 'route' => 'student.reportes'],
        ];

        $completedCount = collect($progress)->where('done', true)->count();

        // Solo cargar anuncios y notas para empresas de estudiantes reales con grupo asignado
        $isRealStudent = ! session('audit_mode')
            && ! session('demo_mode')
            && ! session('reference_mode')
            && $student?->group_id;

        $announcements = collect();
        $scores = collect();

        if ($isRealStudent) {
            $announcements = Announcement::on($centralConn)
                ->where('group_id', $student->group_id)
                ->where('active', true)
                ->orderByDesc('created_at')
                ->take(5)
                ->get();

            $scores = StudentScore::on($centralConn)
                ->where('tenant_id', $student->id)
                ->orderBy('module')
                ->get()
                ->keyBy('module');
        }

        return view('tenant.dashboard', compact(
            'student',
            'summary',
            'progress',
            'completedCount',
            'announcements',
            'scores',
        ));
    }
}
