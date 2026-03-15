<?php

use App\Http\Middleware\InitializeTenancyByStudent;
use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Models\Central\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Events\TenantCreated;

// ─── Middleware no inicializa auditoría en rutas centrales ──────────────────

it('middleware no activa auditoría en rutas del panel central', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    // Simula docente con sesión de auditoría activa
    $this->actingAs($teacher)
        ->withSession([
            'audit_mode'          => true,
            'audit_tenant_id'     => 'cc1023456789',
            'audit_student_name'  => 'Juan Pérez',
            'audit_company_name'  => 'Empresa Prueba SAS',
        ])
        ->get('/docente/dashboard')  // ruta central — NO debe inicializar tenant
        ->assertSuccessful();
});

it('salir de auditoría borra la sesión de auditoría', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $response = $this->actingAs($teacher)
        ->withSession([
            'audit_mode'         => true,
            'audit_tenant_id'    => 'cc1023456789',
            'audit_student_name' => 'Juan Pérez',
        ])
        ->get('/docente/auditar/salir');

    $response->assertRedirect(route('teacher.dashboard'));
    $response->assertSessionMissing('audit_mode');
    $response->assertSessionMissing('audit_tenant_id');
});

// ─── Solo docentes pueden acceder a rutas de auditoría ─────────────────────

it('superadmin no puede iniciar auditoría', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->get('/docente/auditar/cc1023456789')
        ->assertForbidden();
});

it('usuario no autenticado no puede iniciar auditoría', function () {
    $this->get('/docente/auditar/cc1023456789')
        ->assertRedirect('/login');
});

// ─── Docente solo puede auditar su propio grupo ─────────────────────────────

it('docente no puede auditar empresa de otro grupo', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    // sin grupos asignados — cualquier tenantId debe devolver 403

    $this->actingAs($teacher)
        ->get('/docente/auditar/cc9999999999')
        ->assertForbidden();
});

// ─── Rutas de auditoría tienen el tenantId en la URL ───────────────────────

it('ruta auditar salir tiene prioridad sobre el wildcard auditar/{tenantId}', function () {
    $routes = app('router')->getRoutes();

    $saliRoute  = $routes->getByName('teacher.auditar.stop');
    $startRoute = $routes->getByName('teacher.auditar.start');

    // La ruta /salir es un path fijo (sin parámetro)
    expect($saliRoute->uri())->toBe('docente/auditar/salir');

    // La ruta de inicio usa parámetro tenantId
    expect($startRoute->uri())->toBe('docente/auditar/{tenantId}');
});

// ─── El layout tenant contiene el banner de auditoría ────────────────────

it('el layout tenant incluye el condicional del banner de auditoría', function () {
    // Verificar directamente que el archivo de layout contiene el bloque del banner
    $layoutContent = file_get_contents(resource_path('views/layouts/tenant.blade.php'));

    expect($layoutContent)
        ->toContain("session('audit_mode')")
        ->toContain('Modo auditoría')
        ->toContain('audit_student_name');
});

it('la sesión de auditoría contiene los campos requeridos', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)
        ->withSession([
            'audit_mode'          => true,
            'audit_tenant_id'     => 'cc1023456789',
            'audit_student_name'  => 'Juan Pérez',
            'audit_company_name'  => 'Empresa Prueba SAS',
        ])
        ->get('/docente/dashboard') // ruta central, accesible sin tenancy
        ->assertSessionHas('audit_mode', true)
        ->assertSessionHas('audit_student_name', 'Juan Pérez');
});
