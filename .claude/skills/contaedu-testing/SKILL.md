---
name: contaedu-testing
description: >-
  Patrones Pest para tests en ContaEdu. Activar cuando se escriban o
  modifiquen tests — cubre helpers de tenant, bypass de tenancy en Feature
  tests, assert de cuadre contable, tests de autorización, tests de
  componentes Livewire y qué no requiere test de integración.
---

# ContaEdu — Tests con Pest

## Estructura de tests

```
tests/
  Pest.php                          ← configuración global, helper something()
  TestCase.php                      ← base (vacío, extiende BaseTestCase)
  ArchTest.php                      ← arch tests
  Feature/
    Auth/
      StudentAuthTest.php           ← login/logout del estudiante
      AuthenticationTest.php        ← login web (admin/teacher/coordinator)
      PasswordResetTest.php
      PasswordUpdateTest.php
      EmailVerificationTest.php
      PasswordConfirmationTest.php
      RegistrationTest.php
    Authorization/
      RouteAuthorizationTest.php    ← protección de rutas por rol
    Admin/
      AdminDashboardTest.php
    Teacher/
      AuditModeTest.php             ← sesiones audit_mode
      DashboardBulkTest.php
  Unit/
    AccountingImbalanceTest.php     ← excepción de cuadre contable
    InvoiceBalanceTest.php          ← cálculo de saldo de factura
    CreditNoteAccountingTest.php    ← lógica de nota crédito
    PurchaseOrderTest.php           ← orden de compra
```

---

## Configuración en Pest.php

```php
// tests/Pest.php
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

// Solo Feature tests usan RefreshDatabase — los Unit tests no tocan BD
```

---

## Helper para crear un tenant de prueba

El `createTestTenant()` helper vive en `tests/Feature/Auth/StudentAuthTest.php`
pero debe ser movido a `tests/Pest.php` cuando se necesite en más de un archivo.

```php
function createTestTenant(string $id = 'cc1023456789', string $password = 'password123'): Tenant
{
    // Evita que TenantCreated dispare la creación del schema PostgreSQL en tests
    Event::fake([TenantCreated::class]);

    $institution = Institution::create([
        'name' => 'IED Prueba', 'nit' => '800100200-1',
        'city' => 'Bogotá', 'active' => true,
    ]);
    $teacher = User::factory()->create(['role' => 'teacher']);
    $group   = Group::create([
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
```

**Regla crítica:** `Event::fake([TenantCreated::class])` evita que el listener
`AutoMigrateTenant` intente crear un schema real en PostgreSQL durante el test.
Sin esto, el test falla porque no hay un schema `tenantcc1023456789` en la BD de prueba.

---

## Bypass de tenancy en Feature tests

Las rutas de estudiante con middleware `tenant.initialize` fallam si se intentan
acceder sin un tenant real inicializado. Bypass:

```php
// Ignorar el middleware de inicialización de tenancy
$this->withoutMiddleware(InitializeTenancyByStudent::class)
    ->actingAs($tenant, 'student')
    ->get('/empresa/facturas')
    ->assertSuccessful();

// Para rutas que solo necesitan autenticación web (no tenant):
$this->actingAs($teacher)
    ->get('/docente/dashboard')
    ->assertSuccessful(); // no necesita bypass — no hay tenant.initialize
```

---

## Tests de autorización — patrón estándar

```php
// ✅ Usuario no autenticado → redirige a login
it('GET /admin/dashboard redirige si no autenticado', function () {
    $this->get('/admin/dashboard')->assertRedirect('/login');
});

// ✅ Rol incorrecto → 403
it('docente no puede acceder al panel de admin', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $this->actingAs($teacher)->get('/admin/dashboard')->assertForbidden();
});

// ✅ Rol correcto → 200
it('docente puede acceder al panel de docente', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);
    $this->actingAs($teacher)->get('/docente/dashboard')->assertSuccessful();
});
```

---

## Tests de sesión audit_mode / reference_mode

```php
// Simular auditoría activa
$this->actingAs($teacher)
    ->withSession([
        'audit_mode'         => true,
        'audit_tenant_id'    => 'cc1023456789',
        'audit_student_name' => 'Juan Pérez',
        'audit_company_name' => 'Empresa Prueba SAS',
    ])
    ->get('/docente/dashboard')
    ->assertSessionHas('audit_mode', true);

// Verificar que salir de auditoría limpia la sesión
$response = $this->actingAs($teacher)
    ->withSession(['audit_mode' => true, 'audit_tenant_id' => 'cc123'])
    ->get('/docente/auditar/salir');

$response->assertRedirect(route('teacher.dashboard'));
$response->assertSessionMissing('audit_mode');
$response->assertSessionMissing('audit_tenant_id');
```

---

## Tests de cuadre contable (Unit)

Los tests unitarios de contabilidad NO necesitan BD — verifican la lógica matemática:

```php
// ✅ CORRECTO — test unitario puro
it('asiento cuadrado no lanza excepción', function () {
    $lines = [
        ['debit' => 119.00, 'credit' =>   0.00],
        ['debit' =>   0.00, 'credit' => 100.00],
        ['debit' =>   0.00, 'credit' =>  19.00],
    ];

    $diff = abs(
        array_sum(array_column($lines, 'debit')) -
        array_sum(array_column($lines, 'credit'))
    );

    expect($diff)->toBeLessThan(0.01);
});

// ✅ Verificar que AccountingService lanza la excepción correcta
it('lanza AccountingImbalanceException si débitos ≠ créditos', function () {
    $service = new AccountingService();
    $method  = new ReflectionMethod($service, 'createEntry');
    $method->setAccessible(true);

    $lines = [
        ['account_id' => 1, 'debit' => 100.00, 'credit' =>  0.00, 'description' => 'DR'],
        ['account_id' => 2, 'debit' =>   0.00, 'credit' => 80.00, 'description' => 'CR'], // no cuadra
    ];

    $header = [
        'date' => '2025-01-01', 'reference' => 'TEST-001',
        'description' => 'Test', 'document_type' => 'test',
        'document_id' => 1, 'auto_generated' => false,
    ];

    expect(fn () => $method->invoke($service, $header, $lines))
        ->toThrow(AccountingImbalanceException::class);
});
```

---

## Tests de componentes Livewire

ContaEdu usa Livewire 4. Para tests de componentes:

```php
use Livewire\Livewire;

it('componente Terceros renderiza sin error', function () {
    $teacher = User::factory()->create(['role' => 'teacher']);

    Livewire::actingAs($teacher)
        ->test(\App\Livewire\Tenant\Terceros\Index::class)
        ->assertOk();
});

it('método save() bloquea en audit_mode', function () {
    session(['audit_mode' => true]);

    Livewire::test(\App\Livewire\Tenant\Terceros\Index::class)
        ->call('save')
        ->assertDispatched('notify', fn ($type, $message) =>
            $type === 'error'
        );
});

it('método setTab() cambia el tab activo', function () {
    Livewire::test(\App\Livewire\Tenant\Terceros\Index::class)
        ->call('setTab', 'laboral')
        ->assertSet('activeTab', 'laboral');
});
```

---

## Qué NO requiere test de integración

Los siguientes patrones son suficientemente verificados con tests unitarios
o por el framework — no necesitan tests de integración completos:

- **Cálculo de totales** (IVA, retenciones, subtotales) → Unit test con lógica pura
- **Validación de reglas** (`rules()` de Livewire) → probar el array en Unit
- **Scope `modoActual()`** → test que verifica el where generado, no la BD completa
- **`ucwords(strtolower())`** en vistas → verificación visual, no test

---

## Verificar que el layout contiene elementos de seguridad

```php
// Útil para verificar que no se borró accidentalmente el guard de audit_mode
it('el layout tenant tiene el guard de audit_mode', function () {
    $layout = file_get_contents(resource_path('views/layouts/tenant.blade.php'));

    expect($layout)
        ->toContain("session('audit_mode')")
        ->toContain("session('reference_mode')");
});

// Verificar que no hay wire:click.self en un archivo crítico
it('el modal de facturas no usa wire:click.self', function () {
    $view = file_get_contents(resource_path('views/livewire/tenant/invoices/index.blade.php'));

    expect($view)->not->toContain('wire:click.self');
});
```

---

## Correr tests

```bash
# Todos los tests
php artisan test

# Solo tests unitarios (sin BD — más rápido)
php artisan test --testsuite=Unit

# Solo Feature tests
php artisan test --testsuite=Feature

# Un archivo específico
php artisan test tests/Feature/Authorization/RouteAuthorizationTest.php

# Con coverage (requiere Xdebug o PCOV)
php artisan test --coverage

# Con Pest directamente
./vendor/bin/pest --filter="audit_mode"
```

---

## Trampas comunes en tests de ContaEdu

```
❌ NO usar Event::fake() globalmente — solo para TenantCreated
❌ NO llamar tenancy()->initialize() en tests — usa withoutMiddleware()
❌ NO asumir que el schema del tenant existe — Event::fake evita crearlo
❌ NO mezclar actingAs($user) y actingAs($tenant, 'student') — guards distintos
✅ Para rutas web: actingAs($user) — para rutas student: actingAs($tenant, 'student')
✅ RefreshDatabase solo en Feature/ — los Unit tests son puros
```
