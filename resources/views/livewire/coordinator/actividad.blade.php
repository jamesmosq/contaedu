<div>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Actividad estudiantil</h2>
            <p class="text-sm text-slate-500 mt-0.5">{{ $institution->name }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- KPIs --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $kpis = [
                        ['label' => 'Total',    'value' => $stats['total'],    'color' => 'slate'],
                        ['label' => 'Activos',  'value' => $stats['active'],   'color' => 'forest'],
                        ['label' => 'Inactivos','value' => $stats['inactive'], 'color' => 'amber'],
                        ['label' => 'Sin uso',  'value' => $stats['never'],    'color' => 'red'],
                    ];
                @endphp
                @foreach($kpis as $kpi)
                    @php
                        $textColor = match($kpi['color']) {
                            'forest' => 'text-forest-700',
                            'amber'  => 'text-amber-600',
                            'red'    => 'text-red-500',
                            default  => 'text-slate-700',
                        };
                    @endphp
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm p-5">
                        <p class="text-2xl font-bold {{ $textColor }}">{{ $kpi['value'] }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $kpi['label'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- Filtros --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm p-4 flex flex-wrap items-center gap-4">
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-700 shrink-0">Grupo:</label>
                    <select wire:model.live="selectedGroupId"
                        class="text-sm border border-cream-300 rounded-xl px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-forest-500">
                        <option value="0">Todos</option>
                        @foreach($groups as $group)
                            <option value="{{ $group->id }}">{{ $group->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm font-medium text-slate-700 shrink-0">Estado:</label>
                    <select wire:model.live="filterStatus"
                        class="text-sm border border-cream-300 rounded-xl px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-forest-500">
                        <option value="">Todos</option>
                        <option value="active">Activos</option>
                        <option value="inactive">Inactivos</option>
                        <option value="never_active">Sin uso</option>
                    </select>
                </div>
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
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide">Última actividad</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wide">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @forelse($tenants as $tenant)
                                @php
                                    $status = $tenant->activityStatus();
                                    [$badge, $label] = match($status->value) {
                                        'active'       => ['bg-forest-100 text-forest-700', 'Activo'],
                                        'inactive'     => ['bg-amber-100 text-amber-700', 'Inactivo'],
                                        'never_active' => ['bg-slate-100 text-slate-500', 'Sin uso'],
                                        default        => ['bg-slate-100 text-slate-400', 'Desconocido'],
                                    };
                                @endphp
                                <tr class="hover:bg-cream-50 transition-colors">
                                    <td class="px-5 py-3">
                                        <p class="font-medium text-slate-800">{{ $tenant->student_name }}</p>
                                        <p class="text-xs text-slate-400">{{ $tenant->id }}</p>
                                    </td>
                                    <td class="px-4 py-3 text-slate-600">{{ $tenant->company_name }}</td>
                                    <td class="px-4 py-3 text-slate-500 text-xs">{{ $tenant->group?->name ?? '—' }}</td>
                                    <td class="px-4 py-3 text-center text-slate-500 text-xs">
                                        {{ $tenant->last_activity_at?->diffForHumans() ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge }}">
                                            {{ $label }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-slate-400">
                                        No hay estudiantes que coincidan con los filtros.
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
