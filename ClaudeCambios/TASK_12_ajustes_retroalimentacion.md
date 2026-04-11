# TASK 12 — Ajustes de Retroalimentación: Terceros, NIT y Plan de Cuentas

> ⚠️ Prerequisito: TASK 11 completado.
> ⚠️ Este TASK es puramente de UX/UI y lógica de formularios.
>     No crea tablas nuevas. Solo modifica componentes Livewire
>     y vistas existentes.

---

## Objetivo

Tres ajustes puntuales pedidos en retroalimentación:

1. **Formulario de Terceros en tabs** — el formulario de Empleado es
   demasiado largo para pantallas de 14". Dividir en pestañas.

2. **NIT con dígito de verificación** — formatear automáticamente
   el NIT con guión en Terceros y Configuración de empresa.

3. **Registro de cuenta guiado** — en Plan de Cuentas, reemplazar
   el campo de código libre por navegación jerárquica
   Clase → Grupo → Cuenta (igual que puc.com.co).

---

## PARTE A — FORMULARIO TERCEROS EN TABS

### Contexto

El formulario actual apila todos los campos verticalmente.
Para Empleado son ~12 campos — inmanejable en 14".

### Solución: 2 pestañas dentro del modal

```
[ Datos básicos ]  [ Información laboral ]  ← tabs solo visibles si type=empleado
```

Para Cliente y Proveedor: solo se muestra "Datos básicos" (sin tabs).
Para Empleado: aparecen ambas pestañas.

### Paso A1 — Agregar propiedad `activeTab` en Index.php

En `app/Livewire/Tenant/Terceros/Index.php`, agregar:

```php
// Después de public bool $showForm = false;
public string $activeTab = 'basico'; // 'basico' | 'laboral'
```

Modificar `updatedType()` para resetear el tab al cambiar tipo:

```php
public function updatedType(): void
{
    $this->activeTab = 'basico'; // ← agregar esta línea al inicio

    if ($this->type !== 'empleado') {
        $this->cargo = '';
        $this->salario_basico = 0;
        $this->tipo_contrato = 'indefinido';
        $this->procedimiento_retencion = '1';
        $this->afp = '';
        $this->eps = '';
        $this->arl = '';
        $this->fecha_ingreso = '';
        $this->fecha_retiro = '';
    }
    if ($this->type === 'empleado' && $this->document_type === 'nit') {
        $this->document_type = 'cc';
    }
}
```

Modificar `resetAll()` para resetear el tab:

```php
private function resetAll(): void
{
    $this->showForm  = false;
    $this->activeTab = 'basico'; // ← agregar
    $this->reset([
        'editingId', 'document', 'name', 'address', 'phone', 'email',
        'municipio_codigo', 'municipioSearch', 'municipioLabel',
        'cargo', 'afp', 'eps', 'arl', 'fecha_ingreso', 'fecha_retiro',
    ]);
    $this->document_type           = 'nit';
    $this->type                    = 'cliente';
    $this->regimen                 = 'simplificado';
    $this->salario_basico          = 0;
    $this->tipo_contrato           = 'indefinido';
    $this->procedimiento_retencion = '1';
}
```

Agregar método para cambiar de tab:

```php
public function setTab(string $tab): void
{
    $this->activeTab = $tab;
}
```

### Paso A2 — Modificar la vista del formulario

En `resources/views/livewire/tenant/terceros/index.blade.php`,
dentro del modal del formulario, reemplazar la estructura actual
por la versión con tabs.

**Estructura del modal con tabs:**

```blade
{{-- Modal --}}
@if($showForm)
<div class="modal-overlay">
  <div class="modal" style="max-width:560px; max-height:90vh; overflow-y:auto;">

    <div class="modal-header">
      <h3>{{ $editingId ? 'Editar tercero' : 'Nuevo tercero' }}</h3>
      <button wire:click="cancelForm">✕</button>
    </div>

    <div class="modal-body">

      {{-- Tipo de tercero --}}
      <div class="form-group">
        <label class="form-label">Tipo de tercero *</label>
        <div class="btn-group">
          <button wire:click="$set('type','cliente')"
                  class="{{ $type==='cliente' ? 'btn-primary' : 'btn-outline' }}">
            Cliente
          </button>
          <button wire:click="$set('type','proveedor')"
                  class="{{ $type==='proveedor' ? 'btn-primary' : 'btn-outline' }}">
            Proveedor
          </button>
          <button wire:click="$set('type','empleado')"
                  class="{{ $type==='empleado' ? 'btn-primary' : 'btn-outline' }}">
            Empleado
          </button>
        </div>
      </div>

      {{-- TABS — solo visibles para empleado --}}
      @if($type === 'empleado')
      <div class="tabs" style="margin-bottom:1.25rem;">
        <button wire:click="setTab('basico')"
                class="tab {{ $activeTab==='basico' ? 'tab-active' : '' }}">
          Datos básicos
        </button>
        <button wire:click="setTab('laboral')"
                class="tab {{ $activeTab==='laboral' ? 'tab-active' : '' }}">
          Información laboral
        </button>
      </div>
      @endif

      {{-- TAB: Datos básicos (siempre visible) --}}
      @if($activeTab === 'basico' || $type !== 'empleado')
      <div>
        {{-- Tipo documento + Número --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label class="form-label">Tipo documento</label>
            <select wire:model.live="document_type" class="form-select">
              @if($type !== 'empleado')
              <option value="nit">NIT</option>
              @endif
              <option value="cc">Cédula (CC)</option>
              <option value="ce">Cédula extranjera</option>
              <option value="pasaporte">Pasaporte</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Número documento</label>
            <input type="text"
                   wire:model.live="document"
                   class="form-input"
                   placeholder="{{ $document_type === 'nit' ? 'ej: 900123456-7' : 'ej: 1234567890' }}" />
            @if($document_type === 'nit')
            <small style="color:#6b7280; font-size:0.72rem;">
              Incluye el dígito de verificación con guión: 900123456-7
            </small>
            @endif
            @error('document') <span class="form-error">{{ $message }}</span> @enderror
          </div>
        </div>

        {{-- Razón social / Nombre --}}
        <div class="form-group">
          <label class="form-label">
            {{ $document_type === 'nit' ? 'Razón social' : 'Nombre completo' }}
          </label>
          <input type="text" wire:model="name" class="form-input"
                 placeholder="{{ $document_type === 'nit' ? 'ej: Distribuidora XYZ S.A.S.' : 'ej: Juan Pérez García' }}" />
          @error('name') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        {{-- Régimen (solo cliente y proveedor) --}}
        @if($type !== 'empleado')
        <div class="form-group">
          <label class="form-label">Régimen</label>
          <select wire:model="regimen" class="form-select">
            <option value="simplificado">No responsable de IVA (Simplificado)</option>
            <option value="comun">Responsable de IVA (Común)</option>
          </select>
        </div>
        @endif

        {{-- Teléfono + Correo --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label class="form-label">Teléfono</label>
            <input type="text" wire:model="phone" class="form-input" placeholder="ej: 3001234567" />
          </div>
          <div class="form-group">
            <label class="form-label">Correo</label>
            <input type="email" wire:model="email" class="form-input" placeholder="ej: correo@empresa.com" />
          </div>
        </div>

        {{-- Dirección --}}
        <div class="form-group">
          <label class="form-label">Dirección</label>
          <input type="text" wire:model="address" class="form-input" placeholder="ej: Calle 10 # 5-23" />
        </div>

        {{-- Ciudad / Municipio --}}
        <div class="form-group" style="position:relative;">
          <label class="form-label">Ciudad / Municipio</label>
          <input type="text" wire:model.live="municipioSearch" class="form-input"
                 placeholder="Escriba para buscar..." autocomplete="off" />
          @if($municipios->count())
          <div class="dropdown-list">
            @foreach($municipios as $m)
            <button wire:click="selectMunicipio('{{ $m->codigo }}', '{{ $m->label }}')"
                    class="dropdown-item">
              {{ $m->municipio }} — {{ $m->departamento }}
            </button>
            @endforeach
          </div>
          @endif
        </div>
      </div>
      @endif

      {{-- TAB: Información laboral (solo empleado) --}}
      @if($type === 'empleado' && $activeTab === 'laboral')
      <div>
        {{-- Cargo + Fecha ingreso --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label class="form-label">Cargo *</label>
            <input type="text" wire:model="cargo" class="form-input" placeholder="ej: Contador" />
            @error('cargo') <span class="form-error">{{ $message }}</span> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Fecha de ingreso *</label>
            <input type="date" wire:model="fecha_ingreso" class="form-input" />
            @error('fecha_ingreso') <span class="form-error">{{ $message }}</span> @enderror
          </div>
        </div>

        {{-- Salario + Tipo contrato --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label class="form-label">Salario básico mensual *</label>
            <input type="number" wire:model="salario_basico" class="form-input"
                   min="0" placeholder="ej: 1300000" />
            @error('salario_basico') <span class="form-error">{{ $message }}</span> @enderror
          </div>
          <div class="form-group">
            <label class="form-label">Tipo de contrato *</label>
            <select wire:model="tipo_contrato" class="form-select">
              <option value="indefinido">Término indefinido</option>
              <option value="fijo">Término fijo</option>
              <option value="obra_labor">Obra o labor</option>
              <option value="prestacion_servicios">Prestación de servicios</option>
            </select>
          </div>
        </div>

        {{-- Procedimiento retención --}}
        <div class="form-group">
          <label class="form-label">Procedimiento de retención (Art. 383 E.T.) *</label>
          <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.75rem;">
            <button wire:click="$set('procedimiento_retencion','1')"
                    class="{{ $procedimiento_retencion==='1' ? 'btn-primary' : 'btn-outline' }}"
                    style="font-size:0.8rem; padding:0.5rem;">
              Procedimiento 1 — Mensual fijo
            </button>
            <button wire:click="$set('procedimiento_retencion','2')"
                    class="{{ $procedimiento_retencion==='2' ? 'btn-primary' : 'btn-outline' }}"
                    style="font-size:0.8rem; padding:0.5rem;">
              Procedimiento 2 — Promedio 6 meses
            </button>
          </div>
        </div>

        {{-- EPS + AFP --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:1rem;">
          <div class="form-group">
            <label class="form-label">EPS</label>
            <input type="text" wire:model="eps" class="form-input" placeholder="ej: Sura" />
          </div>
          <div class="form-group">
            <label class="form-label">AFP (pensión)</label>
            <input type="text" wire:model="afp" class="form-input" placeholder="ej: Porvenir" />
          </div>
        </div>

        {{-- ARL --}}
        <div class="form-group">
          <label class="form-label">ARL</label>
          <input type="text" wire:model="arl" class="form-input" placeholder="ej: Positiva" />
        </div>

        {{-- Fecha retiro (opcional) --}}
        <div class="form-group">
          <label class="form-label">Fecha de retiro <span style="color:#9ca3af;">(opcional)</span></label>
          <input type="date" wire:model="fecha_retiro" class="form-input" />
        </div>
      </div>
      @endif

    </div>{{-- fin modal-body --}}

    {{-- Footer con navegación entre tabs --}}
    <div class="modal-footer" style="display:flex; justify-content:space-between; align-items:center;">
      <div>
        @if($type === 'empleado' && $activeTab === 'basico')
        <button wire:click="setTab('laboral')" class="btn-outline">
          Siguiente: Info laboral →
        </button>
        @elseif($type === 'empleado' && $activeTab === 'laboral')
        <button wire:click="setTab('basico')" class="btn-outline">
          ← Datos básicos
        </button>
        @endif
      </div>
      <div style="display:flex; gap:0.75rem;">
        <button wire:click="cancelForm" class="btn-outline">Cancelar</button>
        <button wire:click="save" class="btn-primary">Guardar</button>
      </div>
    </div>

  </div>
</div>
@endif
```

> ⚠️ Usar las mismas clases CSS que el proyecto ya tiene para modales,
>    botones y formularios. Revisar el archivo de vista actual antes
>    de reemplazar para mantener coherencia visual.

---

## PARTE B — NIT CON DÍGITO DE VERIFICACIÓN

### Contexto

El NIT en Colombia tiene formato `XXXXXXXXX-X` donde el último dígito
es el dígito de verificación. Hoy el sistema acepta el número sin formato.

### Solución

El campo acepta el NIT con guión escrito por el usuario.
El sistema guarda el NIT tal como se ingresa (con guión).
Al mostrarlo, siempre aparece con guión.

No se calcula automáticamente el dígito — el estudiante debe conocerlo
o consultarlo (igual que en la vida real).

### Paso B1 — Validación en Terceros

En `app/Livewire/Tenant/Terceros/Index.php`, modificar la regla de `document`
cuando `document_type === 'nit'`:

```php
public function rules(): array
{
    $rules = [
        'document_type' => ['required', 'in:cc,nit,ce,pasaporte'],
        'document'      => $this->document_type === 'nit'
            ? ['required', 'string', 'regex:/^\d{6,10}-\d{1}$/', 'max:20']
            : ['required', 'string', 'max:20'],
        // ... resto de reglas
    ];
    // ...
}
```

Agregar mensaje de validación personalizado. Crear método `messages()`:

```php
public function messages(): array
{
    return [
        'document.regex' => 'El NIT debe incluir el dígito de verificación con guión. Ejemplo: 900123456-7',
    ];
}
```

### Paso B2 — Placeholder y ayuda visual en la vista

En el campo de número de documento, cuando `document_type === 'nit'`,
mostrar el placeholder `ej: 900123456-7` y una nota de ayuda:

```blade
<input type="text"
       wire:model.live="document"
       class="form-input"
       placeholder="{{ $document_type === 'nit' ? 'ej: 900123456-7' : 'ej: 1234567890' }}" />
@if($document_type === 'nit')
<small style="color:#6b7280; font-size:0.72rem; margin-top:0.25rem; display:block;">
  Formato: número-dígito de verificación (ej: 900123456-7)
</small>
@endif
```

### Paso B3 — NIT en Configuración de empresa

En la vista de configuración de empresa
(`resources/views/livewire/tenant/config/` o similar),
agregar el mismo placeholder y nota de ayuda en el campo NIT:

```blade
<input type="text" wire:model="nit" class="form-input"
       placeholder="ej: 900123456-7" />
<small style="color:#6b7280; font-size:0.72rem; margin-top:0.25rem; display:block;">
  Incluye el dígito de verificación con guión. Ejemplo: 900123456-7
</small>
```

### Paso B4 — Mostrar NIT con guión en tablas y reportes

Agregar helper en `app/helpers.php`:

```php
/**
 * Formatea un NIT para mostrar. Si ya tiene guión lo devuelve igual.
 * Si no tiene, lo devuelve tal cual (otros tipos de documento).
 */
function formatearNit(string $nit): string
{
    // Si ya tiene el formato correcto, devolver igual
    if (str_contains($nit, '-')) {
        return $nit;
    }
    return $nit;
}
```

> ℹ️ Como el NIT se guardará con guión desde el formulario,
>    no es necesaria transformación adicional. El helper queda
>    disponible por si se necesita en el futuro.

---

## PARTE C — REGISTRO DE CUENTA GUIADO

### Contexto

Hoy el formulario "Nueva cuenta auxiliar" pide el código libre.
El estudiante no sabe qué código poner sin consultar el PUC.

### Solución: selección jerárquica

```
Paso 1: Selecciona la Clase
[1 Activo] [2 Pasivo] [3 Patrimonio] [4 Ingresos] [5 Gastos] [6 Costos] ...

Paso 2: Selecciona el Grupo (según clase elegida)
[11 Disponible] [12 Inversiones] [13 Deudores] ...

Paso 3: Selecciona la Cuenta (4 dígitos)
[1105 Caja] [1110 Bancos] [1115 Remesas] ...

Paso 4: Define la subcuenta
El sistema sugiere el próximo código disponible (ej: 110520)
El estudiante solo escribe el nombre
```

### Paso C1 — Nuevas propiedades en PlanDeCuentas.php

En `app/Livewire/Tenant/PlanDeCuentas.php`, agregar:

```php
// Navegación guiada
public ?string $selectedClase   = null; // código de 1 dígito
public ?string $selectedGrupo   = null; // código de 2 dígitos
public ?string $selectedCuenta  = null; // código de 4 dígitos
public string  $pasoActual      = 'clase'; // 'clase'|'grupo'|'cuenta'|'subcuenta'
public string  $codigoSugerido  = '';

public function seleccionarClase(string $codigo): void
{
    $this->selectedClase  = $codigo;
    $this->selectedGrupo  = null;
    $this->selectedCuenta = null;
    $this->pasoActual     = 'grupo';
    $this->code           = '';
    $this->codigoSugerido = '';

    // Heredar tipo y naturaleza de la clase
    $clase = Account::where('code', $codigo)->first();
    if ($clase) {
        $this->type   = $clase->type;
        $this->nature = $clase->nature;
        $this->parent_id = $clase->id;
    }
}

public function seleccionarGrupo(string $codigo): void
{
    $this->selectedGrupo  = $codigo;
    $this->selectedCuenta = null;
    $this->pasoActual     = 'cuenta';

    $grupo = Account::where('code', $codigo)->first();
    if ($grupo) {
        $this->type      = $grupo->type;
        $this->nature    = $grupo->nature;
        $this->parent_id = $grupo->id;
    }
}

public function seleccionarCuenta(string $codigo): void
{
    $this->selectedCuenta = $codigo;
    $this->pasoActual     = 'subcuenta';

    $cuenta = Account::where('code', $codigo)->first();
    if ($cuenta) {
        $this->type      = $cuenta->type;
        $this->nature    = $cuenta->nature;
        $this->parent_id = $cuenta->id;
    }

    // Sugerir el próximo código disponible
    $this->codigoSugerido = $this->sugerirCodigo($codigo);
    $this->code = $this->codigoSugerido;
}

public function volverAPaso(string $paso): void
{
    $this->pasoActual = $paso;
    if ($paso === 'clase') {
        $this->selectedClase  = null;
        $this->selectedGrupo  = null;
        $this->selectedCuenta = null;
    } elseif ($paso === 'grupo') {
        $this->selectedGrupo  = null;
        $this->selectedCuenta = null;
    } elseif ($paso === 'cuenta') {
        $this->selectedCuenta = null;
    }
    $this->code           = '';
    $this->codigoSugerido = '';
}

private function sugerirCodigo(string $codigoCuenta): string
{
    // Buscar el último código de subcuenta bajo esta cuenta
    $ultimo = Account::where('code', 'like', $codigoCuenta . '%')
        ->where('level', 4)
        ->orderByDesc('code')
        ->value('code');

    if (! $ultimo) {
        return $codigoCuenta . '05'; // primer subcuenta: XXXX05
    }

    // Incrementar de 5 en 5
    $sufijo = (int) substr($ultimo, -2);
    $nuevoSufijo = str_pad($sufijo + 5, 2, '0', STR_PAD_LEFT);
    return $codigoCuenta . $nuevoSufijo;
}
```

Modificar `openForm()` para resetear la navegación guiada:

```php
public function openForm(?int $parentId = null): void
{
    $this->reset(['editingId', 'code', 'name', 'type', 'nature', 'parent_id']);
    $this->selectedClase   = null;
    $this->selectedGrupo   = null;
    $this->selectedCuenta  = null;
    $this->pasoActual      = 'clase';
    $this->codigoSugerido  = '';

    if ($parentId) {
        $parent = Account::find($parentId);
        $this->type      = $parent->type;
        $this->nature    = $parent->nature;
        $this->parent_id = $parentId;
        // Detectar en qué nivel está el padre y saltar al paso correcto
        $this->pasoActual = match($parent->level) {
            1 => 'grupo',
            2 => 'cuenta',
            3 => 'subcuenta',
            default => 'clase',
        };
        if ($parent->level === 1) $this->selectedClase = $parent->code;
        if ($parent->level === 2) {
            $this->selectedClase = substr($parent->code, 0, 1);
            $this->selectedGrupo = $parent->code;
        }
        if ($parent->level === 3) {
            $this->selectedClase  = substr($parent->code, 0, 1);
            $this->selectedGrupo  = substr($parent->code, 0, 2);
            $this->selectedCuenta = $parent->code;
            $this->codigoSugerido = $this->sugerirCodigo($parent->code);
            $this->code           = $this->codigoSugerido;
        }
    }

    $this->showForm = true;
}
```

Modificar `cancelForm()` para resetear:

```php
public function cancelForm(): void
{
    $this->reset(['showForm', 'editingId', 'code', 'name', 'type', 'nature', 'parent_id']);
    $this->selectedClase  = null;
    $this->selectedGrupo  = null;
    $this->selectedCuenta = null;
    $this->pasoActual     = 'clase';
    $this->codigoSugerido = '';
}
```

### Paso C2 — Pasar datos de navegación a la vista

En el método `render()` de `PlanDeCuentas.php`, agregar las cuentas
necesarias para la navegación:

```php
public function render(): mixed
{
    $accounts = Account::query()
        ->when($this->search, fn ($q) => $q
            ->where('code', 'ilike', "%{$this->search}%")
            ->orWhere('name', 'ilike', "%{$this->search}%")
        )
        ->orderBy('code')
        ->get()
        ->groupBy('level');

    $parentAccounts = Account::where('level', '<', 5)->orderBy('code')->get();

    // Para la navegación guiada
    $clases  = Account::where('level', 1)->orderBy('code')->get();
    $grupos  = $this->selectedClase
        ? Account::where('level', 2)
                 ->where('code', 'like', $this->selectedClase . '%')
                 ->orderBy('code')->get()
        : collect();
    $cuentas = $this->selectedGrupo
        ? Account::where('level', 3)
                 ->where('code', 'like', $this->selectedGrupo . '%')
                 ->orderBy('code')->get()
        : collect();

    return view('livewire.tenant.cuentas.plan-de-cuentas',
        compact('accounts', 'parentAccounts', 'clases', 'grupos', 'cuentas'))
        ->title('Plan de Cuentas');
}
```

### Paso C3 — Vista del formulario guiado

En `resources/views/livewire/tenant/cuentas/plan-de-cuentas.blade.php`,
reemplazar el modal de "Nueva cuenta auxiliar" por la versión guiada:

```blade
@if($showForm)
<div class="modal-overlay">
  <div class="modal" style="max-width:540px;">

    <div class="modal-header">
      <h3>Nueva subcuenta auxiliar</h3>
      <button wire:click="cancelForm">✕</button>
    </div>

    <div class="modal-body">

      {{-- Breadcrumb de navegación --}}
      <div style="display:flex; align-items:center; gap:0.5rem; font-size:0.8rem;
                  color:#6b7280; margin-bottom:1.25rem; flex-wrap:wrap;">
        <button wire:click="volverAPaso('clase')"
                style="color:{{ $selectedClase ? '#1a4731' : '#9ca3af' }}; background:none; border:none; cursor:pointer; padding:0;">
          Clase
        </button>
        @if($selectedClase)
        <span>›</span>
        <button wire:click="volverAPaso('grupo')"
                style="color:{{ $selectedGrupo ? '#1a4731' : '#9ca3af' }}; background:none; border:none; cursor:pointer; padding:0;">
          {{ $selectedClase }} Grupo
        </button>
        @endif
        @if($selectedGrupo)
        <span>›</span>
        <button wire:click="volverAPaso('cuenta')"
                style="color:{{ $selectedCuenta ? '#1a4731' : '#9ca3af' }}; background:none; border:none; cursor:pointer; padding:0;">
          {{ $selectedGrupo }} Cuenta
        </button>
        @endif
        @if($selectedCuenta)
        <span>›</span>
        <span style="color:#1a4731; font-weight:600;">{{ $selectedCuenta }} Subcuenta</span>
        @endif
      </div>

      {{-- PASO 1: Seleccionar clase --}}
      @if($pasoActual === 'clase')
      <div>
        <p style="font-size:0.85rem; color:#6b7280; margin-bottom:1rem;">
          Selecciona la clase del PUC a la que pertenece la nueva cuenta:
        </p>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:0.5rem;">
          @foreach($clases as $clase)
          <button wire:click="seleccionarClase('{{ $clase->code }}')"
                  style="text-align:left; padding:0.75rem 1rem; border:1px solid #e5e7eb;
                         border-radius:8px; cursor:pointer; background:#fff;
                         transition: all 0.15s;"
                  onmouseover="this.style.borderColor='#1a4731'; this.style.background='#f0fdf4'"
                  onmouseout="this.style.borderColor='#e5e7eb'; this.style.background='#fff'">
            <div style="font-weight:700; color:#1a4731; font-size:0.9rem;">
              {{ $clase->code }}
            </div>
            <div style="font-size:0.78rem; color:#6b7280;">{{ $clase->name }}</div>
          </button>
          @endforeach
        </div>
      </div>
      @endif

      {{-- PASO 2: Seleccionar grupo --}}
      @if($pasoActual === 'grupo')
      <div>
        <p style="font-size:0.85rem; color:#6b7280; margin-bottom:1rem;">
          Selecciona el grupo dentro de la Clase {{ $selectedClase }}:
        </p>
        <div style="display:flex; flex-direction:column; gap:0.4rem; max-height:300px; overflow-y:auto;">
          @foreach($grupos as $grupo)
          <button wire:click="seleccionarGrupo('{{ $grupo->code }}')"
                  style="text-align:left; padding:0.6rem 1rem; border:1px solid #e5e7eb;
                         border-radius:6px; cursor:pointer; background:#fff; display:flex; gap:1rem;">
            <span style="font-family:monospace; color:#1a4731; font-weight:600; min-width:32px;">
              {{ $grupo->code }}
            </span>
            <span style="font-size:0.85rem; color:#374151;">{{ $grupo->name }}</span>
          </button>
          @endforeach
        </div>
      </div>
      @endif

      {{-- PASO 3: Seleccionar cuenta --}}
      @if($pasoActual === 'cuenta')
      <div>
        <p style="font-size:0.85rem; color:#6b7280; margin-bottom:1rem;">
          Selecciona la cuenta (4 dígitos) bajo el grupo {{ $selectedGrupo }}:
        </p>
        <div style="display:flex; flex-direction:column; gap:0.4rem; max-height:300px; overflow-y:auto;">
          @foreach($cuentas as $cuenta)
          <button wire:click="seleccionarCuenta('{{ $cuenta->code }}')"
                  style="text-align:left; padding:0.6rem 1rem; border:1px solid #e5e7eb;
                         border-radius:6px; cursor:pointer; background:#fff; display:flex; gap:1rem;">
            <span style="font-family:monospace; color:#1a4731; font-weight:600; min-width:48px;">
              {{ $cuenta->code }}
            </span>
            <span style="font-size:0.85rem; color:#374151;">{{ $cuenta->name }}</span>
            @if($cuenta->descripcion)
            <span style="margin-left:auto; color:#16a34a; font-size:0.65rem;">● con guía</span>
            @endif
          </button>
          @endforeach
        </div>
      </div>
      @endif

      {{-- PASO 4: Definir subcuenta --}}
      @if($pasoActual === 'subcuenta')
      <div>
        <p style="font-size:0.85rem; color:#6b7280; margin-bottom:1rem;">
          Define la nueva subcuenta bajo
          <strong>{{ $selectedCuenta }}</strong>:
        </p>

        {{-- Código sugerido (editable) --}}
        <div class="form-group">
          <label class="form-label">Código de la subcuenta</label>
          <input type="text" wire:model="code" class="form-input"
                 placeholder="{{ $codigoSugerido ?: 'ej: 110520' }}" />
          @if($codigoSugerido)
          <small style="color:#6b7280; font-size:0.72rem;">
            Sugerido: {{ $codigoSugerido }} — puedes modificarlo si necesitas otro
          </small>
          @endif
          @error('code') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        {{-- Nombre --}}
        <div class="form-group">
          <label class="form-label">Nombre de la subcuenta</label>
          <input type="text" wire:model="name" class="form-input"
                 placeholder="ej: Caja sucursal norte" />
          @error('name') <span class="form-error">{{ $message }}</span> @enderror
        </div>

        {{-- Naturaleza (heredada, solo informativa) --}}
        <div style="background:#f0fdf4; border-radius:6px; padding:0.75rem; font-size:0.82rem; color:#374151;">
          <strong>Tipo:</strong> {{ ucfirst($type) }} ·
          <strong>Naturaleza:</strong> {{ ucfirst($nature) }}
          <span style="color:#6b7280; font-size:0.75rem;">
            (heredados de la cuenta padre)
          </span>
        </div>
      </div>
      @endif

    </div>{{-- fin modal-body --}}

    <div class="modal-footer" style="display:flex; justify-content:flex-end; gap:0.75rem;">
      <button wire:click="cancelForm" class="btn-outline">Cancelar</button>
      @if($pasoActual === 'subcuenta')
      <button wire:click="save" class="btn-primary">Guardar cuenta</button>
      @endif
    </div>

  </div>
</div>
@endif
```

---

## Lo que NO se toca

- Modelo `Third` — sin cambios
- Enum `ThirdType` — sin cambios
- Migraciones — sin cambios (no hay columnas nuevas)
- `AccountingService` — sin cambios
- Lógica de asientos — sin cambios
- Rutas — sin cambios

---

## Orden de ejecución

```
PARTE A — Terceros en tabs
1. Agregar activeTab + setTab() en Terceros/Index.php (Paso A1)
2. Reemplazar vista del formulario con tabs (Paso A2)

PARTE B — NIT con dígito
3. Agregar validación regex para NIT en Terceros/Index.php (Paso B1)
4. Agregar messages() con mensaje personalizado (Paso B1)
5. Actualizar placeholder y nota de ayuda en vista Terceros (Paso B2)
6. Actualizar campo NIT en vista Configuración de empresa (Paso B3)
7. Agregar formatearNit() en helpers.php (Paso B4)

PARTE C — Cuenta guiada
8. Agregar propiedades y métodos de navegación en PlanDeCuentas.php (Paso C1)
9. Actualizar render() con clases/grupos/cuentas (Paso C2)
10. Reemplazar modal de nueva cuenta con versión guiada (Paso C3)
```

---

## Verificación

**Terceros:**
1. Abrir "Nuevo tercero" → tipo Cliente → solo pestaña "Datos básicos" visible, sin tabs
2. Cambiar a Empleado → aparecen tabs "Datos básicos" e "Información laboral"
3. En tab "Datos básicos" → botón "Siguiente: Info laboral →" en el footer
4. En tab "Información laboral" → botón "← Datos básicos" en el footer
5. Guardar empleado desde cualquier tab → funciona correctamente

**NIT:**
6. En Terceros, tipo documento NIT → placeholder muestra `ej: 900123456-7`
7. Ingresar NIT sin guión → error de validación con mensaje claro
8. Ingresar NIT con guión `900123456-7` → pasa la validación
9. En Configuración de empresa → campo NIT muestra el mismo placeholder y nota

**Plan de cuentas:**
10. Clic en "+ Agregar cuenta" → aparece modal con las 9 clases del PUC
11. Seleccionar clase 1 (Activo) → aparecen los grupos (11, 12, 13...)
12. Seleccionar grupo 11 (Disponible) → aparecen las cuentas (1105, 1110...)
13. Seleccionar cuenta 1105 (Caja) → aparece el paso de subcuenta con código sugerido
14. El breadcrumb muestra la ruta: Clase › Grupo › Cuenta › Subcuenta
15. Hacer clic en el breadcrumb → vuelve al paso anterior
16. Clic en "+ Sub" en la lista → abre el modal directamente en el paso correcto
17. El tipo y naturaleza se heredan automáticamente del padre
