<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Announcement;
use App\Models\Central\StudentScore;
use App\Models\Tenant\BankAccount;
use App\Models\Tenant\CompanyConfig;
use App\Models\Tenant\CompanySummary;
use App\Models\Tenant\Product;
use App\Models\Tenant\Third;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $student = tenancy()->tenant;
        $centralConn = config('tenancy.database.central_connection', 'pgsql');

        // KPIs del resumen materializado
        $summary = CompanySummary::first();

        $modo = modoContable();

        $porCobrar = (float) DB::table('journal_lines')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('accounts.code', 'like', '1305%')
            ->where('journal_entries.modo', $modo)
            ->whereNull('journal_entries.deleted_at')
            ->selectRaw('COALESCE(SUM(debit) - SUM(credit), 0) as saldo')
            ->value('saldo');

        $porPagar = (float) DB::table('journal_lines')
            ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
            ->join('journal_entries', 'journal_entries.id', '=', 'journal_lines.journal_entry_id')
            ->where('accounts.code', 'like', '2205%')
            ->where('journal_entries.modo', $modo)
            ->whereNull('journal_entries.deleted_at')
            ->selectRaw('COALESCE(SUM(credit) - SUM(debit), 0) as saldo')
            ->value('saldo');

        $saldoBancario = $modo === 'real'
            ? (float) BankAccount::where('activa', true)->sum('saldo')
            : null;

        // Indicador de progreso — 6 hitos del ciclo contable
        $hasConfig = CompanyConfig::whereNotNull('razon_social')
            ->where('razon_social', '!=', '')
            ->exists();

        // Las rutas del progreso se resuelven en la vista usando el array $r (que depende del modo activo).
        // Aquí solo guardamos la clave de módulo.
        $progress = [
            ['label' => 'Configuración de empresa', 'done' => $hasConfig, 'key' => 'config'],
            ['label' => 'Terceros registrados', 'done' => Third::modoActual()->exists(), 'key' => 'terceros'],
            ['label' => 'Productos registrados', 'done' => Product::modoActual()->exists(), 'key' => 'productos'],
            ['label' => 'Facturas de venta emitidas', 'done' => ($summary?->total_facturas_venta ?? 0) > 0, 'key' => 'facturas'],
            ['label' => 'Compras registradas', 'done' => ($summary?->total_facturas_compra ?? 0) > 0, 'key' => 'compras'],
            ['label' => 'Balance cuadrado', 'done' => $summary?->balance_cuadrado ?? false, 'key' => 'reportes'],
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
                ->current()
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
            'porCobrar',
            'porPagar',
            'saldoBancario',
        ));
    }
}
