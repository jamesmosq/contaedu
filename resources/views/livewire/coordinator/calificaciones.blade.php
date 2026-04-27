<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Calificaciones</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $institution->name }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('coordinator.calificaciones.export', ['formato' => 'excel', 'grupo' => $selectedGroupId ?: '']) }}"
                    class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Excel
                </a>
                <a href="{{ route('coordinator.calificaciones.export', ['formato' => 'pdf', 'grupo' => $selectedGroupId ?: '']) }}"
                    class="flex items-center gap-1.5 px-3 py-2 text-sm font-medium bg-forest-800 hover:bg-forest-700 text-white rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-1.5-9H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                    </svg>
                    PDF
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filtro de grupo --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm p-4 flex items-center gap-4">
                <label class="text-sm font-medium text-slate-700 shrink-0">Filtrar por grupo:</label>
                <select wire:model.live="selectedGroupId"
                    class="text-sm border border-cream-300 rounded-xl px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-forest-500">
                    <option value="0">Todos los grupos</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
                <span class="text-xs text-slate-400">{{ $rows->count() }} estudiante(s)</span>
            </div>

            {{-- Tabla --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-forest-900 text-white">
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide">Estudiante</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Empresa</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide">Grupo</th>
                                @foreach(\App\Livewire\Coordinator\Calificaciones::$modules as $label)
                                    <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide">{{ $label }}</th>
                                @endforeach
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide">Promedio</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @forelse($rows as $row)
                                @php
                                    $prom = $row['promedio'];
                                    $promColor = $prom === null ? 'text-slate-400' : ($prom >= 4 ? 'text-forest-700 bg-forest-50' : ($prom >= 3 ? 'text-amber-700 bg-amber-50' : 'text-red-600 bg-red-50'));
                                @endphp
                                <tr class="hover:bg-cream-50 transition-colors">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-slate-800">{{ $row['tenant']->student_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $row['tenant']->id }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $row['tenant']->company_name }}</td>
                                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $row['tenant']->group?->name ?? '—' }}</td>
                                    @foreach(array_keys(\App\Livewire\Coordinator\Calificaciones::$modules) as $mod)
                                        @php $val = $row['scores'][$mod]; @endphp
                                        <td class="px-4 py-3 text-center">
                                            @if($val !== null)
                                                <span class="font-semibold {{ $val >= 4 ? 'text-forest-700' : ($val >= 3 ? 'text-amber-600' : 'text-red-500') }}">
                                                    {{ number_format($val, 1) }}
                                                </span>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="px-4 py-3 text-center">
                                        @if($prom !== null)
                                            <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold {{ $promColor }}">
                                                {{ number_format($prom, 1) }}
                                            </span>
                                        @else
                                            <span class="text-slate-300 text-xs">Sin notas</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 3 + count(\App\Livewire\Coordinator\Calificaciones::$modules) + 1 }}"
                                        class="px-6 py-12 text-center text-slate-400">
                                        No hay estudiantes con calificaciones en este período.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
