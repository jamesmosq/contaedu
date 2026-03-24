<?php

use App\Http\Controllers\Student\AuthController as StudentAuth;
use App\Http\Controllers\Student\ReferenceController;
use App\Http\Controllers\Teacher\AuditController;
use App\Http\Controllers\Teacher\BulkTemplateController;
use App\Http\Controllers\Teacher\DemoController;
use App\Http\Controllers\Tenant\ActivosFijosPdfController;
use App\Http\Controllers\Tenant\ConciliacionPdfController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboard;
use App\Http\Controllers\Tenant\FacturacionElectronica\FacturacionElectronicaController;
use App\Http\Controllers\Tenant\FacturacionElectronica\FeResolucionController;
use App\Http\Controllers\Tenant\ReportPdfController;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Student\Referencias as StudentReferencias;
use App\Livewire\Teacher\Comparativo as TeacherComparativo;
use App\Livewire\Teacher\Dashboard as TeacherDashboard;
use App\Livewire\Teacher\DemoCompanies as TeacherDemoCompanies;
use App\Livewire\Teacher\Rubrica as TeacherRubrica;
use App\Livewire\Tenant\ActivosFijos\Index as ActivosFijosIndex;
use App\Livewire\Tenant\Calendario\Index as CalendarioIndex;
use App\Livewire\Tenant\Compras\Index as ComprasIndex;
use App\Livewire\Tenant\Conciliacion\Index as ConciliacionIndex;
use App\Livewire\Tenant\Config\CompanyConfig;
use App\Livewire\Tenant\Invoices\Index as InvoicesIndex;
use App\Livewire\Tenant\PlanDeCuentas;
use App\Livewire\Tenant\Productos\Index as ProductosIndex;
use App\Livewire\Tenant\Reportes\Index as ReportesIndex;
use App\Livewire\Tenant\Terceros\Index as TercerosIndex;
use Illuminate\Support\Facades\Route;

// ─── Página principal ──────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth('student')->check()) {
        return redirect()->route('student.dashboard');
    }

    if (auth('web')->check()) {
        $user = auth('web')->user();

        return $user->role === 'superadmin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('teacher.dashboard');
    }

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
        Route::get('/calendario-tributario', CalendarioIndex::class)->name('calendario');
        Route::get('/activos-fijos', ActivosFijosIndex::class)->name('activos-fijos');
        Route::get('/activos-fijos/pdf', ActivosFijosPdfController::class)->name('activos-fijos.pdf');
        Route::get('/conciliacion-bancaria', ConciliacionIndex::class)->name('conciliacion');
        Route::get('/conciliacion-bancaria/pdf', ConciliacionPdfController::class)->name('conciliacion.pdf');
    });

    // ─── Empresas de demostración del docente ──────────────────────────────
    Route::get('/demos', TeacherDemoCompanies::class)->name('demos');
    Route::get('/demo/{demoId}/salir', [DemoController::class, 'exit'])->name('demo.exit');
    Route::get('/demo/{demoId}/entrar', [DemoController::class, 'enter'])->name('demo.enter');

    // Módulos de la empresa demo (mismo Livewire que estudiante, acceso completo)
    Route::prefix('demo/{demoId}')->name('demo.')->group(function () {
        Route::get('/dashboard', [DemoController::class, 'dashboard'])->name('dashboard');
        Route::get('/configuracion', CompanyConfig::class)->name('config');
        Route::get('/cuentas', PlanDeCuentas::class)->name('cuentas');
        Route::get('/terceros', TercerosIndex::class)->name('terceros');
        Route::get('/productos', ProductosIndex::class)->name('productos');
        Route::get('/facturas', InvoicesIndex::class)->name('facturas');
        Route::get('/compras', ComprasIndex::class)->name('compras');
        Route::get('/reportes', ReportesIndex::class)->name('reportes');
        Route::get('/reportes/pdf', ReportPdfController::class)->name('reportes.pdf');
        Route::get('/calendario-tributario', CalendarioIndex::class)->name('calendario');
        Route::get('/activos-fijos', ActivosFijosIndex::class)->name('activos-fijos');
        Route::get('/conciliacion-bancaria', ConciliacionIndex::class)->name('conciliacion');
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
        Route::get('/calendario-tributario', CalendarioIndex::class)->name('calendario');
        Route::get('/activos-fijos', ActivosFijosIndex::class)->name('activos-fijos');
        Route::get('/activos-fijos/pdf', ActivosFijosPdfController::class)->name('activos-fijos.pdf');
        Route::get('/conciliacion-bancaria', ConciliacionIndex::class)->name('conciliacion');
        Route::get('/conciliacion-bancaria/pdf', ConciliacionPdfController::class)->name('conciliacion.pdf');

        // ─── Empresas de referencia (demos del docente en solo lectura) ───────
        Route::get('/referencias', StudentReferencias::class)->name('referencias');
        Route::get('/referencias/{demoId}/salir', [ReferenceController::class, 'exit'])->name('referencias.exit');
        Route::get('/referencias/{demoId}/entrar', [ReferenceController::class, 'enter'])->name('referencias.enter');

        Route::prefix('referencias/{demoId}')->name('referencia.')->group(function () {
            Route::get('/dashboard', [ReferenceController::class, 'dashboard'])->name('dashboard');
            Route::get('/configuracion', CompanyConfig::class)->name('config');
            Route::get('/cuentas', PlanDeCuentas::class)->name('cuentas');
            Route::get('/terceros', TercerosIndex::class)->name('terceros');
            Route::get('/productos', ProductosIndex::class)->name('productos');
            Route::get('/facturas', InvoicesIndex::class)->name('facturas');
            Route::get('/compras', ComprasIndex::class)->name('compras');
            Route::get('/reportes', ReportesIndex::class)->name('reportes');
            Route::get('/calendario-tributario', CalendarioIndex::class)->name('calendario');
            Route::get('/activos-fijos', ActivosFijosIndex::class)->name('activos-fijos');
            Route::get('/conciliacion-bancaria', ConciliacionIndex::class)->name('conciliacion');
        });

        // Fase 6 — Facturación Electrónica Simulada
        Route::prefix('facturacion-electronica')->name('fe.')->group(function () {
            Route::get('/', [FacturacionElectronicaController::class, 'index'])->name('index');
            Route::get('/crear', [FacturacionElectronicaController::class, 'crear'])->name('crear');
            Route::post('/', [FacturacionElectronicaController::class, 'store'])->name('store');
            // Resoluciones primero para evitar conflicto con el wildcard /{factura}
            Route::resource('resoluciones', FeResolucionController::class)->except('destroy')->parameters(['resoluciones' => 'resolucion']);
            Route::get('/{factura}', [FacturacionElectronicaController::class, 'show'])->name('show');
            Route::post('/{factura}/emitir', [FacturacionElectronicaController::class, 'emitir'])->name('emitir');
            Route::post('/{factura}/reenviar', [FacturacionElectronicaController::class, 'reenviar'])->name('reenviar');
            Route::post('/{factura}/anular', [FacturacionElectronicaController::class, 'anular'])->name('anular');
            Route::get('/{factura}/xml', [FacturacionElectronicaController::class, 'verXml'])->name('xml');
            Route::get('/{factura}/representacion', [FacturacionElectronicaController::class, 'representacion'])->name('representacion');
            Route::post('/{factura}/eventos', [FacturacionElectronicaController::class, 'registrarEvento'])->name('eventos.store');
        });
    });
