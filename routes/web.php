<?php

use App\Http\Controllers\Coordinator\AuditController as CoordinatorAudit;
use App\Http\Controllers\Student\AuthController as StudentAuth;
use App\Http\Controllers\Student\ReferenceController;
use App\Http\Controllers\Teacher\AuditController;
use App\Http\Controllers\Teacher\BulkTemplateController;
use App\Http\Controllers\Teacher\DemoController;
use App\Http\Controllers\Tenant\ActivosFijosPdfController;
use App\Http\Controllers\Tenant\BancoPdfController;
use App\Http\Controllers\Tenant\ConciliacionPdfController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboard;
use App\Http\Controllers\Tenant\FacturacionElectronica\FacturacionElectronicaController;
use App\Http\Controllers\Tenant\FacturacionElectronica\FeResolucionController;
use App\Http\Controllers\Tenant\ReportPdfController;
use App\Models\Tenant\FeFactura;
use App\Models\Tenant\FeResolucion;
use Illuminate\Http\Request;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\SecurityLogs as AdminSecurityLogs;
use App\Livewire\Admin\TransferRequests as AdminTransferRequests;
use App\Livewire\Coordinator\Dashboard as CoordinatorDashboard;
use App\Livewire\Student\Referencias as StudentReferencias;
use App\Livewire\Teacher\Announcements as TeacherAnnouncements;
use App\Livewire\Teacher\Comparativo as TeacherComparativo;
use App\Livewire\Teacher\Dashboard as TeacherDashboard;
use App\Livewire\Teacher\DemoCompanies as TeacherDemoCompanies;
use App\Livewire\Teacher\Rubrica as TeacherRubrica;
use App\Livewire\Teacher\StudentSearch as TeacherStudentSearch;
use App\Livewire\Tenant\ActivosFijos\Index as ActivosFijosIndex;
use App\Livewire\Tenant\Calendario\Index as CalendarioIndex;
use App\Livewire\Tenant\Compras\Index as ComprasIndex;
use App\Livewire\Tenant\Conciliacion\Index as ConciliacionIndex;
use App\Livewire\Tenant\Config\CompanyConfig;
use App\Livewire\Tenant\Invoices\Index as InvoicesIndex;
use App\Livewire\Tenant\PlanDeCuentas;
use App\Livewire\Tenant\Productos\Index as ProductosIndex;
use App\Livewire\Tenant\Reportes\Index as ReportesIndex;
use App\Livewire\Teacher\Negocios\Index as TeacherNegociosIndex;
use App\Livewire\Tenant\Banco\Index as BancoIndex;
use App\Livewire\Tenant\Negocios\Index as NegociosIndex;
use App\Livewire\Tenant\Terceros\Index as TercerosIndex;
use App\Http\Controllers\SandboxController;
use Illuminate\Support\Facades\Route;

// ─── Página principal ──────────────────────────────────────────────────────
Route::get('/', function () {
    if (auth('student')->check()) {
        return redirect()->route('student.dashboard');
    }

    if (auth('web')->check()) {
        $user = auth('web')->user();

        return match ($user->role->value) {
            'superadmin' => redirect()->route('admin.dashboard'),
            'coordinator' => redirect()->route('coordinator.dashboard'),
            default => redirect()->route('teacher.dashboard'),
        };
    }

    return view('welcome');
});

// ─── Rutas de Breeze (superadmin y docente usan el guard web) ──────────────
require __DIR__.'/auth.php';

// ─── Panel Superadmin ──────────────────────────────────────────────────────
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', AdminDashboard::class)->name('dashboard');
    Route::get('/transferencias', AdminTransferRequests::class)->name('transferencias');
    Route::get('/seguridad', AdminSecurityLogs::class)->name('seguridad');
});

// ─── Panel Coordinador ────────────────────────────────────────────────────
Route::middleware(['auth', 'role:coordinator'])->prefix('coordinador')->name('coordinator.')->group(function () {
    Route::get('/dashboard', CoordinatorDashboard::class)->name('dashboard');
    Route::get('/negocios', TeacherNegociosIndex::class)->name('negocios');
    Route::get('/plantilla-estudiantes', BulkTemplateController::class)->name('plantilla');

    // Iniciar / detener auditoría
    Route::get('/auditar/salir', [CoordinatorAudit::class, 'stop'])->name('auditar.stop');
    Route::get('/auditar/{tenantId}', [CoordinatorAudit::class, 'start'])->name('auditar.start');

    // Módulos del estudiante en modo auditoría (solo lectura)
    Route::prefix('auditoria/{tenantId}')->name('auditoria.')->group(function () {
        Route::get('/dashboard', [CoordinatorAudit::class, 'dashboard'])->name('dashboard');
        Route::get('/configuracion', CompanyConfig::class)->name('config');
        Route::get('/cuentas', PlanDeCuentas::class)->name('cuentas');
        Route::get('/terceros', TercerosIndex::class)->name('terceros');
        Route::get('/productos', ProductosIndex::class)->name('productos');
        Route::get('/facturas', InvoicesIndex::class)->name('facturas');
        Route::get('/compras', ComprasIndex::class)->name('compras');
        Route::get('/reportes', ReportesIndex::class)->name('reportes');
        Route::get('/reportes/pdf', ReportPdfController::class)->name('reportes.pdf');
        Route::get('/activos-fijos', ActivosFijosIndex::class)->name('activos-fijos');
        Route::get('/conciliacion-bancaria', ConciliacionIndex::class)->name('conciliacion');
        Route::get('/banco', BancoIndex::class)->name('banco');
        Route::get('/banco/documento/pdf', BancoPdfController::class)->name('banco.documento.pdf');
    });
});

// ─── Panel Docente ─────────────────────────────────────────────────────────
Route::middleware(['auth', 'role:teacher'])->prefix('docente')->name('teacher.')->group(function () {
    Route::get('/dashboard', TeacherDashboard::class)->name('dashboard');
    Route::get('/negocios', TeacherNegociosIndex::class)->name('negocios');
    Route::get('/buscar-estudiante', TeacherStudentSearch::class)->name('buscar-estudiante');
    Route::get('/plantilla-estudiantes', BulkTemplateController::class)->name('plantilla');
    Route::get('/comparativo', TeacherComparativo::class)->name('comparativo');
    Route::get('/anuncios', TeacherAnnouncements::class)->name('announcements');
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
        Route::get('/banco', BancoIndex::class)->name('banco');
        Route::get('/banco/documento/pdf', BancoPdfController::class)->name('banco.documento.pdf');
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
        Route::get('/activos-fijos/pdf', ActivosFijosPdfController::class)->name('activos-fijos.pdf');
        Route::get('/conciliacion-bancaria', ConciliacionIndex::class)->name('conciliacion');
        Route::get('/conciliacion-bancaria/pdf', ConciliacionPdfController::class)->name('conciliacion.pdf');
        Route::get('/banco', BancoIndex::class)->name('banco');
        Route::get('/banco/documento/pdf', BancoPdfController::class)->name('banco.documento.pdf');

        // Facturación Electrónica Simulada
        // Usamos closures para absorber {demoId} del prefijo y evitar que Laravel lo inyecte
        // posicionalmente en los métodos del controlador antes que el modelo FeFactura.
        Route::prefix('facturacion-electronica')->name('fe.')->group(function () {
            Route::get('/', fn (string $demoId) =>
                app(FacturacionElectronicaController::class)->index()
            )->name('index');

            Route::get('/crear', fn (string $demoId) =>
                app(FacturacionElectronicaController::class)->crear()
            )->name('crear');

            Route::post('/', fn (Request $request, string $demoId) =>
                app(FacturacionElectronicaController::class)->store($request)
            )->name('store');

            // Resoluciones (expandido desde resource para poder usar closures)
            Route::get('/resoluciones', fn (string $demoId) =>
                app(FeResolucionController::class)->index()
            )->name('resoluciones.index');

            Route::get('/resoluciones/create', fn (string $demoId) =>
                app(FeResolucionController::class)->create()
            )->name('resoluciones.create');

            Route::post('/resoluciones', fn (Request $request, string $demoId) =>
                app(FeResolucionController::class)->store($request)
            )->name('resoluciones.store');

            Route::get('/resoluciones/{resolucion}', fn (string $demoId, FeResolucion $resolucion) =>
                app(FeResolucionController::class)->show($resolucion)
            )->name('resoluciones.show');

            Route::get('/resoluciones/{resolucion}/edit', fn (string $demoId, FeResolucion $resolucion) =>
                app(FeResolucionController::class)->edit($resolucion)
            )->name('resoluciones.edit');

            Route::match(['put', 'patch'], '/resoluciones/{resolucion}', fn (Request $request, string $demoId, FeResolucion $resolucion) =>
                app(FeResolucionController::class)->update($request, $resolucion)
            )->name('resoluciones.update');

            // Facturas — {factura} va DESPUÉS de {demoId} en la firma del closure
            Route::get('/{factura}', fn (string $demoId, FeFactura $factura) =>
                app(FacturacionElectronicaController::class)->show($factura)
            )->name('show');

            Route::get('/{factura}/editar', fn (string $demoId, FeFactura $factura) =>
                app(FacturacionElectronicaController::class)->edit($factura)
            )->name('edit');

            Route::put('/{factura}', fn (Request $request, string $demoId, FeFactura $factura) =>
                app(FacturacionElectronicaController::class)->update($request, $factura)
            )->name('update');

            Route::delete('/{factura}', fn (string $demoId, FeFactura $factura) =>
                app(FacturacionElectronicaController::class)->destroy($factura)
            )->name('destroy');

            Route::post('/{factura}/emitir', fn (string $demoId, FeFactura $factura) =>
                app(FacturacionElectronicaController::class)->emitir($factura)
            )->name('emitir');

            Route::post('/{factura}/reenviar', fn (string $demoId, FeFactura $factura) =>
                app(FacturacionElectronicaController::class)->reenviar($factura)
            )->name('reenviar');

            Route::post('/{factura}/anular', fn (Request $request, string $demoId, FeFactura $factura) =>
                app(FacturacionElectronicaController::class)->anular($request, $factura)
            )->name('anular');

            Route::get('/{factura}/xml', fn (string $demoId, FeFactura $factura) =>
                app(FacturacionElectronicaController::class)->verXml($factura)
            )->name('xml');

            Route::get('/{factura}/representacion', fn (string $demoId, FeFactura $factura) =>
                app(FacturacionElectronicaController::class)->representacion($factura)
            )->name('representacion');

            Route::get('/{factura}/pdf', fn (string $demoId, FeFactura $factura) =>
                app(FacturacionElectronicaController::class)->pdf($factura)
            )->name('pdf');

            Route::post('/{factura}/eventos', fn (Request $request, string $demoId, FeFactura $factura) =>
                app(FacturacionElectronicaController::class)->registrarEvento($request, $factura)
            )->name('eventos.store');
        });
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

        // Fase 4b — Mercado interempresarial
        Route::get('/negocios', NegociosIndex::class)->name('negocios');

        // Fase 5 — Reportes contables
        Route::get('/reportes', ReportesIndex::class)->name('reportes');
        Route::get('/reportes/pdf', ReportPdfController::class)->name('reportes.pdf');
        Route::get('/calendario-tributario', CalendarioIndex::class)->name('calendario');
        Route::get('/activos-fijos', ActivosFijosIndex::class)->name('activos-fijos');
        Route::get('/activos-fijos/pdf', ActivosFijosPdfController::class)->name('activos-fijos.pdf');
        Route::get('/conciliacion-bancaria', ConciliacionIndex::class)->name('conciliacion');
        Route::get('/conciliacion-bancaria/pdf', ConciliacionPdfController::class)->name('conciliacion.pdf');
        Route::get('/banco', BancoIndex::class)->name('banco');
        Route::get('/banco/documento/pdf', BancoPdfController::class)->name('banco.documento.pdf');

        // PUC Interactivo (referencia rápida desde la zona real)
        Route::get('/puc', \App\Livewire\Tenant\PucInteractivo::class)->name('puc');

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
            Route::get('/banco', BancoIndex::class)->name('banco');
            Route::get('/banco/documento/pdf', BancoPdfController::class)->name('banco.documento.pdf');
        });

        // Fase 6 — Facturación Electrónica Simulada
        Route::prefix('facturacion-electronica')->name('fe.')->group(function () {
            Route::get('/', [FacturacionElectronicaController::class, 'index'])->name('index');
            Route::get('/crear', [FacturacionElectronicaController::class, 'crear'])->name('crear');
            Route::post('/', [FacturacionElectronicaController::class, 'store'])->name('store');
            // Resoluciones primero para evitar conflicto con el wildcard /{factura}
            Route::resource('resoluciones', FeResolucionController::class)->except('destroy')->parameters(['resoluciones' => 'resolucion']);
            Route::get('/{factura}', [FacturacionElectronicaController::class, 'show'])->name('show');
            Route::get('/{factura}/editar', [FacturacionElectronicaController::class, 'edit'])->name('edit');
            Route::put('/{factura}', [FacturacionElectronicaController::class, 'update'])->name('update');
            Route::delete('/{factura}', [FacturacionElectronicaController::class, 'destroy'])->name('destroy');
            Route::post('/{factura}/emitir', [FacturacionElectronicaController::class, 'emitir'])->name('emitir');
            Route::post('/{factura}/reenviar', [FacturacionElectronicaController::class, 'reenviar'])->name('reenviar');
            Route::post('/{factura}/anular', [FacturacionElectronicaController::class, 'anular'])->name('anular');
            Route::get('/{factura}/xml', [FacturacionElectronicaController::class, 'verXml'])->name('xml');
            Route::get('/{factura}/representacion', [FacturacionElectronicaController::class, 'representacion'])->name('representacion');
            Route::get('/{factura}/pdf', [FacturacionElectronicaController::class, 'pdf'])->name('pdf');
            Route::post('/{factura}/eventos', [FacturacionElectronicaController::class, 'registrarEvento'])->name('eventos.store');
        });
    });

// ─── Zona Aprendizaje (Sandbox) ─────────────────────────────────────────────
Route::middleware(['auth:student', 'tenant.initialize'])
    ->prefix('aprendizaje')
    ->name('sandbox.')
    ->group(function () {

        Route::get('/dashboard', [TenantDashboard::class, 'index'])->name('dashboard');

        // Maestros contables
        Route::get('/configuracion', CompanyConfig::class)->name('config');
        Route::get('/cuentas', PlanDeCuentas::class)->name('cuentas');
        Route::get('/terceros', TercerosIndex::class)->name('terceros');
        Route::get('/productos', ProductosIndex::class)->name('productos');

        // Operaciones
        Route::get('/facturas', InvoicesIndex::class)->name('facturas');
        Route::get('/compras', ComprasIndex::class)->name('compras');

        // Reportes y herramientas
        Route::get('/reportes', ReportesIndex::class)->name('reportes');
        Route::get('/reportes/pdf', ReportPdfController::class)->name('reportes.pdf');
        Route::get('/calendario-tributario', CalendarioIndex::class)->name('calendario');
        Route::get('/activos-fijos', ActivosFijosIndex::class)->name('activos-fijos');
        Route::get('/activos-fijos/pdf', ActivosFijosPdfController::class)->name('activos-fijos.pdf');
        Route::get('/conciliacion-bancaria', ConciliacionIndex::class)->name('conciliacion');
        Route::get('/conciliacion-bancaria/pdf', ConciliacionPdfController::class)->name('conciliacion.pdf');

        // Facturación electrónica simulada
        Route::prefix('facturacion-electronica')->name('fe.')->group(function () {
            Route::get('/', [FacturacionElectronicaController::class, 'index'])->name('index');
            Route::get('/crear', [FacturacionElectronicaController::class, 'crear'])->name('crear');
            Route::post('/', [FacturacionElectronicaController::class, 'store'])->name('store');
            Route::resource('resoluciones', FeResolucionController::class)
                ->except('destroy')
                ->parameters(['resoluciones' => 'resolucion']);
            Route::get('/{factura}', [FacturacionElectronicaController::class, 'show'])->name('show');
            Route::get('/{factura}/editar', [FacturacionElectronicaController::class, 'edit'])->name('edit');
            Route::put('/{factura}', [FacturacionElectronicaController::class, 'update'])->name('update');
            Route::delete('/{factura}', [FacturacionElectronicaController::class, 'destroy'])->name('destroy');
            Route::post('/{factura}/emitir', [FacturacionElectronicaController::class, 'emitir'])->name('emitir');
            Route::post('/{factura}/anular', [FacturacionElectronicaController::class, 'anular'])->name('anular');
            Route::get('/{factura}/pdf', [FacturacionElectronicaController::class, 'pdf'])->name('pdf');
        });

        // PUC Interactivo
        Route::get('/puc', \App\Livewire\Tenant\PucInteractivo::class)->name('puc');

        // Reset sandbox
        Route::post('/reset', [SandboxController::class, 'reset'])->name('reset');
    });
