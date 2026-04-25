<div>

    {{-- Banner modo aprendizaje ya está en el layout --}}

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-slate-800">Mis ejercicios</h1>
                <p class="text-sm text-slate-500 mt-0.5">Completa los ejercicios asignados por tu docente en modo aprendizaje</p>
            </div>
        </div>

        @if($items->isEmpty())
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-6 py-16 text-center">
                <div class="w-14 h-14 bg-forest-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-forest-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-slate-700 mb-1">Sin ejercicios asignados</h3>
                <p class="text-slate-400 text-xs">Tu docente aún no te ha asignado ejercicios.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($items as $item)
                    @php
                        $asgn = $item['assignment'];
                        $ex = $asgn->exercise;
                        $c = $item['completion'];
                        $vencida = $asgn->due_date && $asgn->due_date->isPast();
                        $resultado = $c?->result ?? 'pendiente';
                        $badgeColor = match($resultado) {
                            'aprobado'  => 'bg-green-100 text-green-700 border-green-200',
                            'parcial'   => 'bg-amber-100 text-amber-700 border-amber-200',
                            'no_cumple' => 'bg-red-100 text-red-700 border-red-200',
                            default     => 'bg-slate-100 text-slate-500 border-slate-200',
                        };
                        $badgeLabel = match($resultado) {
                            'aprobado'  => 'Aprobado',
                            'parcial'   => 'Parcial',
                            'no_cumple' => 'No cumple',
                            default     => 'Pendiente',
                        };
                    @endphp
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                        <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex flex-wrap items-center gap-2 mb-1">
                                    <h3 class="font-semibold text-slate-800">{{ $ex->title }}</h3>
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium border {{ $badgeColor }}">
                                        {{ $badgeLabel }}
                                    </span>
                                    @if($vencida && $resultado === 'pendiente')
                                        <span class="text-xs text-red-500">Vencida</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 mb-2">
                                    {{ \App\Models\Central\Exercise::typeLabel($ex->type) }}
                                    @if($ex->monto_minimo) · Monto mínimo: ${{ number_format($ex->monto_minimo, 0, ',', '.') }} @endif
                                    · {{ $ex->puntos }} pts
                                </p>
                                @if($ex->instructions)
                                    <p class="text-sm text-slate-600">{{ $ex->instructions }}</p>
                                @endif

                                {{-- Resultado detallado --}}
                                @if($c && $c->verification_detail)
                                    <div class="mt-3 p-3 rounded-xl {{ $resultado === 'aprobado' ? 'bg-green-50 border border-green-100' : ($resultado === 'parcial' ? 'bg-amber-50 border border-amber-100' : 'bg-red-50 border border-red-100') }}">
                                        <p class="text-xs font-medium {{ $resultado === 'aprobado' ? 'text-green-700' : ($resultado === 'parcial' ? 'text-amber-700' : 'text-red-700') }}">
                                            {{ $c->verification_detail['mensaje'] ?? '' }}
                                        </p>
                                        <p class="text-xs text-slate-400 mt-0.5">Verificado {{ $c->submitted_at?->format('d/m/Y H:i') }}</p>
                                    </div>
                                @endif

                                {{-- Enlace a la sección del ejercicio --}}
                                @php
                                    $link = match($ex->type) {
                                        'factura_venta'    => route('sandbox.facturas'),
                                        'factura_compra'   => route('sandbox.compras'),
                                        'asiento_manual'   => route('sandbox.cuentas'),
                                        'registro_tercero' => route('sandbox.terceros'),
                                        'registro_producto'=> route('sandbox.productos'),
                                        'pago_proveedor'   => route('sandbox.compras'),
                                        default            => '#',
                                    };
                                @endphp
                                <a href="{{ $link }}" class="inline-flex items-center gap-1 mt-2 text-xs text-forest-600 hover:text-forest-800 font-medium">
                                    Ir a {{ \App\Models\Central\Exercise::typeLabel($ex->type) }}
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/>
                                    </svg>
                                </a>
                            </div>

                            <div class="shrink-0 flex flex-col items-end gap-2">
                                @if($asgn->due_date)
                                    <p class="text-xs text-slate-400">Límite: {{ $asgn->due_date->format('d/m/Y') }}</p>
                                @endif
                                <button
                                    wire:click="submit({{ $asgn->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="submit({{ $asgn->id }})"
                                    @if($vencida && $resultado === 'pendiente') disabled @endif
                                    class="flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium transition
                                        {{ $resultado === 'aprobado'
                                            ? 'bg-green-50 text-green-600 border border-green-200 cursor-default'
                                            : ($vencida && $resultado === 'pendiente'
                                                ? 'bg-slate-100 text-slate-400 cursor-not-allowed'
                                                : 'bg-forest-700 hover:bg-forest-600 text-white') }}">
                                    <span wire:loading.remove wire:target="submit({{ $asgn->id }})">
                                        @if($resultado === 'aprobado')
                                            ✓ Aprobado
                                        @elseif($resultado !== 'pendiente')
                                            Re-verificar
                                        @else
                                            Marcar como listo
                                        @endif
                                    </span>
                                    <span wire:loading wire:target="submit({{ $asgn->id }})">Verificando...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
</div>
