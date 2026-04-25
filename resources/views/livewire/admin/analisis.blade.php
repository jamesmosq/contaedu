<div>
    <x-slot name="header">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Análisis de uso</h2>
                <p class="text-xs text-slate-500 mt-0.5">Ficha de desempeño por institución para gestión de contratos</p>
            </div>
            <div class="flex items-center gap-3">
                <select wire:model.live="period"
                    class="border border-cream-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-forest-500">
                    <option value="30">Últimos 30 días</option>
                    <option value="60">Últimos 60 días</option>
                    <option value="90">Últimos 90 días</option>
                </select>
                <select wire:model.live="institutionId"
                    class="border border-cream-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-forest-500 min-w-48">
                    <option value="0">— Selecciona una institución —</option>
                    @foreach($institutions as $inst)
                        <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto px-6 py-8">

        @if(! $institutionId)
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm px-8 py-16 text-center">
                <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>
                </svg>
                <p class="text-slate-500 text-sm">Selecciona una institución para ver su análisis de uso</p>
            </div>

        @elseif($data)
            {{-- ── Encabezado institución ────────────────────────────── --}}
            <div class="bg-gradient-to-r from-forest-950 to-forest-800 rounded-2xl p-6 text-white mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Ficha de institución</p>
                    <h3 class="text-xl font-bold">{{ $data['institution']->name }}</h3>
                    <p class="text-forest-300 text-sm mt-1">
                        {{ $data['totalStudents'] }} estudiantes · {{ $data['institution']->coordinator?->name ?? 'Sin coordinador' }}
                        @if($data['institution']->contract_expires_at)
                            · Contrato vence {{ $data['institution']->contract_expires_at->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
                @php
                    $ar = $data['activationRate'];
                    $arColor = $ar >= 70 ? 'text-green-400' : ($ar >= 40 ? 'text-amber-400' : 'text-red-400');
                @endphp
                <div class="text-center bg-white/10 rounded-xl px-6 py-3 shrink-0">
                    <p class="text-3xl font-bold {{ $arColor }}">{{ $ar }}%</p>
                    <p class="text-xs text-forest-300 mt-0.5">Tasa de activación</p>
                </div>
            </div>

            {{-- ── KPIs estudiantes ─────────────────────────────────── --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                @foreach([
                    ['label' => 'Sesiones totales',       'value' => number_format($data['totalSessions']),     'sub' => 'en '.$period.' días',          'icon' => 'text-forest-600'],
                    ['label' => 'Est. activos',           'value' => $data['uniqueStudents'],                   'sub' => 'de '.$data['totalStudents'],    'icon' => 'text-blue-600'],
                    ['label' => 'Duración promedio',      'value' => $data['avgDuration'].' min',               'sub' => 'por sesión',                   'icon' => 'text-amber-600'],
                    ['label' => 'Horas totales de uso',   'value' => $data['totalHours'].'h',                   'sub' => 'estudiantes',                  'icon' => 'text-purple-600'],
                ] as $kpi)
                    <div class="bg-white rounded-xl border border-cream-200 shadow-card-sm p-4">
                        <p class="text-xs text-slate-400 mb-1">{{ $kpi['label'] }}</p>
                        <p class="text-2xl font-bold {{ $kpi['icon'] }}">{{ $kpi['value'] }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $kpi['sub'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- ── KPIs docentes ───────────────────────────────────── --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                @foreach([
                    ['label' => 'Sesiones docentes',     'value' => $data['teacherStats']['total_sesiones'],   'sub' => 'en '.$period.' días'],
                    ['label' => 'Docentes activos',      'value' => $data['teacherStats']['docentes_activos'], 'sub' => 'con sesión registrada'],
                    ['label' => 'Duración prom. docente','value' => $data['teacherStats']['avg_duracion'].' min','sub' => 'por sesión'],
                    ['label' => 'Horas docentes',        'value' => $data['teacherStats']['total_horas'].'h',  'sub' => 'total en plataforma'],
                ] as $kpi)
                    <div class="bg-white rounded-xl border border-cream-200 shadow-card-sm p-4 border-l-4 border-l-gold-400">
                        <p class="text-xs text-slate-400 mb-1">{{ $kpi['label'] }}</p>
                        <p class="text-2xl font-bold text-gold-700">{{ $kpi['value'] }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $kpi['sub'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                {{-- Sesiones por semana --}}
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm p-6">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4">Sesiones por semana</h3>
                    @if($data['sessionsByWeek']->isEmpty())
                        <p class="text-sm text-slate-400">Sin sesiones en este período.</p>
                    @else
                        @php $maxS = $data['sessionsByWeek']->max('sesiones') ?: 1; @endphp
                        <div class="flex items-end gap-1.5 h-28">
                            @foreach($data['sessionsByWeek'] as $punto)
                                <div class="flex-1 flex flex-col items-center gap-1">
                                    <span class="text-[10px] text-slate-500 font-medium">{{ $punto['sesiones'] }}</span>
                                    <div class="w-full bg-forest-600 rounded-t"
                                         style="height: {{ max(4, round(($punto['sesiones'] / $maxS) * 80)) }}px"
                                         title="{{ $punto['avg_min'] }} min prom."></div>
                                    <span class="text-[9px] text-slate-400">{{ $punto['semana'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Distribución de duración --}}
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm p-6">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4">Duración de sesiones</h3>
                    @php
                        $dist = $data['durDist'];
                        $totalDist = array_sum($dist) ?: 1;
                        $distItems = [
                            ['label' => 'Muy corta (<5 min)',  'key' => 'muy_corta', 'color' => 'bg-red-400',    'note' => 'Posible rebote'],
                            ['label' => 'Corta (5–20 min)',    'key' => 'corta',     'color' => 'bg-amber-400',  'note' => 'Uso básico'],
                            ['label' => 'Media (20–60 min)',   'key' => 'media',     'color' => 'bg-forest-500', 'note' => 'Buen engagement'],
                            ['label' => 'Larga (>60 min)',     'key' => 'larga',     'color' => 'bg-blue-500',   'note' => 'Alto engagement'],
                        ];
                    @endphp
                    <div class="space-y-3">
                        @foreach($distItems as $item)
                            @php $n = $dist[$item['key']]; $pct = round(($n / $totalDist) * 100); @endphp
                            <div class="flex items-center gap-3">
                                <div class="w-32 shrink-0">
                                    <p class="text-xs text-slate-600 leading-tight">{{ $item['label'] }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $item['note'] }}</p>
                                </div>
                                <div class="flex-1 bg-cream-100 rounded-full h-2">
                                    <div class="{{ $item['color'] }} h-2 rounded-full" style="width: {{ $pct }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-slate-600 w-8 text-right">{{ $n }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                {{-- Horas pico --}}
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm p-6">
                    <h3 class="text-sm font-semibold text-slate-700 mb-4">Horas pico de uso</h3>
                    @if(empty($data['horasPico']))
                        <p class="text-sm text-slate-400">Sin datos.</p>
                    @else
                        @php $maxH = max($data['horasPico']) ?: 1; @endphp
                        <div class="flex items-end gap-0.5 h-20">
                            @for($h = 0; $h < 24; $h++)
                                @php $n = $data['horasPico'][$h] ?? 0; @endphp
                                <div class="flex-1 flex flex-col items-center gap-0.5" title="{{ $h }}h: {{ $n }} sesiones">
                                    <div class="w-full rounded-t {{ $n > 0 ? 'bg-forest-500' : 'bg-cream-200' }}"
                                         style="height: {{ $n > 0 ? max(3, round(($n / $maxH) * 60)) : 2 }}px"></div>
                                    @if(in_array($h, [0, 6, 12, 18, 23]))
                                        <span class="text-[8px] text-slate-400">{{ $h }}h</span>
                                    @else
                                        <span class="text-[8px]">&nbsp;</span>
                                    @endif
                                </div>
                            @endfor
                        </div>
                    @endif
                </div>

                {{-- Operaciones --}}
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm p-6">
                    <h3 class="text-sm font-semibold text-slate-700 mb-1">Actividad contable</h3>
                    <p class="text-xs text-slate-400 mb-4">Operaciones creadas en los últimos {{ $period }} días</p>
                    @php $ops = $data['opsData']; @endphp
                    <div class="space-y-3">
                        @foreach([
                            ['label' => 'Facturas de venta', 'value' => $ops['facturas'],  'color' => 'bg-forest-500'],
                            ['label' => 'Facturas de compra','value' => $ops['compras'],   'color' => 'bg-blue-400'],
                            ['label' => 'Asientos manuales', 'value' => $ops['asientos'],  'color' => 'bg-amber-400'],
                        ] as $op)
                            @php $maxOp = max($ops['facturas'], $ops['compras'], $ops['asientos']) ?: 1; @endphp
                            <div class="flex items-center gap-3">
                                <span class="text-xs text-slate-500 w-36">{{ $op['label'] }}</span>
                                <div class="flex-1 bg-cream-100 rounded-full h-2">
                                    <div class="{{ $op['color'] }} h-2 rounded-full" style="width: {{ round(($op['value'] / $maxOp) * 100) }}%"></div>
                                </div>
                                <span class="text-xs font-semibold text-slate-700 w-8 text-right">{{ $op['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 pt-4 border-t border-cream-100 flex justify-between text-xs text-slate-500">
                        <span>Total operaciones: <strong class="text-slate-700">{{ number_format($ops['total']) }}</strong></span>
                        <span>Por estudiante: <strong class="text-slate-700">{{ $ops['porEstudiante'] }}</strong></span>
                    </div>
                </div>
            </div>

            {{-- ── Alertas: estudiantes inactivos ───────────────────── --}}
            @if($data['inactivos'] > 0 || $data['sinSesionInfo']->isNotEmpty())
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b border-cream-100 flex items-center gap-3">
                        <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700">Alertas de inactividad</h3>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ $data['inactivos'] }} estudiante(s) sin sesión en {{ $period }} días ·
                                {{ $data['sinSesionInfo']->count() }} que nunca han iniciado sesión
                            </p>
                        </div>
                    </div>
                    @if($data['sinSesionInfo']->isNotEmpty())
                        <div class="px-6 py-4">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Nunca han ingresado</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($data['sinSesionInfo'] as $t)
                                    <div class="flex items-center gap-3 bg-cream-50 rounded-xl px-3 py-2">
                                        <div class="w-7 h-7 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                                            <span class="text-xs font-bold text-red-600">{{ strtoupper(substr($t->student_name ?? '?', 0, 1)) }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-xs font-medium text-slate-700 truncate">{{ $t->student_name }}</p>
                                            <p class="text-xs text-slate-400 truncate">{{ $t->company_name }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <p class="text-xs text-slate-400 text-right">Datos de sesión actualizados en tiempo real. Operaciones contables con caché de 3 min.</p>

        @endif
    </div>
</div>
