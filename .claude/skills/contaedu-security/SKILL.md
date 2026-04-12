---
name: contaedu-security
description: >-
  Seguridad y verificación de integridad en ContaEdu. Activar cuando se
  agreguen métodos Livewire públicos, formularios, queries con input del
  usuario, o cualquier operación mutante. Cubre tenant isolation, XSS,
  SQL injection, CSRF, autorización y checklist de revisión pre-deploy.
---

# ContaEdu — Seguridad y Verificación

## El riesgo principal: aislamiento de tenant

ContaEdu usa PostgreSQL search_path por tenant. Cada estudiante tiene su
propio schema. Los modelos Tenant (`Invoice`, `Third`, `Account`, etc.)
**solo pueden ver datos del schema activo** — esto hace que
`Third::findOrFail($id)` sea seguro: si el ID no pertenece al tenant
actual, simplemente no existe en su schema y lanza 404.

**PERO esto se rompe si se cambia el tenant en medio de un request:**

```php
// ✅ SEGURO — findOrFail solo ve el schema del tenant actual
Third::findOrFail($id);
Invoice::findOrFail($id);

// ❌ PELIGROSO — después de initialize(), los modelos Tenant
//   apuntan al NUEVO tenant, no al original
tenancy()->initialize($otroTenant);
Third::findOrFail($id); // ← ahora busca en el schema de $otroTenant
```

Regla: **nunca hacer `findOrFail` de modelos Tenant después de
`tenancy()->initialize()`** dentro del mismo request.

---

## Métodos Livewire públicos — todos son llamables desde el browser

En Livewire 4, CUALQUIER método `public` puede ser invocado con
`wire:call` o directamente vía WebSocket. Esto significa que un
estudiante podría llamar `delete(999)`, `save()`, `anular(5)`, etc.

**Regla obligatoria en métodos mutantes:**

```php
// ✅ CORRECTO — bloquear en modo solo lectura
public function delete(int $id): void
{
    if (session('audit_mode') || session('reference_mode')) {
        return; // silencioso, o dispatch notify error
    }
    Third::findOrFail($id)->delete();
    $this->dispatch('notify', type: 'success', message: 'Eliminado.');
}

public function save(): void
{
    if (session('audit_mode') || session('reference_mode')) {
        $this->dispatch('notify', type: 'error', message: 'Solo lectura.');
        return;
    }
    $this->validate();
    // ...
}
```

**Los métodos de solo lectura** (`render`, `openEdit`, `setTab`, etc.)
no necesitan este guard.

---

## XSS — `{!! !!}` solo para fuentes confiables

En el proyecto, `{!! !!}` se usa ÚNICAMENTE para renderizar SVG inline
desde arrays PHP hardcodeados (ej: `$icons[$item['icon']] ?? ''` en
`tenant-navigation.blade.php`). Esto es seguro porque la fuente es
código PHP, no input del usuario.

```blade
{{-- ✅ SEGURO — array controlado desde PHP --}}
{!! $icons[$item['icon']] ?? '' !!}

{{-- ❌ NUNCA HACER — input del usuario sin sanitizar --}}
{!! $third->name !!}
{!! request('comentario') !!}
{!! $account->descripcion !!}

{{-- ✅ Siempre para datos del usuario --}}
{{ $third->name }}
{{ $account->descripcion }}
```

---

## SQL raw — parámetros siempre vinculados

En el proyecto hay uso de `whereRaw` y `selectRaw`. Los valores deben
ir como segundo parámetro (binding), nunca interpolados.

```php
// ✅ CORRECTO — binding seguro
->whereRaw("left(code, 1) IN ('5', '1')")  // string fijo, ok
->selectRaw('COALESCE(SUM(debit) - SUM(credit), 0) as saldo')  // sin input, ok

// ❌ NUNCA HACER — interpolación con input del usuario
->whereRaw("name = '{$request->name}'")       // SQL injection
->whereRaw("code = " . $this->search)          // SQL injection

// ✅ CORRECTO — binding para input del usuario
->whereRaw('name ILIKE ?', ["%{$this->search}%"])
->where('name', 'ilike', "%{$this->search}%")  // ← preferible, Eloquent lo vincula solo
```

---

## Null safety en `find()` vs `findOrFail()`

`Account::find($id)` devuelve `null` si no existe. Siempre verificar:

```php
// ❌ PELIGROSO — puede explotar con Call to member on null
$parent = Account::find($parentId);
$level = $parent->level + 1;

// ✅ CORRECTO
$parent = Account::find($parentId);
$level = $parent ? $parent->level + 1 : 1;

// ✅ O usar findOrFail si el ID debe existir
$parent = Account::findOrFail($parentId);
```

En el proyecto, `PlanDeCuentas.php` tiene este patrón — revisar si
`$parentId` puede ser null antes de llamar `Account::find($parentId)`.

---

## CSRF — formularios POST no-Livewire

Los formularios Livewire tienen CSRF automático. Los formularios HTML
normales (`method="POST"`) DEBEN tener `@csrf`.

```blade
{{-- ✅ CORRECTO --}}
<form method="POST" action="{{ route('sandbox.reset') }}">
    @csrf
    <button type="submit">Reiniciar</button>
</form>

{{-- ❌ FALTARÍA CSRF --}}
<form method="POST" action="/alguna-ruta">
    <button type="submit">Enviar</button>
</form>
```

Buscar formularios sin `@csrf`:
```bash
grep -rn 'method="POST"' resources/views --include="*.blade.php" | grep -v "@csrf"
```

---

## Mass assignment — `$fillable` en todos los modelos

Todos los modelos del proyecto usan `$fillable` (no `$guarded = []`).
Al agregar nuevas columnas a una tabla, **siempre agregar el campo a
`$fillable`** del modelo correspondiente.

```php
// ✅ CORRECTO — campo explícito
protected $fillable = ['code', 'name', 'type', 'nature', 'parent_id', 'level', 'active'];

// ❌ NUNCA USAR en producción
protected $guarded = [];
```

---

## Checklist de revisión pre-deploy

### Seguridad
- [ ] Todos los métodos Livewire mutantes tienen guard de `audit_mode`/`reference_mode`
- [ ] No hay `{!! !!}` con datos del usuario
- [ ] No hay interpolación de strings en `whereRaw`/`selectRaw`
- [ ] Todos los formularios POST tienen `@csrf`
- [ ] Los nuevos campos están en `$fillable`
- [ ] Los `Account::find()` verifican null antes de usar el resultado

### Integridad de datos
- [ ] Nuevas migraciones de tenant tienen guards `hasColumn`/`hasTable`
- [ ] Los asientos contables pasan por `AccountingService::createEntry()` (nunca directo)
- [ ] Queries en componentes Tenant usan `modoActual()` si la tabla tiene campo `modo`
- [ ] No se mezclan modelos Central y Tenant después de `tenancy()->initialize()`

### Funcionalidad
- [ ] La nueva funcionalidad funciona en `/aprendizaje/*` Y en `/empresa/*`
- [ ] Los botones de acción están dentro del guard `audit_mode`/`reference_mode`
- [ ] Las rutas nuevas tienen el middleware correcto (`auth`, `auth:student`, `tenant.initialize`)
- [ ] Los métodos `save()`/`delete()` llaman `$this->validate()` antes de mutar

### Visual
- [ ] Las nuevas vistas tienen hero con gradiente forest
- [ ] No hay emojis (solo SVG Heroicons)
- [ ] Las variables `@php` locales no colisionan con variables del `render()`
- [ ] Modales no tienen `wire:click.self` en el overlay

---

## Checklist de verificación post-cambio

Después de modificar un componente, verificar manualmente:

```
1. Abrir la vista en /aprendizaje/* → funciona igual que en /empresa/*
2. Activar modo auditoría → los botones de acción desaparecen
3. Activar modo referencia → los botones de acción desaparecen
4. Abrir el formulario → presionar Ctrl+C dentro de un input → modal NO se cierra
5. Crear un registro → aparece notificación toast
6. Validar con campos vacíos → aparecen errores inline bajo cada campo
7. Guardar → el asiento contable aparece en reportes del modo correcto
```

---

## Verificación de tenant isolation

Para confirmar que un componente no filtra datos entre tenants:

```php
// En Tinker o un test — crear datos en tenant A, verificar que tenant B no los ve
tenancy()->initialize($tenantA);
Invoice::create([...]);

tenancy()->initialize($tenantB);
$count = Invoice::count(); // debe ser 0 si tenant B no tiene facturas
```

---

## Variables de blade — evitar colisiones

Si un `@php` block en una vista define una variable con el mismo nombre
que una variable pasada desde `render()`, la sobreescribe para el resto
de la vista.

```blade
{{-- ❌ PELIGROSO — sobreescribe $clases del render() --}}
@php
    $clases = ['1' => 'Activo', ...]; // colisión con $clases de render()
@endphp

{{-- ✅ CORRECTO — usar nombre único --}}
@php
    $clasesBadge = ['1' => 'Activo', ...];
@endphp
```

**Regla:** los arrays locales de configuración/colores en blade deben
tener sufijo que los diferencie (`Badge`, `Color`, `Label`, `Map`).
