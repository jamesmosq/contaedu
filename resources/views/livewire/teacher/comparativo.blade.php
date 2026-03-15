<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Panel comparativo</h2>
                <p class="text-sm text-slate-500 mt-0.5">Métricas de todos los estudiantes</p>
            </div>
            <a href="{{ route('teacher.dashboard') }}" class="px-3 py-1.5 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
                ← Volver
            </a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @forelse($groups as $group)
                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">
                    {{ $group->name }} — {{ $group->institution->name }}
                </h4>

                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-8">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estudiante / Empresa</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide"># Facturas venta</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total ventas</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide"># Facturas compra</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total compras</th>
                                <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Balance cuadrado</th>
                                <th class="px-4 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($rows as $r)
                                @if($r['group']->id === $group->id)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4">
                                            <p class="font-medium text-slate-700">{{ $r['tenant']->student_name }}</p>
                                            <p class="text-xs text-slate-400">{{ $r['tenant']->company_name }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-right text-slate-700">{{ number_format($r['data']['ventasCount']) }}</td>
                                        <td class="px-5 py-4 text-right text-slate-700 font-medium">$ {{ number_format($r['data']['ventasTotal'], 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 text-right text-slate-700">{{ number_format($r['data']['comprasCount']) }}</td>
                                        <td class="px-5 py-4 text-right text-slate-700 font-medium">$ {{ number_format($r['data']['comprasTotal'], 0, ',', '.') }}</td>
                                        <td class="px-5 py-4 text-center">
                                            @if($r['data']['balanced'])
                                                <span class="inline-flex items-center gap-1 px-2 py-1 bg-accent-100 text-accent-700 rounded-full text-xs font-medium">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                                                    Sí
                                                </span>
                                            @elseif($r['data']['totalDebit'] == 0)
                                                <span class="px-2 py-1 bg-slate-100 text-slate-500 rounded-full text-xs">Sin movimientos</span>
                                            @else
                                                <span class="px-2 py-1 bg-red-100 text-red-700 rounded-full text-xs font-medium">No cuadra</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('teacher.rubrica', $r['tenant']->id) }}" class="text-xs text-slate-500 hover:text-slate-700">Calificar</a>
                                                <a href="{{ route('teacher.auditar.start', $r['tenant']->id) }}" class="text-xs text-brand-700 hover:text-brand-900 font-medium">Auditar →</a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @empty
                <p class="text-slate-400 text-center py-12">No tienes grupos asignados.</p>
            @endforelse

        </div>
    </div>
</div>
