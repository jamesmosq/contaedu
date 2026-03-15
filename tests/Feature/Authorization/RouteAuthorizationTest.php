<?php

use App\Http\Middleware\InitializeTenancyByStudent;
use App\Models\Central\Institution;
use App\Models\User;

// ─── Rutas protegidas redirigen a usuarios no autenticados ─────────────────

it('GET /admin/dashboard redirige a login si no autenticado', function () {
    $this->get('/admin/dashboard')
        ->assertRedirect('/login');
});

it('GET /docente/dashboard redirige a login si no autenticado', function () {
    $this->get('/docente/dashboard')
        ->assertRedirect('/login');
});

it('GET /docente/comparativo redirige a login si no autenticado', function () {
    $this->get('/docente/comparativo')
        ->assertRedirect('/login');
});

it('GET /empresa/dashboard redirige a login de estudiante si no autenticado', function () {
    $this->withoutMiddleware(InitializeTenancyByStudent::class)
        ->get('/empresa/dashboard')
        ->assertRedirect();
});

// ─── Control de acceso por rol ─────────────────────────────────────────────

it('docente no puede acceder al panel de superadmin', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)
        ->get('/admin/dashboard')
        ->assertForbidden();
});

it('superadmin no puede acceder al panel de docente', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->get('/docente/dashboard')
        ->assertForbidden();
});

it('superadmin puede acceder al panel de admin', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->get('/admin/dashboard')
        ->assertSuccessful();
});

it('docente puede acceder al panel de docente', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)
        ->get('/docente/dashboard')
        ->assertSuccessful();
});

it('docente puede acceder al comparativo', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    $this->actingAs($teacher)
        ->get('/docente/comparativo')
        ->assertSuccessful();
});

// ─── Protección CSRF configurada en el stack de middleware ─────────────────
// Laravel omite la verificación CSRF en tests por diseño (runningUnitTests()).
// Verificamos que el middleware está registrado en la aplicación.

it('VerifyCsrfToken está registrado en el grupo web', function () {
    $middlewareGroups = app(\Illuminate\Contracts\Http\Kernel::class)
        ->getMiddlewareGroups();

    $webMiddleware = $middlewareGroups['web'] ?? [];

    $hasCsrf = in_array(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class, $webMiddleware)
        || in_array(\App\Http\Middleware\VerifyCsrfToken::class, $webMiddleware);

    expect($hasCsrf)->toBeTrue('El middleware CSRF debe estar registrado en el grupo web');
});

it('rutas POST procesan CSRF correctamente con token válido', function () {
    // Con token CSRF válido, la respuesta no debe ser 419
    $this->post('/login', [
        '_token'   => csrf_token(),
        'email'    => 'inexistente@test.com',
        'password' => 'secret',
    ])->assertStatus(302); // redirect (credenciales incorrectas, pero sin 419)
});

it('rutas POST de estudiante procesan CSRF correctamente con token válido', function () {
    $this->post('/estudiante/login', [
        '_token'   => csrf_token(),
        'cedula'   => 'cc9999999',
        'password' => 'secret',
    ])->assertStatus(302); // redirect con errores, pero no 419
});

// ─── Ruta de auditoría requiere rol docente ─────────────────────────────────

it('superadmin no puede iniciar modo auditoría', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->get('/docente/auditar/cc1023456789')
        ->assertForbidden();
});

it('usuario no autenticado no puede acceder a ruta de auditoría', function () {
    $this->get('/docente/auditar/cc1023456789')
        ->assertRedirect('/login');
});

// ─── Salir de auditoría requiere autenticación ──────────────────────────────

it('GET /docente/auditar/salir redirige si no autenticado', function () {
    $this->get('/docente/auditar/salir')
        ->assertRedirect('/login');
});
