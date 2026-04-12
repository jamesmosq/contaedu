---
name: contaedu-ui-sistema-visual
description: >-
  Sistema visual de ContaEdu: paleta de colores, hero, tablas, modales,
  badges, botones e iconos. Activar cuando se cree o modifique cualquier
  vista blade. Evita inconsistencias visuales y uso de emojis.
---

# ContaEdu — Sistema Visual

## Paleta de colores

El proyecto usa tres familias de color propias + slate estándar:

```
forest-*   → verde oscuro — color primario del sistema
  forest-50    fondo hover muy suave
  forest-100   bordes sutiles
  forest-300   texto secundario sobre fondos oscuros
  forest-400   labels/subtítulos en hero
  forest-500   focus ring de inputs
  forest-600   links y acciones inline
  forest-700   botones seleccionados, tabs activos
  forest-800   botón primario (CTA principal)
  forest-900   hero gradient inicio
  forest-950   thead de tablas, hero gradient fin

gold-*     → dorado — acento, botón hero CTA
  gold-50    fondo de badge
  gold-100   borde de badge
  gold-400   hover del botón hero
  gold-500   botón "Nuevo X" en el hero (texto forest-950)
  gold-700   texto de badge gold

cream-*    → crema — fondos y bordes de contenido
  cream-50   hover de filas de tabla
  cream-100  bordes internos (divisores)
  cream-200  bordes de inputs, cards, tablas

slate-*    → gris estándar Tailwind — texto general
  slate-400  placeholders, textos secundarios
  slate-500  texto de apoyo
  slate-600  texto normal en tablas
  slate-700  labels de formularios, texto principal
  slate-800  títulos de modales, headings de sección
```

---

## Patrón del Hero (cabecera de página)

Todas las páginas del tenant usan este hero. **Sin excepciones.**

```blade
<div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
    <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
        <div>
            <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">
                Sección / Módulo
            </p>
            <h1 class="font-display text-2xl font-bold text-white">Título de la página</h1>
            <p class="text-forest-300 text-sm mt-1">Descripción breve del módulo</p>
        </div>
        {{-- Botón CTA opcional --}}
        @if(! session('audit_mode') && ! session('reference_mode'))
            <button wire:click="openCreate"
                class="flex items-center gap-2 px-4 py-2 bg-gold-500 text-forest-950 text-sm font-bold rounded-xl hover:bg-gold-400 transition shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo X
            </button>
        @endif
    </div>
</div>
```

> Usar `max-w-2xl` en lugar de `max-w-7xl` para páginas de configuración/formulario único.

---

## Patrón de tabla estándar

```blade
<div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
    <table class="w-full text-sm">
        <thead>
            <tr class="bg-forest-950 border-b border-forest-800">
                <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide">
                    Columna
                </th>
                {{-- columna de acciones sin texto --}}
                <th class="px-6 py-3.5"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-cream-100">
            @forelse($items as $item)
                <tr wire:key="item-{{ $item->id }}" class="hover:bg-cream-50 transition">
                    <td class="px-6 py-3 text-sm text-slate-700">{{ $item->name }}</td>
                    <td class="px-6 py-3 text-right">
                        {{-- acciones --}}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="N" class="px-6 py-12 text-center text-slate-400 text-sm">
                        No hay registros.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{-- paginación --}}
    @if($items->hasPages())
        <div class="px-6 py-4 border-t border-cream-100">{{ $items->links() }}</div>
    @endif
</div>
```

---

## Patrón de modal estándar

```blade
@if($showForm && ! session('audit_mode') && ! session('reference_mode'))
<div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg my-8 flex flex-col">

        {{-- Header sticky --}}
        <div class="px-6 py-5 border-b border-cream-100 flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
            <h3 class="text-base font-semibold text-slate-800">
                {{ $editingId ? 'Editar X' : 'Nuevo X' }}
            </h3>
            <button wire:click="cancelForm"
                class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <div class="px-6 py-5 space-y-4">
            {{-- campos del formulario --}}
        </div>

        {{-- Footer sticky --}}
        <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3 sticky bottom-0 bg-white rounded-b-2xl">
            <button wire:click="cancelForm"
                class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">
                Cancelar
            </button>
            <button wire:click="save" wire:loading.attr="disabled"
                class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                <span wire:loading.remove wire:target="save">{{ $editingId ? 'Actualizar' : 'Guardar' }}</span>
                <span wire:loading wire:target="save">Guardando…</span>
            </button>
        </div>

    </div>
</div>
@endif
```

> **NUNCA** agregar `wire:click.self` ni `@click.self` al overlay — cierra el modal al presionar Ctrl.
> El modal grande (max-w-2xl o más) usa `overflow-y-auto` en el overlay.
> El modal pequeño (max-w-md) puede usar `items-center` en lugar de `items-start`.

---

## Campos de formulario estándar

```blade
{{-- Input texto --}}
<div>
    <label class="block text-sm font-medium text-slate-700 mb-1.5">Campo *</label>
    <input wire:model="campo" type="text"
        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
    @error('campo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
</div>

{{-- Select --}}
<select wire:model="campo"
    class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
    <option value="">Seleccione...</option>
</select>

{{-- Nota de ayuda bajo un campo --}}
<p class="text-slate-400 text-xs mt-1">Texto de ayuda contextual</p>

{{-- Grid de 2 columnas --}}
<div class="grid grid-cols-2 gap-4">
    ...
</div>
```

---

## Badges de estado y tipo

```php
// Tipos de tercero
'cliente'   => 'bg-blue-50 text-blue-700 border border-blue-100'
'proveedor' => 'bg-violet-50 text-violet-700 border border-violet-100'
'empleado'  => 'bg-emerald-50 text-emerald-700 border border-emerald-100'
'ambos'     => 'bg-gold-50 text-gold-700 border border-gold-100'

// Tipos PUC
'activo'     => 'bg-blue-50 text-blue-700 border border-blue-100'
'pasivo'     => 'bg-red-50 text-red-700 border border-red-100'
'patrimonio' => 'bg-violet-50 text-violet-700 border border-violet-100'
'ingreso'    => 'bg-green-50 text-green-700 border border-green-100'
'gasto'      => 'bg-orange-50 text-orange-700 border border-orange-100'
'costo'      => 'bg-gold-50 text-gold-700 border border-gold-100'
'orden'      => 'bg-slate-100 text-slate-600 border border-slate-200'
```

Estructura del badge:
```blade
<span class="px-2 py-0.5 rounded-lg text-xs font-medium {{ $colorClases }}">
    Etiqueta
</span>
```

---

## Botones

```blade
{{-- Primario (guardar, confirmar) --}}
<button class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
    Guardar
</button>

{{-- CTA hero (nuevo registro) --}}
<button class="flex items-center gap-2 px-4 py-2 bg-gold-500 text-forest-950 text-sm font-bold rounded-xl hover:bg-gold-400 transition">
    Nuevo X
</button>

{{-- Secundario / Cancelar --}}
<button class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">
    Cancelar
</button>

{{-- Acción inline en tabla (editar) --}}
<button class="text-xs text-forest-600 hover:text-forest-800 font-semibold px-2 py-1 rounded-lg hover:bg-forest-50 transition">
    Editar
</button>

{{-- Destructivo inline (eliminar) --}}
<button class="text-xs text-red-500 hover:text-red-700 font-semibold px-2 py-1 rounded-lg hover:bg-red-50 transition">
    Eliminar
</button>

{{-- Toggle activo/inactivo (segmented) --}}
<button class="px-4 py-1.5 rounded-lg text-sm font-medium border transition
    {{ $activo ? 'bg-forest-700 text-white border-forest-700' : 'bg-white text-slate-600 border-cream-200 hover:border-slate-300' }}">
    Opción
</button>
```

---

## Iconos — siempre SVG Heroicons

**NUNCA usar emojis** (🧪, 📄, ✓, ✕, etc.). Siempre SVG inline de Heroicons.

```blade
{{-- Plus (agregar) --}}
<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
</svg>

{{-- X (cerrar modal) --}}
<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
</svg>

{{-- Chevron derecha (siguiente) --}}
<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
</svg>

{{-- Lupa (buscar) --}}
<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
</svg>

{{-- Pencil (editar) --}}
<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
</svg>

{{-- Trash (eliminar) --}}
<svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
</svg>
```

---

## Buscador estándar

```blade
<div class="relative w-full sm:w-80">
    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"
        fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round"
            d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
    </svg>
    <input wire:model.live.debounce.300ms="search" type="text"
        placeholder="Buscar…"
        class="w-full pl-9 rounded-xl border-cream-200 text-sm shadow-sm focus:ring-forest-500 focus:border-forest-500" />
</div>
```

---

## Tabs dentro de modal

Solo se usan cuando un formulario tiene demasiados campos (ej: Empleado).

```blade
<div class="flex border-b border-cream-200">
    <button wire:click="setTab('basico')" type="button"
        class="px-4 py-2.5 text-sm font-medium border-b-2 transition -mb-px
            {{ $activeTab === 'basico'
                ? 'border-forest-700 text-forest-800'
                : 'border-transparent text-slate-500 hover:text-slate-700' }}">
        Datos básicos
    </button>
    <button wire:click="setTab('laboral')" type="button"
        class="px-4 py-2.5 text-sm font-medium border-b-2 transition -mb-px
            {{ $activeTab === 'laboral'
                ? 'border-forest-700 text-forest-800'
                : 'border-transparent text-slate-500 hover:text-slate-700' }}">
        Información laboral
    </button>
</div>
```

---

## Cards de contenido

```blade
<div class="bg-white rounded-2xl border border-cream-200 shadow-card p-6">
    {{-- contenido --}}
</div>

{{-- Card con secciones divididas --}}
<div class="bg-white rounded-2xl border border-cream-200 shadow-card divide-y divide-cream-100">
    <div class="px-6 py-5">Sección 1</div>
    <div class="px-6 py-5">Sección 2</div>
</div>
```

---

## Texto en mayúsculas de BD → mostrar en título

Los nombres del PUC y otros campos se guardan en MAYÚSCULAS en la BD.
Al mostrar en vistas usar:

```blade
{{ ucwords(strtolower($account->name)) }}
{{-- "CUENTAS POR PAGAR" → "Cuentas Por Pagar" --}}
```
