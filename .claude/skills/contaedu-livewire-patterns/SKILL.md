---
name: contaedu-livewire-patterns
description: >-
  Patrones Livewire 4 específicos de ContaEdu. Activar cuando se cree,
  modifique o depure cualquier componente Livewire del proyecto. Cubre
  convenciones de organización, layouts, filtrado por modo, notificaciones
  y patrones de componentes existentes.
---

# ContaEdu — Patrones Livewire 4

## Organización de componentes

```
app/Livewire/
  Tenant/           ← compartidos entre /aprendizaje/* y /empresa/*
    ActivosFijos/
      Index.php
    Banco/
      Index.php
    Calendario/
      Index.php
    Compras/
      Index.php
    Conciliacion/
      Index.php
    Config/
      Index.php     ← CompanyConfig
    Invoices/
      Index.php
    Negocios/
      Index.php
    PlanDeCuentas.php
    PucInteractivo.php
    Productos/
      Index.php
    Reportes/
      Index.php
    Terceros/
      Index.php
  Student/
    Referencias.php
    NotificationBell.php
  Teacher/          ← paneles del docente
  Admin/            ← paneles del admin
  Coordinator/      ← paneles del coordinador
  Shared/           ← componentes reutilizables entre roles
```

---

## Convención de un componente Tenant típico

```php
<?php

namespace App\Livewire\Tenant\Invoices;

use App\Models\Tenant\Invoice;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.tenant')]          // ← siempre este layout
#[Title('Facturas de venta')]        // ← título de la página
class Index extends Component
{
    public string $search = '';
    public string $estado = '';

    // Notificación estándar del proyecto
    public function notify(string $type, string $message): void
    {
        $this->dispatch('notify', type: $type, message: $message);
    }

    public function render(): mixed
    {
        $invoices = Invoice::modoActual()  // ← SIEMPRE filtrar por modo
            ->when($this->search, fn ($q) => $q->where('...', 'ilike', "%{$this->search}%"))
            ->orderByDesc('date')
            ->get();

        return view('livewire.tenant.invoices.index', compact('invoices'));
    }
}
```

---

## Regla crítica: filtrar siempre por modo

Cualquier query en un componente Tenant sobre tablas con campo `modo`
DEBE usar el scope `modoActual()`:

```php
// ✅ CORRECTO
Invoice::modoActual()->get();
JournalEntry::modoActual()->whereBetween('date', [$from, $to])->get();
PurchaseInvoice::modoActual()->where('status', 'pendiente')->get();

// ❌ INCORRECTO — mezcla datos de ambas zonas
Invoice::all();
Invoice::where('status', 'emitida')->get();
```

Tablas con campo `modo` (requieren scope):
- `journal_entries` → `JournalEntry`
- `invoices` → `Invoice`
- `purchase_invoices` → `PurchaseInvoice`
- `purchase_orders` → `PurchaseOrder`
- `payments` → `Payment`
- `cash_receipts` → `CashReceipt`
- `credit_notes` → `CreditNote`
- `debit_notes` → `DebitNote`
- `fixed_assets` → `FixedAsset`
- `bank_reconciliations` → `BankReconciliation`

Tablas SIN campo `modo` (no requieren scope — siempre compartidas):
- `accounts` → PUC compartido
- `thirds` → terceros compartidos
- `products` → productos compartidos
- `bank_accounts` → siempre real
- `bank_transactions` → siempre real
- `company_config` → configuración compartida

---

## Detectar zona en un componente

```php
// En un componente Livewire, si necesitas lógica diferente por zona:
$esSandbox = request()->is('aprendizaje/*');

// O usando el helper:
$modo = modoContable(); // 'sandbox' | 'real'
```

---

## Notificaciones (sistema existente)

```php
// Desde cualquier componente Livewire:
$this->dispatch('notify', type: 'success', message: 'Guardado correctamente.');
$this->dispatch('notify', type: 'error',   message: 'Ocurrió un error.');
$this->dispatch('notify', type: 'warning', message: 'Revisa los datos.');
$this->dispatch('notify', type: 'info',    message: 'Información.');
```

---

## Rutas de vistas

Las vistas de componentes Tenant siguen esta convención:

```
app/Livewire/Tenant/Invoices/Index.php
  → resources/views/livewire/tenant/invoices/index.blade.php

app/Livewire/Tenant/PlanDeCuentas.php
  → resources/views/livewire/tenant/cuentas/plan-de-cuentas.blade.php

app/Livewire/Tenant/PucInteractivo.php
  → resources/views/livewire/tenant/puc/puc-interactivo.blade.php
```

---

## Rutas que apuntan al mismo componente en ambas zonas

Los componentes Tenant son reutilizados en `/aprendizaje/*` y `/empresa/*`.
La separación de datos se hace por `modoActual()`, no duplicando componentes.

```php
// web.php — el mismo componente sirve para ambas zonas
Route::get('/empresa/facturas',      InvoicesIndex::class)->name('student.facturas');
Route::get('/aprendizaje/facturas',  InvoicesIndex::class)->name('sandbox.facturas');

// El componente detecta el modo automáticamente via modoContable()
```

**Si un cambio en el componente aplica a ambas zonas → modificar una sola vez.**
**Si aplica solo a una zona → usar `request()->is('aprendizaje/*')` dentro del componente.**

---

## Patrones de formularios modales

El proyecto usa modales inline (no componentes separados) para formularios
simples. Ver `PlanDeCuentas.php` como referencia:

```php
public bool $showForm = false;

public function openForm(): void
{
    $this->reset(['code', 'name', ...]);
    $this->showForm = true;
}

public function cancelForm(): void
{
    $this->reset(['showForm', 'code', 'name', ...]);
}

public function save(): void
{
    $this->validate();
    Model::create([...]);
    $this->reset(['showForm', ...]);
    $this->dispatch('notify', type: 'success', message: 'Guardado.');
}
```

---

## Layout del tenant

El layout principal es `layouts.tenant` (o `layouts/tenant.blade.php`).
Incluye:
- Sidebar con dos zonas: APRENDIZAJE CONTABLE y MI EMPRESA
- Banner sandbox (visible solo en `/aprendizaje/*`)
- Sistema de notificaciones toast
- NotificationBell para anuncios del docente

---

## Paginación

Usar `WithPagination` de Livewire cuando la lista puede ser larga:

```php
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public function render(): mixed
    {
        return view('...', [
            'items' => Model::modoActual()->paginate(20),
        ]);
    }
}
```

---

## Wire directives más usados en el proyecto

```blade
wire:model.live="search"              ← búsqueda en tiempo real
wire:model.live.debounce.300ms="q"    ← búsqueda con debounce
wire:click="metodo"                   ← acción sin parámetro
wire:click="metodo({{ $id }})"        ← acción con parámetro
wire:loading.attr="disabled"          ← deshabilitar durante carga
wire:loading.class="opacity-50"       ← opacidad durante carga
```

---

## Confirmaciones destructivas — SweetAlert2

**NUNCA usar** `wire:confirm`, `onsubmit="return confirm()"` ni `wire:click.self`.
Todos producen el dialog nativo del browser o cierran el modal con Ctrl.

**Patrón correcto** — usar `confirmAction()` de `app.js` vía Alpine:

```blade
{{-- Eliminar un registro --}}
<button
    x-on:click="confirmAction(
        '¿Eliminar este tercero?',
        () => $wire.delete({{ $third->id }}),
        { danger: true, confirmText: 'Sí, eliminar' }
    )"
    class="...">
    Eliminar
</button>

{{-- Acción crítica con texto personalizado --}}
<button
    x-on:click="confirmAction(
        '¿Anular esta factura? Esta acción no se puede deshacer.',
        () => $wire.anular({{ $invoice->id }}),
        { danger: true, confirmText: 'Sí, anular', cancelText: 'Cancelar' }
    )"
    class="...">
    Anular
</button>
```

**Para formularios POST normales** (no Livewire), usar `Swal.fire` directo:

```blade
<form id="mi-form" method="POST" action="{{ route('...') }}">
    @csrf
    <button type="button"
        onclick="Swal.fire({
            title: '¿Título de la acción?',
            text: 'Descripción de consecuencias.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10472a',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Sí, confirmar',
            cancelButtonText: 'Cancelar'
        }).then(result => { if (result.isConfirmed) document.getElementById('mi-form').submit(); })">
        Ejecutar
    </button>
</form>
```

**Firma de `confirmAction()`:**

```js
// app/resources/js/app.js
window.confirmAction = function(message, callback, options = {})
// options: { danger: bool, confirmText: string, cancelText: string }
// Usa Swal.fire internamente con colores del proyecto (forest-800 / red-600)
```

---

## Modales — reglas

- **NO** poner `wire:click.self` en el overlay — cierra el modal al presionar Ctrl
- El overlay es solo `fixed inset-0 bg-slate-900/60 z-40 ...` sin listeners de click
- El cierre siempre va en el botón ✕ explícito y en "Cancelar"
- Usar `x-show="$wire.showForm"` + `x-cloak` para modales reactivos
  o `@if($showForm)` para modales simples sin animación
