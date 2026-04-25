<div>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Banco de ejercicios</h2>
                <p class="text-xs text-slate-500 mt-0.5">Ejercicios oficiales disponibles para todos los docentes</p>
            </div>
            <div class="flex items-center gap-2">
                {{-- Importar Excel --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                        class="flex items-center gap-1.5 px-3 py-1.5 border border-forest-300 text-forest-700 text-xs font-semibold rounded-lg hover:bg-forest-50 transition">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5"/>
                        </svg>
                        Importar Excel
                    </button>
                    <div x-show="open" @click.outside="open = false"
                        class="absolute right-0 top-full mt-1 bg-white border border-cream-200 rounded-xl shadow-lg p-4 w-72 z-10"
                        style="display:none">
                        <p class="text-xs text-slate-500 mb-3">Sube un archivo .xlsx con los ejercicios. Máx. 2 MB.</p>
                        <input wire:model="ejerciciosFile" type="file" accept=".xlsx,.xls"
                            class="block w-full text-xs text-slate-500 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:bg-forest-50 file:text-forest-700 file:text-xs file:font-medium hover:file:bg-forest-100 mb-3">
                        @error('ejerciciosFile') <p class="text-xs text-red-600 mb-2">{{ $message }}</p> @enderror
                        <button wire:click="importEjercicios" wire:loading.attr="disabled"
                            class="w-full px-3 py-1.5 bg-forest-800 text-white text-xs font-semibold rounded-lg hover:bg-forest-700 transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="importEjercicios">Importar</span>
                            <span wire:loading wire:target="importEjercicios">Procesando…</span>
                        </button>
                    </div>
                </div>

                <a href="{{ route('admin.ejercicios.plantilla') }}"
                    class="flex items-center gap-1.5 px-3 py-1.5 border border-slate-200 text-slate-600 text-xs font-semibold rounded-lg hover:bg-slate-50 transition">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Plantilla
                </a>

                <button wire:click="openForm()"
                    class="px-3 py-1.5 bg-forest-800 text-white text-xs font-semibold rounded-lg hover:bg-forest-700 transition">
                    + Nuevo ejercicio
                </button>
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto px-6 py-8 space-y-6">

        {{-- Resultado de importación --}}
        @if($importResult)
            <div class="bg-white rounded-2xl border {{ $importResult['errors'] ? 'border-amber-200' : 'border-green-200' }} shadow-card-sm px-6 py-4">
                <div class="flex items-start gap-3">
                    <div class="flex-1">
                        <p class="text-sm font-semibold {{ $importResult['errors'] ? 'text-amber-700' : 'text-green-700' }}">
                            {{ $importResult['imported'] }} ejercicio(s) importado(s) correctamente.
                            @if($importResult['errors']) {{ count($importResult['errors']) }} fila(s) con error. @endif
                        </p>
                        @if($importResult['errors'])
                            <ul class="mt-2 space-y-0.5">
                                @foreach($importResult['errors'] as $err)
                                    <li class="text-xs text-amber-600">Fila {{ $err['fila'] }}: {{ $err['error'] }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                    <button wire:click="$set('importResult', null)" class="text-slate-400 hover:text-slate-600 text-lg leading-none">&times;</button>
                </div>
            </div>
        @endif

        {{-- Tabla --}}
        <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-cream-50 text-xs text-slate-500 uppercase tracking-wide border-b border-cream-200">
                    <tr>
                        <th class="px-6 py-3 text-left">Ejercicio</th>
                        <th class="px-6 py-3 text-left">Tipo</th>
                        <th class="px-6 py-3 text-right">Monto mín.</th>
                        <th class="px-6 py-3 text-right">Pts</th>
                        <th class="px-6 py-3 text-right">Asignaciones</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-100">
                    @forelse($exercises as $ex)
                        <tr wire:key="ex-{{ $ex->id }}" class="hover:bg-cream-50 transition">
                            <td class="px-6 py-3">
                                <p class="font-medium text-slate-800">{{ $ex->title }}</p>
                                @if($ex->instructions)
                                    <p class="text-xs text-slate-400 truncate max-w-sm">{{ $ex->instructions }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-slate-500 text-xs">
                                {{ \App\Models\Central\Exercise::typeLabel($ex->type) }}
                            </td>
                            <td class="px-6 py-3 text-right text-slate-500 text-xs">
                                {{ $ex->monto_minimo ? '$ '.number_format($ex->monto_minimo, 0, ',', '.') : '—' }}
                            </td>
                            <td class="px-6 py-3 text-right font-semibold text-forest-700">{{ $ex->puntos }}</td>
                            <td class="px-6 py-3 text-right text-slate-400 text-xs">{{ $ex->assignments_count }}</td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button wire:click="openForm({{ $ex->id }})"
                                        class="text-xs text-forest-600 hover:text-forest-800 font-medium">Editar</button>
                                    <button wire:click="delete({{ $ex->id }})"
                                        wire:confirm="¿Eliminar este ejercicio oficial? Los docentes que lo hayan clonado conservarán su copia."
                                        class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                No hay ejercicios oficiales.
                                <button wire:click="openForm()" class="ml-2 text-forest-600 hover:underline">Crear el primero</button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="text-xs text-slate-400">
            Los docentes ven estos ejercicios en su panel y pueden clonarlos a su banco personal con un click.
        </p>
    </div>

    {{-- Modal --}}
    @if($showForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-card-lg w-full max-w-lg">
                <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">{{ $editingId ? 'Editar ejercicio oficial' : 'Nuevo ejercicio oficial' }}</h3>
                    <button wire:click="$set('showForm',false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Título</label>
                        <input wire:model="gTitle" type="text" class="w-full border border-cream-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-forest-500 focus:border-forest-500">
                        @error('gTitle') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Instrucciones</label>
                        <textarea wire:model="gInstructions" rows="3" class="w-full border border-cream-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-forest-500 focus:border-forest-500 resize-none"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tipo</label>
                            <select wire:model="gType" class="w-full border border-cream-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-forest-500">
                                <option value="factura_venta">Factura de venta</option>
                                <option value="factura_compra">Factura de compra</option>
                                <option value="asiento_manual">Asiento contable</option>
                                <option value="registro_tercero">Registro de tercero</option>
                                <option value="registro_producto">Registro de producto</option>
                                <option value="pago_proveedor">Pago a proveedor</option>
                            </select>
                            @error('gType') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Puntos</label>
                            <input wire:model="gPuntos" type="number" min="1" max="100" class="w-full border border-cream-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-forest-500">
                            @error('gPuntos') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Monto mínimo (opcional)</label>
                            <input wire:model="gMontoMinimo" type="number" min="0" step="0.01" placeholder="0.00" class="w-full border border-cream-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-forest-500">
                            @error('gMontoMinimo') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Cuenta PUC requerida (opcional)</label>
                            <input wire:model="gCuentaPuc" type="text" placeholder="ej. 1105" class="w-full border border-cream-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-forest-500">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showForm',false)"
                        class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">Guardar</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
