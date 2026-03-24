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
                                    <span class="text-base">{{ $evento['icon'] }}</span>
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
