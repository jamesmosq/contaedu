<div>

    {{-- ── Hero banner ─────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Panel Docente</p>
                <h1 class="font-display text-2xl font-bold text-white">Panel comparativo</h1>
                <p class="text-forest-300 text-sm mt-1">
                    @if($selectedGroup)
                        {{ $selectedGroup->name }} — {{ $selectedGroup->institution->name }}
                    @else
                        Selecciona un grupo para ver las métricas de tus estudiantes
                    @endif
                </p>
            </div>
            @if($selectedGroup)
                <button wire:click="clearGroup"
                    class="flex items-center gap-2 px-4 py-2 bg-forest-800 border border-forest-700 text-forest-200 text-sm font-medium rounded-xl hover:bg-forest-700 hover:text-white transition shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Cambiar grupo
                </button>
            @else
                <a href="{{ route('teacher.dashboard') }}"
                   class="flex items-center gap-2 px-4 py-2 bg-forest-800 border border-forest-700 text-forest-200 text-sm font-medium rounded-xl hover:bg-forest-700 hover:text-white transition shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                    Volver a grupos
                </a>
            @endif
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">

            {{-- ── Selector de grupo ────────────────────────────────────────── --}}
            @if(!$selectedGroup)
                @if($groups->isEmpty())
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-16 text-center">
                        <div class="w-14 h-14 bg-forest-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-forest-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                            </svg>
                        </div>
                        <p class="text-sm text-slate-500">No tienes grupos asignados.</p>
                    </div>
                @else
                    <h2 class="text-sm font-semibold text-slate-500 uppercase tracking-wide mb-4">Selecciona un grupo</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($groups as $group)
                            <button wire:click="selectGroup({{ $group->id }})"
                                class="bg-white rounded-2xl border border-cream-200 shadow-card hover:border-forest-300 hover:shadow-card-md transition-all text-left p-6 group">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-10 h-10 bg-forest-50 rounded-xl flex items-center justify-center group-hover:bg-forest-100 transition">
                                        <svg class="w-5 h-5 text-forest-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                                        </svg>
                                    </div>
                                    <span class="text-2xl font-bold text-forest-800">{{ $group->tenants_count ?? $group->tenants->count() }}</span>
                                </div>
                                <h3 class="text-base font-bold text-slate-800 mb-0.5">{{ $group->name }}</h3>
                                <p class="text-xs text-slate-400">{{ $group->institution->name }} · Período {{ $group->period }}</p>
                                <div class="flex items-center gap-1 mt-4 text-xs text-forest-600 font-semibold">
                                    Ver métricas
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif

            {{-- ── Tabla de resultados del grupo ────────────────────────────── --}}
            @else
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-forest-950 border-b border-forest-800">
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide">Estudiante / Empresa</th>
                                <th class="text-right px-5 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide"># Ventas</th>
                                <th class="text-right px-5 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide">Total ventas</th>
                                <th class="text-right px-5 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide"># Compras</th>
                                <th class="text-right px-5 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide">Total compras</th>
                                <th class="text-center px-5 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide">Balance</th>
                                <th class="px-4 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @forelse($rows as $r)
                                @php $s = $r['summary']; @endphp
                                <tr class="hover:bg-cream-50 transition">
                                    <td class="px-6 py-4">
                                        <p class="font-semibold text-slate-800">{{ $r['tenant']->student_name }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ $r['tenant']->company_name }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-right text-slate-700">
                                        {{ $s ? number_format($s->total_facturas_venta) : '—' }}
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold text-slate-800">
                                        {{ $s ? '$ '.number_format($s->monto_total_ventas, 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="px-5 py-4 text-right text-slate-700">
                                        {{ $s ? number_format($s->total_facturas_compra) : '—' }}
                                    </td>
                                    <td class="px-5 py-4 text-right font-semibold text-slate-800">
                                        {{ $s ? '$ '.number_format($s->monto_total_compras, 0, ',', '.') : '—' }}
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if(!$s)
                                            <span class="px-2.5 py-1 bg-cream-100 text-slate-400 rounded-full text-xs">Sin datos</span>
                                        @elseif($s->total_facturas_venta === 0 && $s->total_facturas_compra === 0)
                                            <span class="px-2.5 py-1 bg-cream-100 text-slate-500 rounded-full text-xs">Sin movimientos</span>
                                        @elseif($s->balance_cuadrado)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                Cuadrado
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full text-xs font-semibold">No cuadra</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('teacher.rubrica', $r['tenant']->id) }}"
                                               class="px-2.5 py-1.5 text-xs text-slate-500 hover:text-forest-700 hover:bg-forest-50 font-medium rounded-lg transition">
                                                Calificar
                                            </a>
                                            <a href="{{ route('teacher.auditar.start', $r['tenant']->id) }}"
                                               class="flex items-center gap-1 px-2.5 py-1.5 text-xs text-forest-700 hover:text-forest-900 hover:bg-forest-50 font-semibold rounded-lg transition">
                                                Auditar
                                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                                </svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-sm text-slate-400">
                                        Este grupo no tiene estudiantes registrados.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>
</div>
