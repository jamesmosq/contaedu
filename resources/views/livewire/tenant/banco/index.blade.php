<div>
    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Finanzas</p>
                <h1 class="font-display text-2xl font-bold text-white">Banco</h1>
                <p class="text-forest-300 text-sm mt-1">Simulador bancario — gestiona tus cuentas y movimientos</p>
            </div>
            @if(!session('audit_mode') && !session('reference_mode'))
                <div class="flex flex-wrap items-center gap-2">
                    @if($this->cuentas->count() >= 2)
                        <button wire:click="$set('showTransferenciaForm', true)"
                            class="px-4 py-2 bg-forest-700 text-white text-sm font-semibold rounded-xl hover:bg-forest-600 transition flex items-center gap-2 border border-forest-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg>
                            Transferir
                        </button>
                    @endif
                    @if($this->cuentaActiva?->account_type === 'corriente')
                        <button wire:click="$set('showChequeForm', true)"
                            class="px-4 py-2 bg-forest-700 text-white text-sm font-semibold rounded-xl hover:bg-forest-600 transition flex items-center gap-2 border border-forest-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
                            Emitir cheque
                        </button>
                    @endif
                    @if($this->cuentas->count() < 2)
                        <button wire:click="abrirFormSegundaCuenta"
                            class="px-4 py-2 bg-forest-700 text-white text-sm font-semibold rounded-xl hover:bg-forest-600 transition flex items-center gap-2 border border-forest-600">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            {{ $this->cuentas->isEmpty() ? 'Crear cuenta bancaria' : 'Abrir segunda cuenta' }}
                        </button>
                    @endif
                    <button wire:click="$set('showFinMesConfirm', true)"
                        class="px-4 py-2 bg-gold-500 text-white text-sm font-semibold rounded-xl hover:bg-gold-400 transition flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        Simular fin de mes
                    </button>
                </div>
            @endif
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto space-y-6">

            {{-- ── Sin cuenta bancaria (tenant antiguo sin migrar) ────────── --}}
            @if($this->cuentas->isEmpty())
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-center space-y-3">
                    <p class="text-amber-700 font-semibold">Esta empresa no tiene cuenta bancaria asignada.</p>
                    <p class="text-amber-600 text-sm">Las cuentas nuevas se asignan automáticamente, pero tu empresa fue creada antes del módulo bancario. Puedes crear tu primera cuenta ahora.</p>
                    @if(!session('audit_mode') && !session('reference_mode'))
                        <button wire:click="abrirFormSegundaCuenta"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-gold-500 text-white text-sm font-semibold rounded-xl hover:bg-gold-400 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Crear cuenta bancaria
                        </button>
                    @endif
                </div>
            @else

            {{-- ── Tarjetas de cuentas ──────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($this->cuentas as $cuenta)
                    @php
                        $isActiva   = $cuenta->id === $cuentaActivaId;
                        // Paleta del sistema: forest (verde) + gold (dorado)
                        $cardGrad = match($cuenta->bank) {
                            'bancolombia'  => 'background:linear-gradient(135deg,#0a2e1a,#051a0f)', // forest-900 → forest-950
                            'davivienda'   => 'background:linear-gradient(135deg,#b8860b,#4a3204)', // gold-600 → accent-900
                            'banco_bogota' => 'background:linear-gradient(135deg,#165e36,#0a2e1a)', // forest-700 → forest-900
                            default        => 'background:linear-gradient(135deg,#10472a,#051a0f)', // forest-800 → forest-950
                        };
                        $dotColor = match($cuenta->bank) {
                            'bancolombia'  => '#3db872', // forest-400
                            'davivienda'   => '#f0cc5a', // gold-300
                            'banco_bogota' => '#71c99c', // forest-300
                            default        => '#a7dfc0', // forest-200
                        };
                    @endphp
                    <button wire:click="seleccionarCuenta({{ $cuenta->id }})"
                        style="{{ $cardGrad }}{{ $isActiva ? '; outline: 2px solid #d4a017; outline-offset: 2px;' : '' }}"
                        class="rounded-2xl p-5 text-left transition {{ $isActiva ? '' : 'opacity-80 hover:opacity-100' }}">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full" style="background-color:{{ $dotColor }}"></span>
                                <span class="text-white font-bold text-sm">{{ $cuenta->nombreBanco() }}</span>
                                @if($cuenta->es_principal)
                                    <span class="text-xs bg-white/20 text-white/80 rounded px-1.5 py-0.5">Principal</span>
                                @endif
                            </div>
                            <span class="text-white/60 text-xs capitalize">{{ $cuenta->account_type }}</span>
                        </div>
                        <p class="text-white/60 text-xs font-mono mb-1">{{ $cuenta->account_number }}</p>
                        <p class="text-white text-2xl font-bold">${{ number_format($cuenta->saldo, 0, ',', '.') }}</p>
                        @if($cuenta->sobregiro_usado > 0)
                            <p class="text-red-300 text-xs mt-1">Sobregiro: ${{ number_format($cuenta->sobregiro_usado, 0, ',', '.') }}</p>
                        @endif
                        @if($cuenta->bloqueada)
                            <p class="text-red-400 text-xs mt-1 font-semibold">CUENTA BLOQUEADA</p>
                        @endif
                        @if($cuenta->saldo < 5_000_000 && !$cuenta->bloqueada)
                            <p class="text-amber-300 text-xs mt-1">Saldo bajo</p>
                        @endif
                    </button>
                @endforeach

                @if($this->cuentas->count() < 2 && !session('audit_mode') && !session('reference_mode'))
                    <button wire:click="abrirFormSegundaCuenta"
                        class="rounded-2xl border-2 border-dashed border-slate-300 p-5 flex flex-col items-center justify-center gap-2 text-slate-400 hover:border-slate-400 hover:text-slate-500 transition">
                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                        <span class="text-sm font-medium">Abrir segunda cuenta</span>
                    </button>
                @endif
            </div>

            {{-- Saldo total si tiene 2 cuentas --}}
            @if($this->cuentas->count() > 1)
                <div class="bg-slate-50 rounded-xl border border-slate-200 px-5 py-3 flex items-center justify-between">
                    <span class="text-sm text-slate-600 font-medium">Saldo total en ambas cuentas</span>
                    <span class="text-lg font-bold text-slate-800">${{ number_format($this->saldoTotal, 0, ',', '.') }}</span>
                </div>
            @endif

            {{-- ── Alertas automáticas ──────────────────────────────────── --}}
            @if(count($this->alertas) > 0)
                <div class="space-y-2">
                    @foreach($this->alertas as $alerta)
                        <div class="flex items-start gap-3 rounded-xl border px-4 py-3 text-sm
                            {{ $alerta['tipo'] === 'error'   ? 'bg-red-50 border-red-200 text-red-800' :
                               ($alerta['tipo'] === 'warning' ? 'bg-amber-50 border-amber-200 text-amber-800' :
                                                                'bg-sky-50 border-sky-200 text-sky-800') }}">
                            @if($alerta['tipo'] === 'error')
                                <svg class="w-4 h-4 mt-0.5 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                            @elseif($alerta['tipo'] === 'warning')
                                <svg class="w-4 h-4 mt-0.5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                            @else
                                <svg class="w-4 h-4 mt-0.5 shrink-0 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                            @endif
                            <span>{{ $alerta['mensaje'] }}</span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- ── Nota pedagógica ────────────────────────────────────── --}}
            <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 text-sm text-sky-800">
                <p class="font-semibold mb-1">¿Por qué importa el banco que usas?</p>
                <p>Cada banco cobra comisiones diferentes. Cuando pagas a un proveedor que usa otro banco, pagas <strong>comisión ACH</strong> (transferencia entre redes). Los contadores colombianos aprenden a planificar los pagos para minimizar estos costos. Además, el <strong>GMF 4x1000</strong> se cobra sobre todo retiro y transferencia de salida.</p>
            </div>

            {{-- ── Tabs ──────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                <div class="border-b border-cream-100 bg-slate-50">
                    <nav class="flex overflow-x-auto">
                        @foreach([
                            ['key' => 'movimientos',      'label' => 'Movimientos'],
                            ['key' => 'transferencias',   'label' => 'Transferencias'],
                            ['key' => 'documentos',       'label' => 'Documentos'],
                            ['key' => 'cheques',          'label' => 'Chequera'],
                        ] as $t)
                            <button wire:click="$set('tab', '{{ $t['key'] }}')"
                                class="px-5 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition
                                    {{ $tab === $t['key']
                                        ? 'border-gold-500 text-gold-600 bg-white'
                                        : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                                {{ $t['label'] }}
                            </button>
                        @endforeach
                    </nav>
                </div>

                {{-- ── Tab Movimientos ─────────────────────────────────── --}}
                @if($tab === 'movimientos')
                    <div>
                        @if($this->movimientos->isEmpty())
                            <div class="px-6 py-12 text-center text-sm text-slate-400">
                                No hay movimientos registrados en esta cuenta.
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                                        <tr>
                                            <th class="px-4 py-3 text-left">Fecha</th>
                                            <th class="px-4 py-3 text-left">Descripción</th>
                                            <th class="px-4 py-3 text-left">Tipo</th>
                                            <th class="px-4 py-3 text-right">Débito</th>
                                            <th class="px-4 py-3 text-right">Crédito</th>
                                            <th class="px-4 py-3 text-right">Saldo</th>
                                            <th class="px-4 py-3 text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($this->movimientos as $mov)
                                            @php $esCargo = $mov->esCargo(); @endphp
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">
                                                    {{ $mov->fecha_transaccion->format('d/m/Y') }}
                                                </td>
                                                <td class="px-4 py-3 text-slate-700">
                                                    {{ $mov->descripcion }}
                                                    @if($mov->gmf > 0)
                                                        <span class="text-xs text-orange-500 ml-1">(+GMF ${{ number_format($mov->gmf, 0, ',', '.') }})</span>
                                                    @endif
                                                    @if($mov->comision > 0)
                                                        <span class="text-xs text-orange-500 ml-1">(+ACH ${{ number_format($mov->comision, 0, ',', '.') }})</span>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="text-xs rounded-full px-2 py-0.5
                                                        {{ $esCargo ? 'bg-red-50 text-red-600' : 'bg-green-50 text-green-600' }}">
                                                        {{ str_replace('_', ' ', $mov->tipo) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3 text-right font-mono text-red-600">
                                                    @if($esCargo)
                                                        ${{ number_format($mov->valor + $mov->gmf + $mov->comision, 0, ',', '.') }}
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-right font-mono text-green-600">
                                                    @if(!$esCargo)
                                                        ${{ number_format($mov->valor, 0, ',', '.') }}
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-right font-mono text-slate-700">
                                                    ${{ number_format($mov->saldo_despues, 0, ',', '.') }}
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    @if($mov->conciliado)
                                                        <span class="text-xs text-green-600">Conciliado</span>
                                                    @else
                                                        <span class="text-xs text-slate-400">Pendiente</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ── Tab Transferencias ─────────────────────────────────── --}}
                @if($tab === 'transferencias')
                    <div class="p-6 space-y-5">
                        @if($this->cuentas->count() < 2)
                            <div class="text-center py-10">
                                <p class="text-sm text-slate-500 mb-3">Necesitas al menos 2 cuentas activas para hacer transferencias entre ellas.</p>
                                @if(!session('audit_mode') && !session('reference_mode'))
                                    <button wire:click="abrirFormSegundaCuenta"
                                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                        Abrir segunda cuenta
                                    </button>
                                @endif
                            </div>
                        @else
                            <div>
                                <h3 class="text-sm font-semibold text-slate-700 mb-4">Transferir entre mis cuentas</h3>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                                    {{-- Cuenta origen --}}
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5 block">Cuenta origen</label>
                                        <select wire:model="transf_origen_id"
                                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-gold-500 focus:border-transparent">
                                            <option value="">Seleccionar...</option>
                                            @foreach($this->cuentas as $c)
                                                <option value="{{ $c->id }}">{{ $c->nombreBanco() }} ***{{ $c->ultimosDigitos() }} — ${{ number_format($c->saldo, 0, ',', '.') }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Cuenta destino --}}
                                    <div>
                                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5 block">Cuenta destino</label>
                                        <select wire:model="transf_destino_id"
                                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-gold-500 focus:border-transparent">
                                            <option value="">Seleccionar...</option>
                                            @foreach($this->cuentas as $c)
                                                @if($c->id != $transf_origen_id)
                                                    <option value="{{ $c->id }}">{{ $c->nombreBanco() }} ***{{ $c->ultimosDigitos() }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                {{-- Monto --}}
                                <div class="mb-4">
                                    <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5 block">Monto a transferir</label>
                                    <div class="relative max-w-xs">
                                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">$</span>
                                        <input type="number" wire:model.live="transf_monto" min="0" step="1000"
                                            class="w-full pl-7 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                                            placeholder="0">
                                    </div>
                                </div>

                                {{-- Vista previa costos --}}
                                @if($transf_monto > 0 && $transf_origen_id && $transf_destino_id)
                                    @php
                                        $origenPrev  = $this->cuentas->firstWhere('id', $transf_origen_id);
                                        $destinoPrev = $this->cuentas->firstWhere('id', $transf_destino_id);
                                        $achPrev     = ($origenPrev && $destinoPrev) ? \App\Services\BankService::costoAch($origenPrev->bank, $destinoPrev->bank) : 0;
                                        $gmfPrev     = \App\Services\BankService::calcularGmf('transferencia_salida', $transf_monto);
                                        $totalPrev   = $transf_monto + $achPrev + $gmfPrev;
                                    @endphp
                                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 mb-4 space-y-1">
                                        <p class="font-semibold">Resumen de cargos:</p>
                                        <p>Monto transferido: <strong>${{ number_format($transf_monto, 0, ',', '.') }}</strong></p>
                                        @if($gmfPrev > 0)
                                            <p>GMF 4×1000: <strong>${{ number_format($gmfPrev, 0, ',', '.') }}</strong></p>
                                        @endif
                                        @if($achPrev > 0)
                                            <p>Comisión ACH (banco diferente): <strong>${{ number_format($achPrev, 0, ',', '.') }}</strong></p>
                                        @endif
                                        <p class="border-t border-amber-300 pt-1 font-semibold">Total debitado de origen: ${{ number_format($totalPrev, 0, ',', '.') }}</p>
                                    </div>
                                @endif

                                @if(!session('audit_mode') && !session('reference_mode'))
                                    <button wire:click="transferirEntreCuentas"
                                        class="px-5 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                        Confirmar transferencia
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ── Tab Documentos ──────────────────────────────────── --}}
                @if($tab === 'documentos')
                    <div class="p-6 space-y-6">

                        {{-- Una sección de solicitud por cada cuenta activa --}}
                        @foreach($this->cuentas as $cta)
                            @php
                                $dotDoc = match($cta->bank) {
                                    'bancolombia'  => '#3db872',
                                    'davivienda'   => '#d4a017',
                                    'banco_bogota' => '#71c99c',
                                    default        => '#94a3b8',
                                };
                            @endphp
                            <div class="rounded-2xl border border-slate-200 overflow-hidden">
                                {{-- Cabecera de la cuenta --}}
                                <div class="flex items-center gap-2 px-4 py-3 bg-slate-50 border-b border-slate-100">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color:{{ $dotDoc }}"></span>
                                    <span class="text-sm font-semibold text-slate-700">{{ $cta->nombreBanco() }}</span>
                                    <span class="text-xs font-mono text-slate-400">***{{ $cta->ultimosDigitos() }}</span>
                                    <span class="ml-auto text-xs font-mono text-slate-500">${{ number_format($cta->saldo, 0, ',', '.') }}</span>
                                </div>

                                <div class="divide-y divide-slate-100">
                                    {{-- Extracto --}}
                                    <div class="flex items-center justify-between px-4 py-3">
                                        <div>
                                            <p class="text-sm font-medium text-slate-700">Extracto bancario</p>
                                            <p class="text-xs text-slate-400">Movimientos del período actual</p>
                                        </div>
                                        @if(!session('audit_mode') && !session('reference_mode'))
                                            <button wire:click="solicitarDocumento('extracto', {{ $cta->id }})"
                                                class="px-3 py-1.5 bg-forest-800 text-white text-xs font-semibold rounded-lg hover:bg-forest-700 transition">
                                                Solicitar
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Certificado --}}
                                    <div class="flex items-center justify-between px-4 py-3">
                                        <div>
                                            <p class="text-sm font-medium text-slate-700">Certificado bancario</p>
                                            <p class="text-xs text-slate-400">Confirma titularidad y saldo</p>
                                        </div>
                                        @if(!session('audit_mode') && !session('reference_mode'))
                                            <button wire:click="solicitarDocumento('certificado', {{ $cta->id }})"
                                                class="px-3 py-1.5 bg-forest-800 text-white text-xs font-semibold rounded-lg hover:bg-forest-700 transition">
                                                Solicitar
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Referencia --}}
                                    <div class="flex items-center justify-between px-4 py-3">
                                        <div>
                                            <p class="text-sm font-medium text-slate-700">Referencia bancaria</p>
                                            <p class="text-xs text-slate-400">Carta de confirmación de cliente del banco</p>
                                        </div>
                                        @if(!session('audit_mode') && !session('reference_mode'))
                                            <button wire:click="solicitarDocumento('referencia', {{ $cta->id }})"
                                                class="px-3 py-1.5 bg-forest-800 text-white text-xs font-semibold rounded-lg hover:bg-forest-700 transition">
                                                Solicitar
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Paz y salvo --}}
                                    <div class="flex items-center justify-between px-4 py-3 {{ $cta->saldo != 0 ? 'opacity-50' : '' }}">
                                        <div>
                                            <p class="text-sm font-medium text-slate-700">Paz y salvo</p>
                                            <p class="text-xs text-slate-400">
                                                {{ $cta->saldo != 0 ? 'Solo disponible con saldo $0' : 'Disponible — saldo $0' }}
                                            </p>
                                        </div>
                                        @if(!session('audit_mode') && !session('reference_mode') && $cta->saldo == 0)
                                            <button wire:click="solicitarDocumento('paz_y_salvo', {{ $cta->id }})"
                                                class="px-3 py-1.5 bg-forest-800 text-white text-xs font-semibold rounded-lg hover:bg-forest-700 transition">
                                                Solicitar
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        {{-- Historial de todos los documentos (todas las cuentas) --}}
                        @if($this->documentos->isNotEmpty())
                            <div>
                                <h4 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Historial de documentos</h4>
                                <div class="divide-y divide-slate-100 rounded-xl border border-slate-200 overflow-hidden">
                                    @foreach($this->documentos as $doc)
                                        @php
                                            $dotH = match($doc->bankAccount?->bank) {
                                                'bancolombia'  => '#3db872',
                                                'davivienda'   => '#d4a017',
                                                'banco_bogota' => '#71c99c',
                                                default        => '#94a3b8',
                                            };
                                        @endphp
                                        <div class="px-4 py-2.5 flex items-center justify-between text-sm bg-white">
                                            <div class="flex items-center gap-2 min-w-0">
                                                <span class="w-2 h-2 rounded-full shrink-0" style="background-color:{{ $dotH }}"></span>
                                                <div class="min-w-0">
                                                    <span class="text-slate-700 font-medium">{{ $doc->nombreTipo() }}</span>
                                                    <span class="text-xs text-slate-400 mx-1.5">·</span>
                                                    <span class="text-xs text-slate-500">{{ $doc->bankAccount?->nombreBanco() }} ***{{ $doc->bankAccount?->ultimosDigitos() }}</span>
                                                    <span class="text-xs text-slate-400 ml-2">{{ $doc->generado_at->format('d/m/Y H:i') }}</span>
                                                </div>
                                            </div>
                                            <a href="{{ rtrim($this->bancoPageUrl, '/') . '/documento/pdf?id=' . $doc->id }}"
                                               target="_blank"
                                               class="text-xs text-forest-700 hover:text-forest-900 font-semibold flex items-center gap-1 ml-4 shrink-0">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                                PDF
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- ── Tab Chequera ─────────────────────────────────────── --}}
                @if($tab === 'cheques')
                    <div>
                        @php $cuentaChk = $this->cuentaActiva; @endphp
                        @if($cuentaChk && $cuentaChk->account_type === 'corriente' && !session('audit_mode') && !session('reference_mode'))
                            <div class="px-4 py-3 border-b border-slate-100 flex justify-end">
                                <button wire:click="$set('showChequeForm', true)"
                                    class="px-4 py-2 bg-forest-800 text-white text-xs font-semibold rounded-xl hover:bg-forest-700 transition flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                    Emitir cheque
                                </button>
                            </div>
                        @endif
                        @if(!$cuentaChk || $cuentaChk->account_type === 'ahorros')
                            <div class="px-6 py-12 text-center text-sm text-slate-400">
                                La chequera solo está disponible en cuentas corrientes.
                            </div>
                        @elseif($this->cheques->isEmpty())
                            <div class="px-6 py-12 text-center text-sm text-slate-400">
                                No hay cheques emitidos aún.
                            </div>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead class="bg-slate-50 text-xs text-slate-500 uppercase tracking-wide">
                                        <tr>
                                            <th class="px-4 py-3 text-left">N° Cheque</th>
                                            <th class="px-4 py-3 text-left">Fecha emisión</th>
                                            <th class="px-4 py-3 text-left">Beneficiario</th>
                                            <th class="px-4 py-3 text-right">Valor</th>
                                            <th class="px-4 py-3 text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($this->cheques as $cheque)
                                            <tr class="hover:bg-slate-50">
                                                <td class="px-4 py-3 font-mono text-slate-700">{{ $cheque->numero_cheque }}</td>
                                                <td class="px-4 py-3 text-slate-500">{{ $cheque->fecha_emision->format('d/m/Y') }}</td>
                                                <td class="px-4 py-3 text-slate-700">{{ $cheque->beneficiario }}</td>
                                                <td class="px-4 py-3 text-right font-mono text-slate-700">${{ number_format($cheque->valor, 0, ',', '.') }}</td>
                                                <td class="px-4 py-3 text-center">
                                                    <span class="text-xs rounded-full px-2 py-0.5 font-medium
                                                        {{ match($cheque->estado) {
                                                            'cobrado'  => 'bg-green-50 text-green-700',
                                                            'emitido'  => 'bg-blue-50 text-blue-700',
                                                            'devuelto' => 'bg-red-50 text-red-700',
                                                            'anulado'  => 'bg-slate-100 text-slate-500',
                                                            default    => 'bg-slate-100 text-slate-500',
                                                        } }}">
                                                        {{ ucfirst($cheque->estado) }}
                                                        @if($cheque->estado === 'emitido' && $cheque->diasPendiente() > 30)
                                                            <span class="text-amber-600 ml-1">({{ $cheque->diasPendiente() }}d)</span>
                                                        @endif
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif

            </div>{{-- /tabs panel --}}

            @endif {{-- /cuentas no vacías --}}

        </div>
    </div>

    {{-- ── Modal: Confirmar fin de mes ────────────────────────────────────── --}}
    @if($showFinMesConfirm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-slate-800 mb-2">Simular fin de mes</h2>
                <p class="text-sm text-slate-600 mb-4">
                    Esta acción aplicará a <strong>todas tus cuentas activas</strong>:
                </p>
                <ul class="text-sm text-slate-600 space-y-1 mb-4 list-disc list-inside">
                    <li>Cuota de manejo bancaria (con IVA)</li>
                    <li>Intereses de ahorros (si tienes cuenta de ahorros)</li>
                    <li>Intereses de sobregiro (si tienes sobregiro activo)</li>
                </ul>
                <p class="text-xs text-amber-600 bg-amber-50 border border-amber-200 rounded-lg p-2 mb-5">
                    Este proceso genera asientos contables automáticos y no se puede revertir.
                </p>
                <div class="flex gap-3">
                    <button wire:click="procesarFinDeMes"
                        class="flex-1 px-4 py-2 bg-gold-500 text-white text-sm font-semibold rounded-xl hover:bg-gold-400 transition">
                        Confirmar
                    </button>
                    <button wire:click="$set('showFinMesConfirm', false)"
                        class="flex-1 px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Modal: Transferencia entre cuentas ───────────────────────────────── --}}
    @if($showTransferenciaForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-slate-800">Transferir entre cuentas</h2>
                    <button wire:click="$set('showTransferenciaForm', false)" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5 block">Cuenta origen</label>
                        <select wire:model="transf_origen_id"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-gold-500 focus:border-transparent">
                            <option value="">Seleccionar...</option>
                            @foreach($this->cuentas as $c)
                                <option value="{{ $c->id }}">{{ $c->nombreBanco() }} ***{{ $c->ultimosDigitos() }} — ${{ number_format($c->saldo, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5 block">Cuenta destino</label>
                        <select wire:model="transf_destino_id"
                            class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-gold-500 focus:border-transparent">
                            <option value="">Seleccionar...</option>
                            @foreach($this->cuentas as $c)
                                @if($c->id != $transf_origen_id)
                                    <option value="{{ $c->id }}">{{ $c->nombreBanco() }} ***{{ $c->ultimosDigitos() }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5 block">Monto</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">$</span>
                            <input type="number" wire:model.live="transf_monto" min="0" step="1000"
                                class="w-full pl-7 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                                placeholder="0">
                        </div>
                    </div>

                    @if($transf_monto > 0 && $transf_origen_id && $transf_destino_id)
                        @php
                            $origenM  = $this->cuentas->firstWhere('id', $transf_origen_id);
                            $destinoM = $this->cuentas->firstWhere('id', $transf_destino_id);
                            $achM     = ($origenM && $destinoM) ? \App\Services\BankService::costoAch($origenM->bank, $destinoM->bank) : 0;
                            $gmfM     = \App\Services\BankService::calcularGmf('transferencia_salida', $transf_monto);
                            $totalM   = $transf_monto + $achM + $gmfM;
                        @endphp
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 space-y-1">
                            <p class="font-semibold">Cargos estimados:</p>
                            @if($gmfM > 0)<p>GMF 4×1000: <strong>${{ number_format($gmfM, 0, ',', '.') }}</strong></p>@endif
                            @if($achM > 0)<p>Comisión ACH: <strong>${{ number_format($achM, 0, ',', '.') }}</strong></p>@endif
                            <p class="border-t border-amber-300 pt-1 font-semibold">Total cargo: ${{ number_format($totalM, 0, ',', '.') }}</p>
                        </div>
                    @endif
                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="transferirEntreCuentas"
                        class="flex-1 px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                        Confirmar transferencia
                    </button>
                    <button wire:click="$set('showTransferenciaForm', false)"
                        class="flex-1 px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Modal: Emitir cheque ────────────────────────────────────────────── --}}
    @if($showChequeForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-slate-800">Emitir cheque</h2>
                    <button wire:click="$set('showChequeForm', false)" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                @php $cuentaCheque = $this->cuentaActiva; @endphp
                @if($cuentaCheque && $cuentaCheque->account_type !== 'corriente')
                    <p class="text-sm text-red-600 text-center py-4">Los cheques solo están disponibles en cuentas corrientes.</p>
                @else
                    <div class="space-y-4">
                        <div>
                            <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5 block">Beneficiario</label>
                            <input type="text" wire:model="cheque_beneficiario"
                                class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                                placeholder="Nombre del beneficiario">
                        </div>

                        <div>
                            <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5 block">Valor</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">$</span>
                                <input type="number" wire:model.live="cheque_valor" min="0" step="1000"
                                    class="w-full pl-7 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                                    placeholder="0">
                            </div>
                        </div>

                        @if($cheque_valor > 0)
                            @php $gmfCheque = \App\Services\BankService::calcularGmf('cheque', $cheque_valor); @endphp
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 space-y-1">
                                <p>GMF 4×1000: <strong>${{ number_format($gmfCheque, 0, ',', '.') }}</strong></p>
                                <p class="font-semibold">Total debitado: ${{ number_format($cheque_valor + $gmfCheque, 0, ',', '.') }}</p>
                            </div>
                        @endif

                        @if($cuentaCheque)
                            <p class="text-xs text-slate-500">
                                Cuenta: {{ $cuentaCheque->nombreBanco() }} ***{{ $cuentaCheque->ultimosDigitos() }} |
                                Cheques emitidos: {{ $cuentaCheque->cheques_emitidos }} /
                                Disponibles: {{ $cuentaCheque->cheques_disponibles }}
                            </p>
                        @endif
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button wire:click="emitirCheque"
                            class="flex-1 px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                            Emitir cheque
                        </button>
                        <button wire:click="$set('showChequeForm', false)"
                            class="flex-1 px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition">
                            Cancelar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ── Modal: Cupo Ágil / Sobregiro ───────────────────────────────────── --}}
    @if($showSobregiroModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">Activar Cupo Ágil</h2>
                </div>

                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm text-amber-800 mb-5 space-y-1">
                    <p class="font-semibold">¿Estás seguro de usar el sobregiro?</p>
                    @if($sobregiroContexto)
                        <p>Razón: {{ $sobregiroContexto }}</p>
                    @endif
                    <p>Monto solicitado: <strong>${{ number_format($sobregiroMontoSolicitado, 0, ',', '.') }}</strong></p>
                    <p class="text-xs text-amber-700 mt-2">
                        El Cupo Ágil de Banco de Bogotá genera <strong>intereses diarios del 0.1%</strong> sobre el saldo usado.
                        Esto se aplica automáticamente al simular fin de mes. Si llevas 2 períodos sin pagar, tu cuenta quedará bloqueada.
                    </p>
                </div>

                <div class="flex gap-3">
                    <button wire:click="confirmarSobregiro"
                        class="flex-1 px-4 py-2 bg-amber-500 text-white text-sm font-semibold rounded-xl hover:bg-amber-400 transition">
                        Usar Cupo Ágil
                    </button>
                    <button wire:click="$set('showSobregiroModal', false)"
                        class="flex-1 px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Modal: Crear / Abrir segunda cuenta ─────────────────────────────── --}}
    @if($showSegundaCuenta)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-slate-800">
                        {{ $this->cuentas->isEmpty() ? 'Crear cuenta bancaria' : 'Abrir segunda cuenta' }}
                    </h2>
                    <button wire:click="$set('showSegundaCuenta', false)" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5 block">Banco</label>
                        <select wire:model="nuevoBanco" class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-gold-500 focus:border-transparent">
                            @php $bancosActuales = $this->cuentas->pluck('bank')->toArray(); @endphp
                            @if(!in_array('bancolombia', $bancosActuales))
                                <option value="bancolombia">Bancolombia</option>
                            @endif
                            @if(!in_array('davivienda', $bancosActuales))
                                <option value="davivienda">Davivienda</option>
                            @endif
                            @if(!in_array('banco_bogota', $bancosActuales))
                                <option value="banco_bogota">Banco de Bogotá</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5 block">Tipo de cuenta</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button wire:click="$set('nuevaTipoCuenta', 'corriente')"
                                class="px-3 py-2.5 rounded-xl border text-sm font-medium transition
                                    {{ $nuevaTipoCuenta === 'corriente' ? 'border-gold-500 bg-gold-50 text-gold-700' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                                Corriente
                            </button>
                            <button wire:click="$set('nuevaTipoCuenta', 'ahorros')"
                                class="px-3 py-2.5 rounded-xl border text-sm font-medium transition
                                    {{ $nuevaTipoCuenta === 'ahorros' ? 'border-gold-500 bg-gold-50 text-gold-700' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                                Ahorros
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-1.5 block">Monto inicial a consignar</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-sm">$</span>
                            <input type="number" wire:model="montoInicial" min="0" step="1000"
                                class="w-full pl-7 rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-gold-500 focus:border-transparent"
                                placeholder="0">
                        </div>
                        @php
                            $cuentaPpal = $this->cuentas->where('es_principal', true)->first();
                            $sinCuentas = $this->cuentas->isEmpty();
                            $ach = (!$sinCuentas && $nuevoBanco) ? \App\Services\BankService::costoAch($cuentaPpal?->bank ?? '', $nuevoBanco) : 0;
                            $gmfEstim = (!$sinCuentas && $montoInicial > 0) ? round($montoInicial * 0.004) : 0;
                        @endphp
                        @if(!$sinCuentas && $montoInicial > 0)
                            <p class="text-xs text-amber-600 mt-1">
                                Comisión ACH: ${{ number_format($ach, 0, ',', '.') }} +
                                GMF: ${{ number_format($gmfEstim, 0, ',', '.') }} =
                                Total cargo: ${{ number_format($montoInicial + $ach + $gmfEstim, 0, ',', '.') }}
                            </p>
                        @endif
                    </div>

                    @if(!$sinCuentas && $cuentaPpal)
                        <p class="text-xs text-slate-500">
                            Saldo disponible en cuenta principal: <strong>${{ number_format($cuentaPpal->saldo, 0, ',', '.') }}</strong>
                        </p>
                    @endif

                    @if($sinCuentas)
                        <p class="text-xs text-slate-500 bg-slate-50 rounded-lg px-3 py-2">
                            Esta será tu cuenta principal. El monto consignado quedará disponible para tus operaciones.
                        </p>
                    @endif
                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="abrirSegundaCuenta"
                        class="flex-1 px-4 py-2 bg-gold-500 text-white text-sm font-semibold rounded-xl hover:bg-gold-400 transition">
                        {{ $this->cuentas->isEmpty() ? 'Crear cuenta' : 'Abrir cuenta' }}
                    </button>
                    <button wire:click="$set('showSegundaCuenta', false)"
                        class="flex-1 px-4 py-2 bg-slate-100 text-slate-700 text-sm font-semibold rounded-xl hover:bg-slate-200 transition">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
