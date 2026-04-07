<div>
    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Maestros contables</p>
                <h1 class="font-display text-2xl font-bold text-white">Activos fijos</h1>
                <p class="text-forest-300 text-sm mt-1">Propiedades, planta y equipo</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ session('audit_mode')
                    ? route('teacher.auditoria.activos-fijos.pdf', ['tenantId' => session('audit_tenant_id')])
                    : (session('demo_mode')
                        ? route('teacher.demo.activos-fijos.pdf', session('demo_tenant_id'))
                        : route('student.activos-fijos.pdf')) }}"
                    target="_blank"
                    class="px-4 py-2 text-sm font-semibold rounded-xl border border-forest-700 text-white bg-forest-800 hover:bg-forest-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                    PDF
                </a>
                @if(!session('audit_mode') && !session('reference_mode'))
                    <button wire:click="openForm"
                        class="px-4 py-2 bg-gold-500 text-white text-sm font-semibold rounded-xl hover:bg-gold-400 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        Nuevo activo
                    </button>
                @endif
            </div>
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- Panel de depreciación mensual --}}
            @if(!session('audit_mode') && !session('reference_mode'))
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-5">
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Registrar depreciación mensual</h3>
                <div class="flex flex-wrap items-end gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Período</label>
                        <input type="month" wire:model="dep_period"
                            class="rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                    </div>
                    <button x-on:click="confirmAction('¿Registrar depreciación para el período seleccionado? Se generarán los asientos contables.', () => $wire.runDepreciation(), { confirmText: 'Sí, registrar' })"
                        class="px-4 py-2 bg-slate-800 text-white text-sm font-semibold rounded-xl hover:bg-slate-700 transition">
                        <span wire:loading.remove wire:target="runDepreciation">Calcular y registrar</span>
                        <span wire:loading wire:target="runDepreciation">Procesando...</span>
                    </button>
                </div>

                @if($showDepResult && count($depResult) > 0)
                    <div class="mt-4 bg-slate-50 rounded-xl p-4 border border-cream-200">
                        <div class="flex items-center gap-4 mb-3">
                            <span class="text-sm font-semibold text-slate-700">Resultado:</span>
                            <span class="px-2 py-0.5 rounded bg-green-100 text-green-700 text-xs font-semibold">{{ $depResult['registrados'] }} activo(s) depreciados</span>
                            <span class="text-sm font-mono font-bold text-slate-800">Total: $ {{ number_format($depResult['total_depreciacion'], 0, ',', '.') }}</span>
                        </div>
                        <div class="space-y-1">
                            @foreach($depResult['detalles'] as $det)
                                @php
                                    $icon = match($det['estado']) {
                                        'registrado'     => '✅',
                                        'ya_depreciado'  => '⏭️',
                                        'completado'     => '🏁',
                                        default          => '—',
                                    };
                                    $label = match($det['estado']) {
                                        'registrado'     => '$ '.number_format($det['monto'], 0, ',', '.'),
                                        'ya_depreciado'  => 'Ya depreciado este período',
                                        'completado'     => 'Totalmente depreciado',
                                        default          => '',
                                    };
                                @endphp
                                <div class="text-xs text-slate-600 flex items-center gap-2">
                                    <span>{{ $icon }}</span>
                                    <span class="font-medium">{{ $det['asset'] }}</span>
                                    <span class="text-slate-400">{{ $label }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
            @endif

            {{-- Tabla de activos --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-forest-950 border-b border-forest-800">
                            <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Código / Nombre</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Categoría</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Adquisición</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Costo histórico</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Dep. acumulada</th>
                            <th class="text-right px-5 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Valor en libros</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Progreso</th>
                            <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Estado</th>
                            @if(!session('audit_mode') && !session('reference_mode'))
                                <th class="px-5 py-3"></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($assets as $asset)
                            @php $statusColor = $asset->status->color(); @endphp
                            <tr wire:key="af-{{ $asset->id }}" class="hover:bg-slate-50">
                                <td class="px-5 py-3">
                                    <p class="font-mono text-xs text-slate-500">{{ $asset->code }}</p>
                                    <p class="font-medium text-slate-800">{{ $asset->name }}</p>
                                    @if($asset->description)
                                        <p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($asset->description, 60) }}</p>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs text-slate-600">{{ $asset->category->label() }}</td>
                                <td class="px-5 py-3 text-slate-600">{{ $asset->acquisition_date->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-right font-mono text-sm text-slate-800">$ {{ number_format($asset->cost, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-mono text-sm text-red-600">$ {{ number_format($asset->accumulated_depreciation, 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-mono text-sm font-semibold text-slate-800">$ {{ number_format($asset->bookValue(), 0, ',', '.') }}</td>
                                <td class="px-5 py-3 min-w-[120px]">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-slate-200 rounded-full h-1.5">
                                            <div class="bg-forest-600 h-1.5 rounded-full transition-all" style="width: {{ $asset->depreciationProgress() }}%"></div>
                                        </div>
                                        <span class="text-xs text-slate-500 w-10 text-right">{{ $asset->depreciationProgress() }}%</span>
                                    </div>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $asset->monthsDepreciated() }}/{{ $asset->useful_life_months }} meses</p>
                                </td>
                                <td class="px-5 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium bg-{{ $statusColor }}-50 text-{{ $statusColor }}-700">
                                        {{ $asset->status->label() }}
                                    </span>
                                    @if($asset->last_depreciation_date)
                                        <p class="text-xs text-slate-400 mt-0.5">Última dep: {{ $asset->last_depreciation_date->format('m/Y') }}</p>
                                    @endif
                                </td>
                                @if(!session('audit_mode') && !session('reference_mode'))
                                    <td class="px-5 py-3">
                                        @if($asset->isActive())
                                            <button x-on:click="confirmAction('¿Dar de baja este activo? No se puede revertir.', () => $wire.retire({{ $asset->id }}), { danger: true, confirmText: 'Sí, dar de baja' })"
                                                class="text-xs px-2 py-1 rounded bg-red-50 text-red-700 hover:bg-red-100 transition">
                                                Dar de baja
                                            </button>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ (session('audit_mode') || session('reference_mode')) ? 8 : 9 }}" class="px-6 py-10 text-center text-slate-400">
                                    No hay activos fijos registrados. Crea el primero con el botón <strong>Nuevo activo</strong>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($assets->count() > 0)
                        <tfoot class="bg-slate-50 border-t-2 border-slate-200">
                            <tr>
                                <td colspan="3" class="px-5 py-3 text-xs font-bold text-slate-600">TOTALES ({{ $assets->count() }} activos)</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-slate-800">$ {{ number_format($assets->sum('cost'), 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-red-600">$ {{ number_format($assets->sum('accumulated_depreciation'), 0, ',', '.') }}</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-slate-800">$ {{ number_format($assets->sum(fn($a) => $a->bookValue()), 0, ',', '.') }}</td>
                                <td colspan="{{ (session('audit_mode') || session('reference_mode')) ? 2 : 3 }}"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            {{-- Nota educativa --}}
            <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4 text-xs text-amber-800">
                <strong>Depreciación línea recta:</strong> Cuota mensual = (Costo − Valor residual) ÷ Vida útil en meses.
                Según la normativa colombiana (NIC 16 NIIF para pymes): equipos de cómputo 3 años, maquinaria 10 años, vehículos 5 años, edificios 20 años.
                El asiento generado es: <strong>DR 5160 Gasto depreciación / CR 1592 Depreciación acumulada</strong>.
            </div>

        </div>
    </div>

    {{-- MODAL: Nuevo activo --}}
    @if($showForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 p-8 max-h-screen overflow-y-auto">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-800">Registrar activo fijo</h2>
                    <button wire:click="$set('showForm', false)" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                </div>

                <div class="space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Código</label>
                            <input wire:model="fa_code" type="text" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('fa_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Categoría</label>
                            <select wire:model.live="fa_category" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del activo</label>
                        <input wire:model="fa_name" type="text" placeholder="Ej: Computador portátil Dell Inspiron 15"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('fa_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-slate-400">(opcional)</span></label>
                        <input wire:model="fa_desc" type="text" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de adquisición</label>
                            <input wire:model="fa_date" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('fa_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Vida útil (meses)</label>
                            <input wire:model="fa_months" type="number" min="1" max="600"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            <p class="text-xs text-slate-400 mt-0.5">{{ round($fa_months / 12, 1) }} años</p>
                            @error('fa_months') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Costo histórico</label>
                            <input wire:model="fa_cost" type="number" min="0.01" step="0.01"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('fa_cost') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Valor residual <span class="text-slate-400">(salvamento)</span></label>
                            <input wire:model="fa_salvage" type="number" min="0" step="0.01"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            <p class="text-xs text-slate-400 mt-0.5">Valor estimado al final de la vida útil</p>
                        </div>
                    </div>

                    {{-- Preview depreciación --}}
                    @if($fa_cost > 0 && $fa_months > 0)
                        @php
                            $depMensual = round(($fa_cost - $fa_salvage) / $fa_months, 2);
                            $depAnual   = $depMensual * 12;
                        @endphp
                        <div class="bg-forest-50 rounded-xl p-3 border border-forest-100 text-sm">
                            <p class="font-semibold text-forest-800 mb-1">Vista previa depreciación (línea recta)</p>
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <div class="text-center">
                                    <p class="text-slate-500">Cuota mensual</p>
                                    <p class="font-mono font-bold text-forest-700">$ {{ number_format($depMensual, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-slate-500">Cuota anual</p>
                                    <p class="font-mono font-bold text-forest-700">$ {{ number_format($depAnual, 0, ',', '.') }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-slate-500">Base depreciable</p>
                                    <p class="font-mono font-bold text-forest-700">$ {{ number_format($fa_cost - $fa_salvage, 0, ',', '.') }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showForm', false)"
                        class="px-4 py-2 text-sm rounded-xl border border-cream-200 text-slate-600 hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                    <button wire:click="saveAsset"
                        class="px-5 py-2 text-sm rounded-xl bg-forest-800 text-white font-semibold hover:bg-forest-700 transition">
                        Registrar activo
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
