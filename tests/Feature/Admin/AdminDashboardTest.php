<?php

use App\Livewire\Admin\Dashboard;
use App\Models\Central\Institution;
use App\Models\User;
use Livewire\Livewire;

// ─── Acceso al panel ────────────────────────────────────────────────────────

it('superadmin ve el dashboard de administración', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    $this->actingAs($superadmin)
        ->get('/admin/dashboard')
        ->assertSuccessful();
});

// ─── Crear institución ──────────────────────────────────────────────────────

it('superadmin puede crear una institución', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->call('openCreateInst')
        ->assertSet('showInstForm', true)
        ->set('instName', 'Colegio Nacional Simón Bolívar')
        ->set('instNit', '800100200-1')
        ->set('instCity', 'Bogotá')
        ->call('saveInst')
        ->assertSet('showInstForm', false);

    expect(Institution::where('name', 'Colegio Nacional Simón Bolívar')->exists())->toBeTrue();
});

it('crear institución requiere nombre', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->call('openCreateInst')
        ->set('instName', '')
        ->call('saveInst')
        ->assertHasErrors(['instName' => 'required']);
});

it('nombre de institución no puede exceder 150 caracteres', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->call('openCreateInst')
        ->set('instName', str_repeat('A', 151))
        ->call('saveInst')
        ->assertHasErrors(['instName' => 'max']);
});

// ─── Editar institución ─────────────────────────────────────────────────────

it('superadmin puede editar una institución existente', function () {
    $superadmin  = User::factory()->create(['role' => 'superadmin']);
    $institution = Institution::create([
        'name'   => 'Institución Original',
        'nit'    => '800100200-1',
        'city'   => 'Bogotá',
        'active' => true,
    ]);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->call('openEditInst', $institution->id)
        ->assertSet('instEditingId', $institution->id)
        ->assertSet('instName', 'Institución Original')
        ->set('instName', 'Institución Actualizada')
        ->call('saveInst')
        ->assertSet('showInstForm', false);

    expect($institution->fresh()->name)->toBe('Institución Actualizada');
});

// ─── Eliminar institución ───────────────────────────────────────────────────

it('superadmin puede eliminar una institución', function () {
    $superadmin  = User::factory()->create(['role' => 'superadmin']);
    $institution = Institution::create([
        'name'   => 'Institución Para Borrar',
        'nit'    => '900300400-1',
        'city'   => 'Cali',
        'active' => true,
    ]);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->call('deleteInst', $institution->id);

    expect(Institution::find($institution->id))->toBeNull();
});

// ─── Crear docente ──────────────────────────────────────────────────────────

it('superadmin puede crear un docente', function () {
    $superadmin  = User::factory()->create(['role' => 'superadmin']);
    $institution = Institution::create(['name' => 'IED Prueba', 'nit' => '800200300-1', 'city' => 'Medellín', 'active' => true]);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->call('openCreateTeacher')
        ->assertSet('showTeacherForm', true)
        ->set('teacherName', 'María García')
        ->set('teacherEmail', 'maria.garcia@test.edu.co')
        ->set('teacherPassword', 'secret123')
        ->set('teacherInstitution', $institution->id)
        ->call('saveTeacher')
        ->assertSet('showTeacherForm', false);

    expect(User::where('email', 'maria.garcia@test.edu.co')->where('role', 'teacher')->exists())->toBeTrue();
});

it('crear docente requiere nombre, email y contraseña', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->call('openCreateTeacher')
        ->set('teacherName', '')
        ->set('teacherEmail', '')
        ->set('teacherPassword', '')
        ->call('saveTeacher')
        ->assertHasErrors(['teacherName', 'teacherEmail', 'teacherPassword']);
});

it('crear docente requiere seleccionar institución', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->call('openCreateTeacher')
        ->set('teacherName', 'Prof. Test')
        ->set('teacherEmail', 'prof@test.edu.co')
        ->set('teacherPassword', 'secret123')
        ->set('teacherInstitution', 0) // sin selección
        ->call('saveTeacher')
        ->assertHasErrors('teacherInstitution');
});

it('crear docente requiere contraseña mínima de 6 caracteres', function () {
    $superadmin  = User::factory()->create(['role' => 'superadmin']);
    $institution = Institution::create(['name' => 'IED Prueba', 'nit' => '800200300-1', 'city' => 'Medellín', 'active' => true]);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->call('openCreateTeacher')
        ->set('teacherName', 'Prof. Test')
        ->set('teacherEmail', 'prof@test.edu.co')
        ->set('teacherPassword', '12345') // solo 5 caracteres
        ->set('teacherInstitution', $institution->id)
        ->call('saveTeacher')
        ->assertHasErrors(['teacherPassword' => 'min']);
});

// ─── Eliminar docente ───────────────────────────────────────────────────────

it('superadmin puede eliminar un docente', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);
    $teacher    = User::factory()->create(['role' => 'teacher']);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->call('deleteTeacher', $teacher->id);

    expect(User::find($teacher->id))->toBeNull();
});

// ─── Tabs del dashboard ─────────────────────────────────────────────────────

it('el dashboard inicia en la tab de instituciones', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->assertSet('tab', 'instituciones');
});

it('superadmin puede cambiar a la tab de docentes', function () {
    $superadmin = User::factory()->create(['role' => 'superadmin']);

    Livewire::actingAs($superadmin)
        ->test(Dashboard::class)
        ->set('tab', 'docentes')
        ->assertSet('tab', 'docentes');
});
