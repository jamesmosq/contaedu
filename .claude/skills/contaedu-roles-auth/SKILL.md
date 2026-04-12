---
name: contaedu-roles-auth
description: >-
  Roles, guards, rutas y modos especiales de ContaEdu (audit_mode,
  reference_mode). Activar cuando se trabaje con permisos, middleware,
  vistas con acciones condicionales, o lógica de solo lectura.
---

# ContaEdu — Roles, Auth y Modos Especiales

## Los 4 roles del sistema

| Rol | Guard | Prefijo de ruta | Nombre de ruta |
|-----|-------|-----------------|----------------|
| `superadmin` | `web` | `/admin/*` | `admin.*` |
| `coordinator` | `web` | `/coordinador/*` | `coordinator.*` |
| `teacher` | `web` | `/docente/*` | `teacher.*` |
| `student` | `student` | `/empresa/*` y `/aprendizaje/*` | `student.*` / `sandbox.*` |

Los roles `superadmin`, `coordinator` y `teacher` usan el guard `web` (Breeze).
El rol `student` usa un guard separado `student` con su propio modelo de autenticación.

---

## Middleware por grupo de rutas

```php
// web.php

// Superadmin
Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(...);

// Coordinador
Route::middleware(['auth', 'role:coordinator'])->prefix('coordinador')->name('coordinator.')->group(...);

// Docente
Route::middleware(['auth', 'role:teacher'])->prefix('docente')->name('teacher.')->group(...);

// Estudiante — zonas con tenant inicializado
Route::middleware(['auth:student', 'tenant.initialize'])->group(function () {
    // /empresa/* → name: student.*
    // /aprendizaje/* → name: sandbox.*
});

// Estudiante — login/register (guest)
Route::middleware('guest:student')->prefix('estudiante')->name('student.')->group(...);
```

---

## Verificar autenticación en código PHP

```php
// ¿Es un estudiante autenticado?
auth('student')->check()
auth('student')->user()   // → App\Models\Central\Tenant

// ¿Es un usuario web (admin/coordinator/teacher)?
auth('web')->check()
auth('web')->user()       // → App\Models\User

// ¿Tiene un rol específico?
auth('web')->user()->role  // string: 'superadmin' | 'coordinator' | 'teacher'
```

---

## Modos especiales de solo lectura

Hay dos modos que convierten la vista del estudiante en solo lectura:

### `audit_mode` — Docente o Coordinador auditando una empresa

Activado por `Teacher\AuditController::start()` o `Coordinator\AuditController::start()`.

```php
// Sesión cuando está activo:
session('audit_mode')          // true
session('audit_tenant_id')     // ID del tenant auditado
session('audit_student_name')  // nombre del estudiante
session('audit_company_name')  // nombre de la empresa
```

Desactivado por `::stop()` — limpia todas las claves de sesión.

### `reference_mode` — Estudiante explorando empresa de referencia

Activado por `Student\ReferenceController::enter()`.

```php
// Sesión cuando está activo:
session('reference_mode')          // true
session('reference_tenant_id')     // ID del tenant de referencia
session('reference_company_name')  // nombre de la empresa de referencia
session('reference_teacher_name')  // nombre del docente dueño
```

---

## Patrón en vistas blade — ocultar acciones destructivas

```blade
{{-- Ocultar botón "Nuevo" y acciones de edición/eliminación --}}
@if(! session('audit_mode') && ! session('reference_mode'))
    <button wire:click="openCreate" class="...">Nuevo X</button>
@endif

@if(! session('audit_mode') && ! session('reference_mode'))
    <button wire:click="openEdit({{ $item->id }})">Editar</button>
    <button x-on:click="confirmAction(...)">Eliminar</button>
@endif
```

> **Regla:** cualquier botón que mute datos DEBE estar dentro de este guard.
> Los botones de solo lectura (ver detalle, exportar PDF) no necesitan el guard.

---

## Patrón en Livewire — bloquear mutaciones

```php
// En métodos save(), delete(), etc.:
public function save(): void
{
    if (session('audit_mode') || session('reference_mode')) {
        $this->dispatch('notify', type: 'error', message: 'No se puede modificar en modo solo lectura.');
        return;
    }
    // ... lógica normal
}
```

---

## Banners de modo en el layout

El layout `tenant.blade.php` muestra banners automáticamente:

```
reference_mode activo  → banner azul/violeta "Empresa de referencia — Solo lectura"
audit_mode activo      → banner amber "Auditando empresa de [estudiante]"
/aprendizaje/*         → banner gold "Modo Aprendizaje — botón Reiniciar"
```

Estos banners se renderizan en `layouts/tenant.blade.php` y en
`resources/views/components/tenant-layout.blade.php` (para vistas FE).

---

## Determinar si es un estudiante real (no auditor ni referencia)

```php
// DashboardController — patrón usado en el proyecto:
$isRealStudent = ! session('audit_mode')
    && ! session('reference_mode')
    && auth('student')->check();
```

---

## Rutas de auditoría

```
teacher.auditar.start   POST  /docente/auditar/{tenantId}
teacher.auditar.stop    POST  /docente/auditar/salir
teacher.auditoria.*           → vistas del tenant en modo auditoría

coordinator.auditar.start   POST  /coordinador/auditar/{tenantId}
coordinator.auditar.stop    POST  /coordinador/auditar/salir
coordinator.auditoria.*           → vistas del tenant en modo auditoría
```

---

## Rutas de referencias (estudiante)

```
student.referencias.index   GET   /empresa/referencias
student.referencias.enter   POST  /empresa/referencias/{demoId}/entrar
student.referencias.exit    POST  /empresa/referencias/salir
```

---

## Cómo acceder al tenant actual en un Livewire component

```php
// El tenant ya está inicializado por el middleware tenant.initialize
// Los modelos Tenant se resuelven automáticamente por el search_path de PostgreSQL

// Si necesitas datos del tenant central (nombre empresa, NIT):
$config = \App\Models\Tenant\CompanyConfig::first();
$config->razon_social;
$config->nit;

// Si necesitas el ID del tenant en sesión (en modo auditoría):
$tenantId = session('audit_tenant_id');
```

---

## Importar guards en controladores

```php
// Para acciones que requieren verificar rol:
if (auth('web')->user()->role !== 'teacher') {
    abort(403);
}

// Para acciones en contexto de tenant (guard student):
$student = auth('student')->user(); // App\Models\Central\Tenant
```
