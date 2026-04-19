<div>
    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Herramientas</p>
                <h1 class="font-display text-2xl font-bold text-white">Calendario de obligaciones</h1>
                <p class="text-forest-300 text-sm mt-1">Vencimientos fiscales y financieros</p>
            </div>
            <div class="flex items-center gap-3">
                <select wire:model.live="year" class="rounded-xl border-forest-700 bg-forest-800 text-white text-sm focus:ring-gold-500 focus:border-gold-500">
                    @foreach([now()->year - 1, now()->year, now()->year + 1] as $y)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-5xl mx-auto space-y-6">

            {{-- Resumen de estado --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <button wire:click="$set('filtro', 'todos')"
                    class="text-left p-4 rounded-2xl border-2 transition {{ $filtro === 'todos' ? 'border-forest-500 bg-forest-50' : 'border-cream-200 bg-white hover:border-slate-300' }}">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Todas las obligaciones</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $eventos->count() }}</p>
                </button>
                <button wire:click="$set('filtro', 'proximo')"
                    class="text-left p-4 rounded-2xl border-2 transition {{ $filtro === 'proximo' ? 'border-amber-500 bg-amber-50' : 'border-cream-200 bg-white hover:border-amber-200' }}">
                    <p class="text-xs font-semibold text-amber-600 uppercase tracking-wide">Próximas (30 días)</p>
                    <p class="text-2xl font-bold text-amber-700 mt-1">{{ $proximos }}</p>
                </button>
                <button wire:click="$set('filtro', 'vencido')"
                    class="text-left p-4 rounded-2xl border-2 transition {{ $filtro === 'vencido' ? 'border-red-500 bg-red-50' : 'border-cream-200 bg-white hover:border-red-200' }}">
                    <p class="text-xs font-semibold text-red-600 uppercase tracking-wide">Vencidas (aplican)</p>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ $vencidos }}</p>
                </button>
            </div>

            {{-- Nota pedagógica --}}
            <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 text-sm text-sky-800">
                <p class="font-semibold mb-1">¿Por qué es importante el calendario de obligaciones fiscales?</p>
                <p>En Colombia las empresas tienen obligaciones periódicas con la DIAN y los municipios. Incumplir los plazos genera sanciones e intereses de mora. Las más comunes son: <strong>IVA</strong> (cuatrimestral para régimen común), <strong>Retenciones en la fuente</strong> (mensual), <strong>Impuesto de Renta</strong> (anual) e <strong>ICA</strong> (bimestral o anual según el municipio). Este calendario se actualiza automáticamente según el régimen tributario configurado en tu empresa.</p>
            </div>

            {{-- Nota educativa sobre régimen --}}
            @if($regimen === 'simplificado')
                <div class="bg-blue-50 border border-blue-200 rounded-2xl px-5 py-4 text-sm text-blue-800">
                    <strong>Régimen Simplificado:</strong> No eres responsable de IVA, no presentas declaración de IVA ni retenciones como agente retenedor. Tu principal obligación es la Declaración de Renta anual y el ICA municipal.
                </div>
            @elseif($regimen === 'comun')
                <div class="bg-violet-50 border border-violet-200 rounded-2xl px-5 py-4 text-sm text-violet-800">
                    <strong>Régimen Común:</strong> Eres responsable de IVA cuatrimestral, debes declarar y pagar retención en la fuente mensualmente, y presentar declaración de renta anual.
                </div>
            @else
                <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4 text-sm text-red-800">
                    <strong>Gran Contribuyente:</strong> Declaras IVA bimestral, retención en la fuente mensual y tienes obligaciones adicionales de información exógena.
                </div>
            @endif

            {{-- Lista de eventos --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-cream-100">
                    <h3 class="font-semibold text-slate-800">Obligaciones tributarias {{ $year }}</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Tu día límite es el <strong>día {{ $diaLimite }}</strong> del mes correspondiente (basado en los 2 últimos dígitos de tu NIT).</p>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($eventos as $evento)
                        @php
                            $estadoClasses = match($evento['estado']) {
                                'vencido'   => 'bg-red-50',
                                'proximo'   => 'bg-amber-50',
                                'no_aplica' => 'opacity-40',
                                default     => '',
                            };
                            $badgeClasses = match($evento['estado']) {
                                'vencido'   => 'bg-red-100 text-red-700',
                                'proximo'   => 'bg-amber-100 text-amber-700',
                                'futuro'    => 'bg-green-100 text-green-700',
                                'no_aplica' => 'bg-slate-100 text-slate-500',
                                default     => 'bg-slate-100 text-slate-500',
                            };
                            $badgeLabel = match($evento['estado']) {
                                'vencido'   => 'Vencida',
                                'proximo'   => 'Próxima',
                                'futuro'    => 'Pendiente',
                                'no_aplica' => 'No aplica',
                                default     => '',
                            };
                            $colorDot = match($evento['color']) {
                                'blue'   => 'bg-blue-500',
                                'violet' => 'bg-violet-500',
                                'purple' => 'bg-purple-500',
                                'red'    => 'bg-red-500',
                                'amber'  => 'bg-amber-500',
                                default  => 'bg-slate-400',
                            };
                        @endphp
                        <div class="flex items-center gap-4 px-6 py-4 {{ $estadoClasses }} hover:bg-slate-50 transition">
                            {{-- Dot color --}}
                            <div class="w-2.5 h-2.5 rounded-full {{ $colorDot }} flex-shrink-0"></div>

                            {{-- Fecha --}}
                            <div class="w-28 flex-shrink-0">
                                <p class="text-sm font-mono font-bold text-slate-800">{{ $evento['fecha']->format('d/m/Y') }}</p>
                                <p class="text-xs text-slate-400">{{ ucfirst($evento['fecha']->translatedFormat('l')) }}</p>
                            </div>

                            {{-- Icono + descripción --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    @php
                                        [$iconColor, $iconBg] = match($evento['color']) {
                                            'blue'   => ['text-blue-600',   'bg-blue-100'],
                                            'violet' => ['text-violet-600', 'bg-violet-100'],
                                            'purple' => ['text-purple-600', 'bg-purple-100'],
                                            'red'    => ['text-red-600',    'bg-red-100'],
                                            'amber'  => ['text-amber-600',  'bg-amber-100'],
                                            default  => ['text-slate-500',  'bg-slate-100'],
                                        };
                                    @endphp
                                    <span class="flex-shrink-0 w-7 h-7 rounded-lg {{ $iconBg }} flex items-center justify-center">
                                    @switch($evento['tipo'])
                                        @case('retenciones')
                                            <svg class="w-4 h-4 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                                            </svg>
                                            @break
                                        @case('iva_bimestre')
                                        @case('iva_cuatri')
                                        @case('iva_anual')
                                            <svg class="w-4 h-4 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                                            </svg>
                                            @break
                                        @case('renta')
                                            <svg class="w-4 h-4 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                            </svg>
                                            @break
                                        @case('ica')
                                            <svg class="w-4 h-4 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                                            </svg>
                                            @break
                                        @case('exogena')
                                            <svg class="w-4 h-4 {{ $iconColor }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 6.375c0 2.278-3.694 4.125-8.25 4.125S3.75 8.653 3.75 6.375m16.5 0c0-2.278-3.694-4.125-8.25-4.125S3.75 4.097 3.75 6.375m16.5 0v11.25c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125V6.375m16.5 0v3.75m-16.5-3.75v3.75m16.5 0v3.75C20.25 16.153 16.556 18 12 18s-8.25-1.847-8.25-4.125v-3.75m16.5 0c0 2.278-3.694 4.125-8.25 4.125s-8.25-1.847-8.25-4.125" />
                                            </svg>
                                            @break
                                        @default
                                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                            </svg>
                                    @endswitch
                                    </span>
                                    <p class="text-sm font-medium text-slate-700 truncate">{{ $evento['descripcion'] }}</p>
                                </div>
                                @php
                                    $info = match($evento['tipo']) {
                                        'retenciones'  => 'Declarar y pagar RteFte del período. Formulario 350.',
                                        'iva_bimestre' => 'Declarar y pagar IVA bimestral. Formulario 300.',
                                        'iva_cuatri'   => 'Declarar y pagar IVA cuatrimestral. Formulario 300.',
                                        'iva_anual'    => 'Régimen simplificado — no aplica.',
                                        'renta'        => 'Declaración de renta personas jurídicas. Formulario 110.',
                                        'ica'          => 'Impuesto de industria y comercio. Verificar fecha exacta con la Secretaría de Hacienda de tu municipio.',
                                        'exogena'      => 'Reportar información en medios magnéticos a la DIAN.',
                                        default        => '',
                                    };
                                @endphp
                                <p class="text-xs text-slate-400 mt-0.5">{{ $info }}</p>
                            </div>

                            {{-- Días restantes / estado --}}
                            <div class="flex-shrink-0 text-right">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold {{ $badgeClasses }}">{{ $badgeLabel }}</span>
                                @if($evento['estado'] === 'futuro' || $evento['estado'] === 'proximo')
                                    <p class="text-xs text-slate-400 mt-1">en {{ now()->diffInDays($evento['fecha']) }} días</p>
                                @elseif($evento['estado'] === 'vencido' && $evento['aplica'])
                                    <p class="text-xs text-red-500 mt-1">hace {{ $evento['fecha']->diffInDays(now()) }} días</p>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-10 text-center text-slate-400">
                            No hay obligaciones para mostrar con el filtro seleccionado.
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Nota legal --}}
            <div class="bg-slate-50 border border-cream-200 rounded-2xl px-5 py-4 text-xs text-slate-500">
                <strong>Nota educativa:</strong> Este calendario es una guía general basada en el calendario tributario colombiano 2025. Las fechas exactas dependen del último dígito del NIT y pueden variar por resolución DIAN cada año. En la práctica, siempre verifica las fechas oficiales en el sitio de la DIAN (dian.gov.co).
            </div>

        </div>
    </div>
</div>
