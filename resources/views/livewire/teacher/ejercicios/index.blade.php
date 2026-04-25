<div>

    {{-- ── Hero ─────────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Panel Docente</p>
                <h1 class="font-display text-2xl font-bold text-white">Ejercicios</h1>
                <p class="text-forest-300 text-sm mt-1">Crea ejercicios y asígnalos a tus grupos con fecha límite</p>
            </div>
            <button wire:click="openForm()"
                class="flex items-center gap-2 px-4 py-2 bg-gold-500 hover:bg-gold-400 text-forest-950 text-sm font-semibold rounded-xl transition shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nuevo ejercicio
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-8 space-y-6">

        {{-- ── Tabs ──────────────────────────────────────────────────────────── --}}
        <div class="flex gap-1 bg-cream-100 p-1 rounded-xl w-fit">
            @foreach(['ejercicios' => 'Mis ejercicios', 'asignar' => 'Asignaciones', 'resultados' => 'Resultados'] as $key => $label)
                <button wire:click="$set('tab', '{{ $key }}')"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition
                        {{ $tab === $key ? 'bg-white text-forest-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>

        {{-- ── Tab: Mis ejercicios ───────────────────────────────────────────── --}}
        @if($tab === 'ejercicios')
            @if($exercises->isEmpty())
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-6 py-16 text-center">
                    <div class="w-14 h-14 bg-forest-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-forest-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-700 mb-1">Sin ejercicios</h3>
                    <p class="text-slate-400 text-xs">Crea tu primer ejercicio para asignarlo a tus grupos.</p>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-cream-50 border-b border-cream-200">
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Ejercicio</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                                <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Pts</th>
                                <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asignaciones</th>
                                <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                                <th class="px-5 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @foreach($exercises as $ex)
                                <tr class="hover:bg-cream-50/50 transition-colors">
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-slate-800">{{ $ex->title }}</p>
                                        @if($ex->monto_minimo)
                                            <p class="text-xs text-slate-400">Mínimo: ${{ number_format($ex->monto_minimo, 0, ',', '.') }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-forest-50 text-forest-700">
                                            {{ \App\Models\Central\Exercise::typeLabel($ex->type) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-center font-semibold text-slate-700">{{ $ex->puntos }}</td>
                                    <td class="px-5 py-4 text-center text-slate-600">{{ $ex->assignments_count }}</td>
                                    <td class="px-5 py-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $ex->active ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                            {{ $ex->active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button wire:click="openAssign({{ $ex->id }})"
                                                class="px-3 py-1.5 text-xs font-medium text-forest-700 border border-forest-200 rounded-lg hover:bg-forest-50 transition">
                                                Asignar
                                            </button>
                                            <button wire:click="openForm({{ $ex->id }})"
                                                class="p-1.5 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z"/>
                                                </svg>
                                            </button>
                                            <button wire:click="toggleActive({{ $ex->id }})"
                                                class="p-1.5 {{ $ex->active ? 'text-amber-400 hover:text-amber-600' : 'text-slate-300 hover:text-green-500' }} hover:bg-slate-100 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- Catálogo de ejercicios oficiales --}}
            @if($globalExercises->isNotEmpty())
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-cream-100">
                        <h3 class="text-sm font-semibold text-slate-700">Ejercicios oficiales</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Creados por ContaEdu. Usa uno como punto de partida y personalízalo.</p>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-cream-50 text-xs text-slate-500 uppercase tracking-wide border-b border-cream-200">
                            <tr>
                                <th class="px-5 py-3 text-left">Título</th>
                                <th class="px-5 py-3 text-left">Tipo</th>
                                <th class="px-5 py-3 text-right">Pts</th>
                                <th class="px-5 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @foreach($globalExercises as $gex)
                                <tr wire:key="gex-{{ $gex->id }}" class="hover:bg-cream-50">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-slate-700">{{ $gex->title }}</p>
                                        @if($gex->instructions)
                                            <p class="text-xs text-slate-400 truncate max-w-xs">{{ $gex->instructions }}</p>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3 text-xs text-slate-500">{{ \App\Models\Central\Exercise::typeLabel($gex->type) }}</td>
                                    <td class="px-5 py-3 text-right text-forest-700 font-semibold text-xs">{{ $gex->puntos }}</td>
                                    <td class="px-5 py-3 text-right">
                                        <button wire:click="cloneGlobal({{ $gex->id }})"
                                            class="px-3 py-1.5 text-xs font-medium text-forest-700 border border-forest-200 bg-forest-50 rounded-lg hover:bg-forest-100 transition">
                                            Usar este ejercicio
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif

        {{-- ── Tab: Asignaciones ─────────────────────────────────────────────── --}}
        @if($tab === 'asignar')
            @if($assignments->isEmpty())
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-6 py-16 text-center">
                    <h3 class="text-sm font-semibold text-slate-700 mb-1">Sin asignaciones</h3>
                    <p class="text-slate-400 text-xs">Crea un ejercicio y asígnalo a un grupo.</p>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-cream-50 border-b border-cream-200">
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Ejercicio</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Grupo</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Asignado</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha límite</th>
                                <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Entregas</th>
                                <th class="px-5 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @foreach($assignments as $asgn)
                                <tr class="hover:bg-cream-50/50 transition-colors">
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-slate-800">{{ $asgn->exercise->title }}</p>
                                        <p class="text-xs text-slate-400">{{ \App\Models\Central\Exercise::typeLabel($asgn->exercise->type) }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $asgn->group->name }}</td>
                                    <td class="px-5 py-4 text-slate-600">{{ $asgn->assigned_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-4 text-slate-600">
                                        {{ $asgn->due_date ? $asgn->due_date->format('d/m/Y') : '—' }}
                                        @if($asgn->due_date && $asgn->due_date->isPast())
                                            <span class="text-red-500 text-xs ml-1">Vencida</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center text-slate-600">{{ $asgn->completions_count }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button wire:click="viewResults({{ $asgn->id }})"
                                                class="px-3 py-1.5 text-xs font-medium text-forest-700 border border-forest-200 rounded-lg hover:bg-forest-50 transition">
                                                Ver resultados
                                            </button>
                                            <button
                                                x-on:click="confirmAction('¿Eliminar esta asignación?', () => $wire.deleteAssignment({{ $asgn->id }}), { danger: true, confirmText: 'Sí, eliminar' })"
                                                class="p-1.5 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif

        {{-- ── Tab: Resultados ──────────────────────────────────────────────── --}}
        @if($tab === 'resultados')
            @if(! $viewingAssignment)
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-6 py-16 text-center">
                    <h3 class="text-sm font-semibold text-slate-700 mb-1">Selecciona una asignación</h3>
                    <p class="text-slate-400 text-xs">Ve a la pestaña "Asignaciones" y haz clic en "Ver resultados".</p>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <div class="px-5 py-4 border-b border-cream-100 flex items-center justify-between">
                        <div>
                            <h3 class="font-semibold text-slate-800">{{ $viewingAssignment->exercise->title }}</h3>
                            <p class="text-xs text-slate-500 mt-0.5">{{ $viewingAssignment->group->name }} · Asignado {{ $viewingAssignment->assigned_at->format('d/m/Y') }}</p>
                        </div>
                        <button wire:click="$set('viewingAssignmentId', null)" class="text-slate-400 hover:text-slate-600 text-sm">← Volver</button>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-cream-50 border-b border-cream-200">
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estudiante</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                                <th class="text-center px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Resultado</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Detalle</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Entregado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @foreach($results as $row)
                                @php $c = $row['completion']; @endphp
                                <tr class="hover:bg-cream-50/50 transition-colors">
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-slate-800">{{ $row['tenant']->student_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $row['tenant']->id }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-600">{{ $row['tenant']->company_name }}</td>
                                    <td class="px-5 py-4 text-center">
                                        @if($c)
                                            @php
                                                $badge = match($c->result) {
                                                    'aprobado' => 'bg-green-100 text-green-700',
                                                    'parcial'  => 'bg-amber-100 text-amber-700',
                                                    'no_cumple'=> 'bg-red-100 text-red-700',
                                                    default    => 'bg-slate-100 text-slate-500',
                                                };
                                                $label = match($c->result) {
                                                    'aprobado' => 'Aprobado',
                                                    'parcial'  => 'Parcial',
                                                    'no_cumple'=> 'No cumple',
                                                    default    => 'Pendiente',
                                                };
                                            @endphp
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
                                        @else
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-400">Sin entregar</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-slate-500 text-xs">
                                        {{ $c?->verification_detail['mensaje'] ?? '—' }}
                                    </td>
                                    <td class="px-5 py-4 text-slate-500 text-xs">
                                        {{ $c?->submitted_at?->format('d/m/Y H:i') ?? '—' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        @endif

    </div>

    {{-- ── Modal: crear / editar ejercicio ──────────────────────────────────── --}}
    @if($showForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
                <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">{{ $editingId ? 'Editar ejercicio' : 'Nuevo ejercicio' }}</h3>
                    <button wire:click="$set('showForm', false)" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Título <span class="text-red-500">*</span></label>
                        <input wire:model="title" type="text" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" placeholder="Ej: Registra tu primera factura de venta" />
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de ejercicio <span class="text-red-500">*</span></label>
                        <select wire:model.live="type" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                            <option value="factura_venta">Factura de venta</option>
                            <option value="factura_compra">Factura de compra</option>
                            <option value="asiento_manual">Asiento contable manual</option>
                            <option value="registro_tercero">Registrar tercero (cliente/proveedor)</option>
                            <option value="registro_producto">Registrar producto</option>
                            <option value="pago_proveedor">Pago a proveedor</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        @if(in_array($type, ['factura_venta', 'factura_compra']))
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Monto mínimo</label>
                            <input wire:model="monto_minimo" type="number" step="1000" min="0" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" placeholder="0 = sin mínimo" />
                        </div>
                        @endif
                        @if($type === 'registro_producto')
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Cuenta PUC requerida</label>
                            <input wire:model="cuenta_puc_requerida" type="text" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" placeholder="Ej: 1435" />
                        </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Puntos</label>
                            <input wire:model="puntos" type="number" min="1" max="100" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Instrucciones para el estudiante</label>
                        <textarea wire:model="instructions" rows="3" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" placeholder="Describe qué debe hacer el estudiante..."></textarea>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showForm', false)" class="px-4 py-2 text-sm text-slate-600">Cancelar</button>
                    <button wire:click="saveExercise" class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                        <span wire:loading.remove wire:target="saveExercise">Guardar</span>
                        <span wire:loading wire:target="saveExercise">Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Modal: asignar a grupo ────────────────────────────────────────────── --}}
    @if($showAssignModal)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">Asignar a grupo</h3>
                    <button wire:click="$set('showAssignModal', false)" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Grupo <span class="text-red-500">*</span></label>
                        <select wire:model="assignGroupId" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                            <option value="0">Selecciona un grupo...</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->period }})</option>
                            @endforeach
                        </select>
                        @error('assignGroupId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha límite <span class="text-slate-400">(opcional)</span></label>
                        <input wire:model="assignDueDate" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showAssignModal', false)" class="px-4 py-2 text-sm text-slate-600">Cancelar</button>
                    <button wire:click="confirmAssign" class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                        <span wire:loading.remove wire:target="confirmAssign">Asignar</span>
                        <span wire:loading wire:target="confirmAssign">Asignando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
