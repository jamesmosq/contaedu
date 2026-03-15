<?php

use App\Http\Controllers\Student\AuthController as StudentAuth;
use App\Http\Controllers\Teacher\AuditController;
use App\Http\Controllers\Teacher\BulkTemplateController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboard;
use App\Http\Controllers\Tenant\ReportPdfController;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Teacher\Comparativo as TeacherComparativo;
use App\Livewire\Teacher\Dashboard as TeacherDashboard;
use App\Livewire\Teacher\Rubrica as TeacherRubrica;
use App\Livewire\Tenant\Compras\Index as ComprasIndex;
use App\Livewire\Tenant\Config\CompanyConfig;
use App\Livewire\Tenant\Invoices\Index as InvoicesIndex;
use App\Livewire\Tenant\PlanDeCuentas;
use App\Livewire\Tenant\Productos\Index as ProductosIndex;
use App\Livewire\Tenant\Reportes\Index as ReportesIndex;
use App\Livewire\Tenant\Terceros\Index as TercerosIndex;
use Illuminate\Support\Facades\Route;

// ─── Página principal ──────────────────────────────────────────────────────
Route::get('/', function () {
    return view('welcome');
});

// ─── Rutas de Breeze (superadmin y docente usan el guard web) ──────────────
require __DIR__.'/auth.php';

// ─── Panel Superadmin ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
});

// ─── Panel Docente ─────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:teacher'])->prefix('docente')->name('teacher.')->group(function () {
    Route::get('/dashboard', TeacherDashboard::class)->name('dashboard');
    Route::get('/plantilla-estudiantes', BulkTemplateController::class)->name('plantilla');
    Route::get('/comparativo', TeacherComparativo::class)->name('comparativo');
    Route::get('/rubrica/{tenantId}', TeacherRubrica::class)->name('rubrica');

    // Iniciar / detener modo auditoría (salir ANTES del wildcard)
    Route::get('/auditar/salir', [AuditController::class, 'stop'])->name('auditar.stop');
    Route::get('/auditar/{tenantId}', [AuditController::class, 'start'])->name('auditar.start');

    // Rutas de auditoría: docente navega módulos del estudiante en modo solo lectura
    Route::prefix('auditoria/{tenantId}')->name('auditoria.')->group(function () {
        Route::get('/dashboard', [AuditController::class, 'dashboard'])->name('dashboard');
        Route::get('/configuracion', CompanyConfig::class)->name('config');
        Route::get('/cuentas', PlanDeCuentas::class)->name('cuentas');
        Route::get('/terceros', TercerosIndex::class)->name('terceros');
        Route::get('/productos', ProductosIndex::class)->name('productos');
        Route::get('/facturas', InvoicesIndex::class)->name('facturas');
        Route::get('/compras', ComprasIndex::class)->name('compras');
        Route::get('/reportes', ReportesIndex::class)->name('reportes');
        Route::get('/reportes/pdf', ReportPdfController::class)->name('reportes.pdf');
    });
});

// ─── Autenticación del Estudiante ──────────────────────────────────────────
Route::middleware('guest:student')->prefix('estudiante')->name('student.')->group(function () {
    Route::get('/login', [StudentAuth::class, 'showLogin'])->name('login');
    Route::post('/login', [StudentAuth::class, 'login']);
});

Route::post('/estudiante/logout', [StudentAuth::class, 'logout'])
    ->middleware('auth:student')
    ->name('student.logout');

// ─── Panel del Estudiante (tenant) ─────────────────────────────────────────
Route::middleware(['auth:student', 'tenant.initialize'])
    ->prefix('empresa')
    ->name('student.')
    ->group(function () {
        Route::get('/dashboard', [TenantDashboard::class, 'index'])->name('dashboard');

        // Fase 2 — Maestros contables
        Route::get('/configuracion', CompanyConfig::class)->name('config');
        Route::get('/cuentas', PlanDeCuentas::class)->name('cuentas');
        Route::get('/terceros', TercerosIndex::class)->name('terceros');
        Route::get('/productos', ProductosIndex::class)->name('productos');

        // Fase 3 — Operaciones de venta
        Route::get('/facturas', InvoicesIndex::class)->name('facturas');

        // Fase 4 — Operaciones de compra
        Route::get('/compras', ComprasIndex::class)->name('compras');

        // Fase 5 — Reportes contables
        Route::get('/reportes', ReportesIndex::class)->name('reportes');
        Route::get('/reportes/pdf', ReportPdfController::class)->name('reportes.pdf');
    });
