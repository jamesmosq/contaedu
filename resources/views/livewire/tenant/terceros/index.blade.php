<div>

    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Maestros contables</p>
                <h1 class="font-display text-2xl font-bold text-white">Terceros</h1>
                <p class="text-forest-300 text-sm mt-1">Clientes y proveedores de la empresa</p>
            </div>
            @if(! session('audit_mode') && ! session('reference_mode'))
                <button wire:click="openCreate"
                    class="flex items-center gap-2 px-4 py-2 bg-gold-500 text-forest-950 text-sm font-bold rounded-xl hover:bg-gold-400 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nuevo tercero
                </button>
            @endif
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">

            {{-- Barra de filtros --}}
            <div class="flex flex-wrap items-center gap-3 mb-6">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o documento…"
                        class="pl-9 rounded-xl border-cream-200 text-sm shadow-sm focus:ring-forest-500 focus:border-forest-500 w-72" />
                </div>
                <select wire:model.live="filterType" class="rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                    <option value="">Todos los tipos</option>
                    <option value="cliente">Clientes</option>
                    <option value="proveedor">Proveedores</option>
                    <option value="empleado">Empleados</option>
                </select>
            </div>

            {{-- Tabla --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-forest-950 border-b border-forest-800">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide">Tercero</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide hidden sm:table-cell">Documento</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide">Tipo</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide hidden md:table-cell">Régimen</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide hidden lg:table-cell">Ciudad</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide hidden lg:table-cell">Contacto</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cream-100">
                        @forelse($thirds as $third)
                            <tr wire:key="third-{{ $third->id }}" class="hover:bg-cream-50 transition">
                                <td class="px-6 py-3 font-medium text-slate-700">{{ $third->name }}</td>
                                <td class="px-6 py-3 text-slate-500 font-mono text-xs uppercase hidden sm:table-cell">{{ strtoupper($third->document_type) }} {{ $third->document }}</td>
                                <td class="px-6 py-3">
                                    @php
                                        $colors = [
                                            'cliente'   => 'bg-blue-50 text-blue-700 border border-blue-100',
                                            'proveedor' => 'bg-violet-50 text-violet-700 border border-violet-100',
                                            'empleado'  => 'bg-emerald-50 text-emerald-700 border border-emerald-100',
                                            'ambos'     => 'bg-gold-50 text-gold-700 border border-gold-100',
                                        ];
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-medium {{ $colors[$third->type->value] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $third->type->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-xs text-slate-500 capitalize hidden md:table-cell">{{ $third->regimen }}</td>
                                <td class="px-6 py-3 text-xs text-slate-500 hidden lg:table-cell">
                                    @if($third->municipio_codigo && isset($municipioMap[$third->municipio_codigo]))
                                        <span title="{{ $municipioMap[$third->municipio_codigo]->departamento }}">
                                            {{ $municipioMap[$third->municipio_codigo]->municipio }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 text-xs text-slate-500 hidden lg:table-cell">
                                    {{ collect([$third->phone, $third->email])->filter()->implode(' · ') }}
                                </td>
                                <td class="px-6 py-3 text-right">
                                    @if(! session('audit_mode') && ! session('reference_mode'))
                                        <div class="flex items-center justify-end gap-2">
                                            <button wire:click="openEdit({{ $third->id }})"
                                                class="text-xs text-forest-600 hover:text-forest-800 font-semibold px-2 py-1 rounded-lg hover:bg-forest-50 transition">
                                                Editar
                                            </button>
                                            <button x-on:click="confirmAction('¿Eliminar este tercero?', () => $wire.delete({{ $third->id }}), { danger: true, confirmText: 'Sí, eliminar' })"
                                                class="text-xs text-red-500 hover:text-red-700 font-semibold px-2 py-1 rounded-lg hover:bg-red-50 transition">
                                                Eliminar
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <p class="text-slate-400 text-sm mb-2">No hay terceros registrados.</p>
                                    @if(! session('audit_mode') && ! session('reference_mode'))
                                        <button wire:click="openCreate" class="text-sm text-forest-600 hover:text-forest-800 font-semibold hover:underline">
                                            Crear el primero
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($thirds->hasPages())
                    <div class="px-6 py-4 border-t border-cream-100">{{ $thirds->links() }}</div>
                @endif
            </div>

        </div>
    </div>

    {{-- ═══ Modal: Nuevo / Editar tercero ═══ --}}
    @if($showForm && ! session('audit_mode') && ! session('reference_mode'))
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg my-8 flex flex-col">
                <div class="px-6 py-5 border-b border-cream-100 flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
                    <h3 class="text-base font-semibold text-slate-800">{{ $editingId ? 'Editar tercero' : 'Nuevo tercero' }}</h3>
                    <button wire:click="cancelForm" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition text-sm">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">

                    {{-- Tipo de tercero (botones) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de tercero *</label>
                        <div class="flex gap-2 flex-wrap">
                            @foreach(['cliente' => 'Cliente', 'proveedor' => 'Proveedor', 'empleado' => 'Empleado'] as $val => $lbl)
                                <button wire:click="$set('type','{{ $val }}')" type="button"
                                    class="px-4 py-1.5 rounded-lg text-sm font-medium border transition
                                        {{ $type === $val
                                            ? ($val === 'empleado' ? 'bg-emerald-600 text-white border-emerald-600' : ($val === 'proveedor' ? 'bg-violet-600 text-white border-violet-600' : 'bg-forest-700 text-white border-forest-700'))
                                            : 'bg-white text-slate-600 border-cream-200 hover:border-slate-300' }}">
                                    {{ $lbl }}
                                </button>
                            @endforeach
                        </div>
                        @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo documento</label>
                            <select wire:model="document_type" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                <option value="nit">NIT</option>
                                <option value="cc">Cédula (CC)</option>
                                <option value="ce">Cédula extranjería</option>
                                <option value="pasaporte">Pasaporte</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Número documento</label>
                            <input wire:model="document" type="text" inputmode="numeric" pattern="[0-9\-]+" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('document') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            {{ $type === 'empleado' ? 'Nombre completo' : 'Razón social / Nombre' }}
                        </label>
                        <input wire:model="name" type="text" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if($type !== 'empleado')
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Régimen</label>
                            <select wire:model="regimen" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                <option value="simplificado">Simplificado</option>
                                <option value="comun">Común</option>
                            </select>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Teléfono</label>
                            <input wire:model="phone" type="tel" inputmode="tel" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Correo</label>
                            <input wire:model="email" type="email" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Dirección</label>
                        <input wire:model="address" type="text" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                    </div>

                    {{-- Municipio DIAN (autocomplete) --}}
                    <div x-data="{ open: false }" @click.outside="open = false">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Ciudad / Municipio</label>
                        <input
                            wire:model.live.debounce.300ms="municipioSearch"
                            x-on:focus="open = true"
                            type="text"
                            placeholder="Escriba para buscar…"
                            autocomplete="off"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500"
                        />
                        @if($municipios->isNotEmpty())
                            <div x-show="open"
                                class="mt-1 border border-cream-200 rounded-xl bg-white shadow-lg z-50 max-h-48 overflow-y-auto divide-y divide-cream-100">
                                @foreach($municipios as $m)
                                    <button type="button"
                                        wire:click="selectMunicipio('{{ $m->codigo }}', '{{ addslashes($m->label) }}')"
                                        x-on:click="open = false"
                                        class="w-full text-left px-4 py-2.5 hover:bg-forest-50 transition text-sm">
                                        <span class="font-medium text-slate-700">{{ $m->municipio }}</span>
                                        <span class="text-slate-400 text-xs ml-1">— {{ $m->departamento }}</span>
                                        <span class="text-slate-300 text-xs ml-1">({{ $m->codigo }})</span>
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        @error('municipio_codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- ── Campos laborales (solo empleados) ── --}}
                    @if($type === 'empleado')
                        <div class="border-t border-cream-100 pt-4 space-y-4">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Información laboral</p>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Cargo *</label>
                                    <input wire:model="cargo" type="text" placeholder="ej: Contador" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                    @error('cargo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Fecha de ingreso *</label>
                                    <input wire:model="fecha_ingreso" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                    @error('fecha_ingreso') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Salario básico mensual *</label>
                                    <input wire:model="salario_basico" type="number" step="1000" min="0" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                    @error('salario_basico') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo de contrato *</label>
                                    <select wire:model="tipo_contrato" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                        <option value="indefinido">Indefinido</option>
                                        <option value="fijo">Fijo</option>
                                        <option value="obra_labor">Obra / Labor</option>
                                        <option value="prestacion_servicios">Prestación de servicios</option>
                                    </select>
                                    @error('tipo_contrato') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Procedimiento de retención *
                                    <span class="text-slate-400 font-normal">(Art. 383 E.T.)</span>
                                </label>
                                <div class="flex gap-3">
                                    <button wire:click="$set('procedimiento_retencion','1')" type="button"
                                        class="flex-1 py-2 px-3 rounded-xl border text-sm font-medium transition
                                            {{ $procedimiento_retencion === '1' ? 'bg-forest-700 text-white border-forest-700' : 'bg-white text-slate-600 border-cream-200 hover:border-slate-300' }}">
                                        Procedimiento 1 — Mensual fijo
                                    </button>
                                    <button wire:click="$set('procedimiento_retencion','2')" type="button"
                                        class="flex-1 py-2 px-3 rounded-xl border text-sm font-medium transition
                                            {{ $procedimiento_retencion === '2' ? 'bg-forest-700 text-white border-forest-700' : 'bg-white text-slate-600 border-cream-200 hover:border-slate-300' }}">
                                        Procedimiento 2 — Promedio 6 meses
                                    </button>
                                </div>
                                @error('procedimiento_retencion') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">EPS</label>
                                    <input wire:model="eps" type="text" placeholder="ej: Sura" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">AFP (pensión)</label>
                                    <input wire:model="afp" type="text" placeholder="ej: Porvenir" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">ARL</label>
                                    <input wire:model="arl" type="text" placeholder="ej: Positiva" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Fecha de retiro</label>
                                    <input wire:model="fecha_retiro" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                    @error('fecha_retiro') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3 sticky bottom-0 bg-white rounded-b-2xl">
                    <button wire:click="cancelForm" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Actualizar' : 'Guardar' }}</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
