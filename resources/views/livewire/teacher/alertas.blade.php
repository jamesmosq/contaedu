<div>

    {{-- ── Hero banner ─────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Panel Docente</p>
                <h1 class="font-display text-2xl font-bold text-white">Alertas contables</h1>
                <p class="text-forest-300 text-sm mt-1">Detecta problemas en las empresas de tus estudiantes sin auditar una a una</p>
            </div>
            <button wire:click="refresh"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-60 cursor-not-allowed"
                class="flex items-center gap-2 px-4 py-2 bg-forest-800 border border-forest-700 text-forest-200 text-sm font-medium rounded-xl hover:bg-forest-700 hover:text-white transition shrink-0">
                <svg wire:loading.remove wire:target="refresh" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                </svg>
                <svg wire:loading wire:target="refresh" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Actualizar
            </button>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-8 space-y-6">

        {{-- ── Resumen de alertas ─────────────────────────────────────────── --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3">
            @php
                $alertLabels = [
                    'con_alertas' => ['label' => 'Con alertas', 'color' => 'red'],
                    'A1' => ['label' => 'Asientos desc.', 'color' => 'orange'],
                    'A2' => ['label' => 'Banco bloqueado', 'color' => 'red'],
                    'A3' => ['label' => 'Inactivos', 'color' => 'amber'],
                    'A4' => ['label' => 'Sin config.', 'color' => 'yellow'],
                    'A5' => ['label' => 'Sin terceros', 'color' => 'blue'],
                    'A6' => ['label' => 'Sin productos', 'color' => 'purple'],
                ];
            @endphp
            @foreach($alertLabels as $key => $info)
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-4 py-3 text-center">
                    <p class="text-2xl font-bold {{ $summary[$key] > 0 ? 'text-red-600' : 'text-forest-600' }}">
                        {{ $summary[$key] }}
                    </p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $info['label'] }}</p>
                </div>
            @endforeach
        </div>

        {{-- ── Filtros ─────────────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-5 py-4 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex items-center gap-3 flex-1">
                <label class="text-sm font-medium text-slate-600 shrink-0">Grupo:</label>
                <select wire:model.live="groupFilter" class="flex-1 min-w-0 text-sm rounded-xl border border-cream-200 bg-cream-50 px-3 py-2 focus:ring-2 focus:ring-forest-500 focus:border-transparent">
                    <option value="0">Todos mis grupos</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->period }})</option>
                    @endforeach
                </select>
            </div>
            <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer select-none shrink-0">
                <input type="checkbox" wire:model.live="onlyWithAlerts" class="rounded border-cream-300 text-forest-600 focus:ring-forest-500">
                Solo mostrar con alertas
            </label>
        </div>

        {{-- ── Tabla de alertas ────────────────────────────────────────────── --}}
        <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
            @if($filtered->isEmpty())
                <div class="px-6 py-16 text-center">
                    <div class="w-14 h-14 bg-forest-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-7 h-7 text-forest-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-slate-700 mb-1">Sin alertas para mostrar</h3>
                    <p class="text-slate-400 text-xs">Todos los estudiantes están en orden o no hay resultados con los filtros actuales.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-cream-50 border-b border-cream-200">
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                                <th class="text-left px-5 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden sm:table-cell">Estudiante</th>
                                <th class="text-center px-3 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    <span title="Asientos desbalanceados">A1</span>
                                </th>
                                <th class="text-center px-3 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    <span title="Cuenta bancaria bloqueada">A2</span>
                                </th>
                                <th class="text-center px-3 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    <span title="Sin actividad en +30 días">A3</span>
                                </th>
                                <th class="text-center px-3 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    <span title="Sin configuración inicial">A4</span>
                                </th>
                                <th class="text-center px-3 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    <span title="Sin terceros registrados">A5</span>
                                </th>
                                <th class="text-center px-3 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                    <span title="Sin productos registrados">A6</span>
                                </th>
                                <th class="px-5 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @foreach($filtered as $row)
                                <tr class="hover:bg-cream-50/50 transition-colors">
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-slate-800">{{ $row['company_name'] }}</p>
                                        <p class="text-xs text-slate-400 sm:hidden">{{ $row['student_name'] }}</p>
                                    </td>
                                    <td class="px-5 py-4 hidden sm:table-cell">
                                        <p class="text-slate-600">{{ $row['student_name'] }}</p>
                                        <p class="text-xs text-slate-400">{{ $row['tenant_id'] }}</p>
                                    </td>
                                    @foreach(['A1','A2','A3','A4','A5','A6'] as $alert)
                                        <td class="px-3 py-4 text-center">
                                            @if($row[$alert])
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-100 text-red-600"
                                                      title="{{ ['A1'=>'Asientos desbalanceados','A2'=>'Banco bloqueado','A3'=>'Inactivo +30 días','A4'=>'Sin configuración','A5'=>'Sin terceros','A6'=>'Sin productos'][$alert] }}">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-forest-50 text-forest-500">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('teacher.auditar.start', $row['tenant_id']) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-forest-700 border border-forest-200 rounded-lg hover:bg-forest-50 transition">
                                            Auditar
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                            </svg>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Leyenda --}}
                <div class="px-5 py-3 border-t border-cream-100 bg-cream-50/50">
                    <div class="flex flex-wrap gap-x-5 gap-y-1.5 text-xs text-slate-500">
                        <span><strong class="text-slate-700">A1</strong> Asientos desbalanceados</span>
                        <span><strong class="text-slate-700">A2</strong> Cuenta bancaria bloqueada</span>
                        <span><strong class="text-slate-700">A3</strong> Sin actividad en +30 días</span>
                        <span><strong class="text-slate-700">A4</strong> Sin configuración inicial</span>
                        <span><strong class="text-slate-700">A5</strong> Sin terceros registrados</span>
                        <span><strong class="text-slate-700">A6</strong> Sin productos registrados</span>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Los resultados se actualizan cada 15 minutos. Usa el botón "Actualizar" para forzar la re-evaluación.</p>
                </div>
            @endif
        </div>

    </div>
</div>
