<?php

use App\Http\Middleware\InitializeTenancyByStudent;
use App\Models\Central\Group;
use App\Models\Central\Institution;
use App\Models\Central\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Events\TenantCreated;

// ─── Helper para crear un tenant de prueba ──────────────────────────────────

function createTestTenant(string $id = 'cc1023456789', string $password = 'password123'): Tenant
{
    // Evita que el evento TenantCreated dispare la creación del schema PostgreSQL
    Event::fake([TenantCreated::class]);

    $institution = Institution::create(['name' => 'IED Prueba', 'nit' => '800100200-1', 'city' => 'Bogotá', 'active' => true]);
    $teacher     = User::factory()->create(['role' => 'teacher']);
    $group       = Group::create([
        'institution_id' => $institution->id,
        'teacher_id'     => $teacher->id,
        'name'           => 'Grupo 2025',
        'period'         => '2025-1',
        'active'         => true,
    ]);

    return Tenant::create([
        'id'              => $id,
        'group_id'        => $group->id,
        'student_name'    => 'Juan Pérez',
        'company_name'    => 'Empresa Prueba SAS',
        'nit_empresa'     => '900123456-1',
        'password'        => Hash::make($password),
        'tenancy_db_name' => 'tenant' . $id,
        'active'          => true,
    ]);
}

// ─── Login del estudiante ───────────────────────────────────────────────────

it('estudiante puede hacer login con cédula y contraseña correctas', function () {
    $tenant = createTestTenant();

    $this->withoutMiddleware(InitializeTenancyByStudent::class)
        ->post('/estudiante/login', [
            '_token'   => csrf_token(),
            'cedula'   => 'cc1023456789',
            'password' => 'password123',
        ])
        ->assertRedirect(route('student.dashboard'));

    $this->assertAuthenticatedAs($tenant, 'student');
});

it('estudiante no puede hacer login con contraseña incorrecta', function () {
    createTestTenant();

    $this->withoutMiddleware(InitializeTenancyByStudent::class)
        ->post('/estudiante/login', [
            '_token'   => csrf_token(),
            'cedula'   => 'cc1023456789',
            'password' => 'wrong-password',
        ])
        ->assertSessionHasErrors('cedula');

    $this->assertGuest('student');
});

it('login de estudiante requiere campo cédula', function () {
    $this->withoutMiddleware(InitializeTenancyByStudent::class)
        ->post('/estudiante/login', [
            '_token'   => csrf_token(),
            'password' => 'password123',
        ])
        ->assertSessionHasErrors('cedula');
});

it('login de estudiante requiere campo password', function () {
    $this->withoutMiddleware(InitializeTenancyByStudent::class)
        ->post('/estudiante/login', [
            '_token'  => csrf_token(),
            'cedula'  => 'cc1023456789',
        ])
        ->assertSessionHasErrors('password');
});

it('estudiante autenticado puede hacer logout', function () {
    $tenant = createTestTenant();

    $this->withoutMiddleware(InitializeTenancyByStudent::class)
        ->actingAs($tenant, 'student')
        ->post('/estudiante/logout', ['_token' => csrf_token()])
        ->assertRedirect(route('student.login'));

    $this->assertGuest('student');
});

it('logout de estudiante requiere autenticación previa', function () {
    $this->withoutMiddleware(InitializeTenancyByStudent::class)
        ->post('/estudiante/logout', ['_token' => csrf_token()])
        ->assertRedirect();
});

it('GET /estudiante/login redirige si el estudiante ya está autenticado', function () {
    $tenant = createTestTenant();

    $this->withoutMiddleware(InitializeTenancyByStudent::class)
        ->actingAs($tenant, 'student')
        ->get('/estudiante/login')
        ->assertRedirect();
});
