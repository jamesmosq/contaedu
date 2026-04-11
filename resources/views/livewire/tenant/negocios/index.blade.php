<div>
    {{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-5xl mx-auto flex items-center justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Mercado interempresarial</p>
                <h1 class="font-display text-2xl font-bold text-white">Negocios</h1>
                <p class="text-forest-300 text-sm mt-1">Compra y vende con las empresas de tu grupo</p>
            </div>
            @if(! session('audit_mode') && ! session('reference_mode') && $tab === 'portafolio')
                <button wire:click="openPortafolioForm()"
                    class="px-4 py-2 bg-gold-500 text-forest-950 text-sm font-bold rounded-xl hover:bg-gold-400 transition shrink-0">
                    + Agregar producto/servicio
                </button>
            @endif
        </div>
    </div>

    <div class="px-6 py-6 lg:px-10">
        <div class="max-w-5xl mx-auto space-y-6">

            {{-- ── Panel financiero ───────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4" wire:poll.8s>
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-forest-50 flex items-center justify-center text-forest-700 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 11.219 12.768 11 12 11c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Saldo en bancos</p>
                        <p class="text-lg font-bold font-mono text-slate-800 truncate">
                            $ {{ number_format($saldoBancos, 0, ',', '.') }}
                        </p>
                        @if($cuentasBancarias->isNotEmpty())
                            @foreach($cuentasBancarias as $cta)
                                @php $dotPF = match($cta->bank) { 'bancolombia' => '#3db872', 'davivienda' => '#d4a017', 'banco_bogota' => '#71c99c', default => '#94a3b8' }; @endphp
                                <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                                    <span class="w-1.5 h-1.5 rounded-full inline-block" style="background-color:{{ $dotPF }}"></span>
                                    {{ $cta->nombreBanco() }} ***{{ $cta->ultimosDigitos() }}
                                </p>
                            @endforeach
                        @else
                            <p class="text-xs text-slate-400">cuenta 1110</p>
                        @endif
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-600 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Por cobrar</p>
                        <p class="text-lg font-bold font-mono {{ $porCobrar > 0 ? 'text-blue-700' : 'text-slate-400' }} truncate">
                            $ {{ number_format($porCobrar, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-slate-400">cuenta 1305</p>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-5 py-4 flex items-center gap-4">
                    <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-500 shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Por pagar</p>
                        <p class="text-lg font-bold font-mono {{ $porPagar > 0 ? 'text-red-600' : 'text-slate-400' }} truncate">
                            $ {{ number_format($porPagar, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-slate-400">cuenta 2205</p>
                    </div>
                </div>
            </div>

            {{-- ── Tabs ──────────────────────────────────────────────────────── --}}
            <div class="flex gap-1 bg-cream-100 rounded-xl p-1 overflow-x-auto">
                @foreach([
                    ['key' => 'comprar',     'label' => 'Comprar'],
                    ['key' => 'portafolio',  'label' => 'Mi portafolio'],
                    ['key' => 'mis_pedidos', 'label' => 'Mis pedidos'],
                    ['key' => 'recibidas',   'label' => 'Pedidos recibidos', 'badge' => $recibidasCount],
                    ['key' => 'historial',   'label' => 'Historial'],
                ] as $t)
                    <button wire:click="$set('tab', '{{ $t['key'] }}')"
                        class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition whitespace-nowrap
                            {{ $tab === $t['key']
                                ? 'bg-white text-forest-900 shadow-sm'
                                : 'text-slate-500 hover:text-slate-700' }}">
                        {{ $t['label'] }}
                        @if(!empty($t['badge']) && $t['badge'] > 0)
                            <span class="ml-1.5 px-1.5 py-0.5 text-xs bg-red-500 text-white rounded-full">{{ $t['badge'] }}</span>
                        @endif
                    </button>
                @endforeach
            </div>

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- TAB: COMPRAR — Flujo buyer-initiated                            --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            @if($tab === 'comprar')
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card divide-y divide-cream-100">

                <div class="px-6 py-4">
                    <h3 class="text-sm font-semibold text-slate-800">Comprar del mercado del grupo</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Selecciona una empresa, elige productos o servicios de su portafolio y envía el pedido</p>
                </div>

                {{-- Paso 1: Seleccionar empresa vendedora --}}
                <div class="px-6 py-5">
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">
                        ¿A quién quieres comprar? <span class="text-red-500">*</span>
                    </label>

                    @if($companeros->isEmpty())
                        <p class="text-sm text-amber-600 bg-amber-50 px-4 py-3 rounded-xl border border-amber-200">
                            Aún no hay compañeros activos en tu grupo.
                        </p>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            @foreach($companeros as $c)
                            <button type="button" wire:click="$set('seller_id', '{{ $c->id }}')"
                                class="flex items-center gap-3 px-4 py-3 rounded-xl border text-left transition
                                    {{ $seller_id === $c->id
                                        ? 'border-forest-600 bg-forest-50 shadow-sm'
                                        : 'border-cream-200 bg-white hover:border-forest-300 hover:bg-cream-50' }}">
                                <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center font-bold text-sm
                                    {{ $seller_id === $c->id ? 'bg-forest-800 text-white' : 'bg-cream-100 text-slate-500' }}">
                                    {{ strtoupper(substr($c->company_name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-800 truncate">{{ $c->company_name }}</p>
                                    <p class="text-xs text-slate-400 truncate">{{ $c->student_name }}</p>
                                </div>
                                @if($seller_id === $c->id)
                                    <span class="ml-auto text-forest-600 shrink-0">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                        </svg>
                                    </span>
                                @endif
                            </button>
                            @endforeach
                        </div>
                        @error('seller_id') <p class="text-red-500 text-xs mt-2">{{ $message }}</p> @enderror
                    @endif
                </div>

                {{-- Paso 2: Portafolio del vendedor --}}
                @if($seller_id)
                <div class="px-6 py-5">
                    @php $sellerName = $companeros->firstWhere('id', $seller_id)?->company_name ?? ''; @endphp
                    <h4 class="text-sm font-semibold text-slate-700 mb-3">
                        Portafolio de <span class="text-forest-800">{{ $sellerName }}</span>
                    </h4>

                    @if($vendedorPortafolio->isEmpty())
                        <div class="rounded-xl border border-cream-200 bg-cream-50 px-4 py-8 text-center">
                            <p class="text-sm text-slate-500">Esta empresa aún no ha publicado productos o servicios en su portafolio.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-4">
                            @foreach($vendedorPortafolio as $pi)
                            <button type="button" wire:click="addItemFromPortafolio({{ $pi->id }})"
                                class="flex items-start gap-3 px-4 py-3 rounded-xl border border-cream-200 bg-cream-50
                                       hover:border-forest-400 hover:bg-forest-50 transition text-left group">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="text-sm font-semibold text-slate-800 group-hover:text-forest-900 truncate">{{ $pi->nombre }}</span>
                                        <span class="px-1.5 py-0.5 rounded text-xs font-medium shrink-0
                                            {{ $pi->tipo === 'servicio' ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                                            {{ ucfirst($pi->tipo) }}
                                        </span>
                                    </div>
                                    @if($pi->descripcion)
                                        <p class="text-xs text-slate-400">{{ Str::limit($pi->descripcion, 60) }}</p>
                                    @endif
                                    <p class="text-xs font-semibold text-forest-800 mt-1">
                                        $ {{ number_format($pi->precio, 0, ',', '.') }}
                                        <span class="font-normal text-slate-500">+ IVA {{ $pi->iva }}%</span>
                                    </p>
                                </div>
                                <span class="w-7 h-7 rounded-lg bg-forest-100 group-hover:bg-forest-800 group-hover:text-white
                                             flex items-center justify-center text-forest-700 font-bold text-base transition shrink-0 mt-0.5">
                                    +
                                </span>
                            </button>
                            @endforeach
                        </div>
                    @endif
                </div>
                @endif

                {{-- Paso 3: Carrito con ítems seleccionados --}}
                @if(count($items) > 0)
                <div class="px-6 py-5">
                    <h4 class="text-sm font-semibold text-slate-700 mb-3">Tu pedido</h4>
                    <div class="overflow-x-auto rounded-xl border border-cream-100">
                        <table class="min-w-full text-sm">
                            <thead class="bg-cream-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Producto/Servicio</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 w-24">Cant.</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 w-32">Precio unit.</th>
                                    <th class="px-3 py-2 text-center text-xs font-semibold text-slate-600 w-16">IVA</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 w-32">Total</th>
                                    <th class="w-8"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-cream-50">
                                @foreach($items as $idx => $item)
                                <tr wire:key="item-{{ $idx }}">
                                    <td class="px-3 py-2 font-medium text-slate-800 text-xs">{{ $item['descripcion'] }}</td>
                                    <td class="px-2 py-2">
                                        <input wire:model.live="items.{{ $idx }}.cantidad"
                                            type="number" min="0.01" step="0.01"
                                            class="w-full text-xs text-right border border-cream-200 rounded-lg px-2 py-1.5 focus:ring-forest-500 focus:border-forest-500" />
                                    </td>
                                    <td class="px-3 py-2 text-right text-xs text-slate-600">$ {{ number_format($item['precio'], 0, ',', '.') }}</td>
                                    <td class="px-3 py-2 text-center text-xs text-slate-500">{{ $item['iva'] }}%</td>
                                    <td class="px-3 py-2 text-right text-xs font-semibold text-slate-800">
                                        @php
                                            $sub = round((float)($item['cantidad'] ?? 0) * (float)($item['precio'] ?? 0), 2);
                                            $iva = round($sub * (int)($item['iva'] ?? 0) / 100, 2);
                                        @endphp
                                        $ {{ number_format($sub + $iva, 0, ',', '.') }}
                                    </td>
                                    <td class="px-2 py-2 text-center">
                                        <button type="button" wire:click="removeItem({{ $idx }})"
                                            class="text-slate-300 hover:text-red-500 text-xs transition">✕</button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Totales --}}
                    @php
                        $totalSub   = collect($items)->sum(fn($i) => round((float)($i['cantidad'] ?? 0) * (float)($i['precio'] ?? 0), 2));
                        $totalIva   = collect($items)->sum(fn($i) => round((float)($i['cantidad'] ?? 0) * (float)($i['precio'] ?? 0) * (int)($i['iva'] ?? 0) / 100, 2));
                        $retefte    = $aplica_retencion ? \App\Services\IntercompanyService::calcularRetencion($totalSub) : 0;
                        $reteiva    = ($aplica_reteiva && $totalIva > 0) ? round($totalIva * 0.15, 2) : 0;
                        $reteica    = $aplica_reteica ? round($totalSub * 0.004, 2) : 0;
                        $totalFinal = $totalSub + $totalIva - $retefte - $reteiva - $reteica;
                    @endphp
                    <div class="mt-4 flex flex-col items-end gap-1 text-sm">
                        <div class="flex gap-8 text-slate-600">
                            <span>Subtotal:</span>
                            <span class="font-medium">$ {{ number_format($totalSub, 0, ',', '.') }}</span>
                        </div>
                        @if($totalIva > 0)
                        <div class="flex gap-8 text-slate-600">
                            <span>IVA:</span>
                            <span class="font-medium">$ {{ number_format($totalIva, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($retefte > 0)
                        <div class="flex gap-8 text-amber-700">
                            <span>Ret. fuente (3.5%):</span>
                            <span class="font-medium">−$ {{ number_format($retefte, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($reteiva > 0)
                        <div class="flex gap-8 text-amber-700">
                            <span>Ret. IVA (15%):</span>
                            <span class="font-medium">−$ {{ number_format($reteiva, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($reteica > 0)
                        <div class="flex gap-8 text-amber-700">
                            <span>Ret. ICA (0.4‰):</span>
                            <span class="font-medium">−$ {{ number_format($reteica, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex gap-8 text-forest-900 font-bold text-base border-t border-cream-200 pt-1 mt-1">
                            <span>Total del pedido:</span>
                            <span>$ {{ number_format($totalFinal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Datos del pedido: concepto + cuenta gasto + retenciones --}}
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Concepto <span class="text-red-500">*</span></label>
                            <input wire:model="concepto" type="text"
                                placeholder="Ej: Compra de insumos de oficina"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('concepto') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                Mi cuenta de gasto <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="gasto_code"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                @foreach($cuentasGasto as $cta)
                                    <option value="{{ $cta->code }}">{{ $cta->code }} — {{ Str::limit($cta->name, 40) }}</option>
                                @endforeach
                            </select>
                            @error('gasto_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            <p class="text-xs text-slate-400 mt-1">Esta cuenta se debitará en tu empresa al confirmar el pedido.</p>
                        </div>
                    </div>

                    {{-- Retenciones --}}
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <input wire:model.live="aplica_retencion" type="checkbox" id="aplica_retencion"
                                class="rounded border-cream-300 text-forest-600 focus:ring-forest-500" />
                            <label for="aplica_retencion" class="text-sm text-slate-700">
                                Aplica retención en la fuente
                                <span class="text-slate-400 text-xs">(3.5%)</span>
                            </label>
                        </div>
                        @if($totalIva > 0)
                        <div class="flex items-center gap-3">
                            <input wire:model.live="aplica_reteiva" type="checkbox" id="aplica_reteiva"
                                class="rounded border-cream-300 text-forest-600 focus:ring-forest-500" />
                            <label for="aplica_reteiva" class="text-sm text-slate-700">
                                Aplica retención IVA
                                <span class="text-slate-400 text-xs">(15% del IVA)</span>
                            </label>
                        </div>
                        @endif
                        <div class="flex items-center gap-3">
                            <input wire:model.live="aplica_reteica" type="checkbox" id="aplica_reteica"
                                class="rounded border-cream-300 text-forest-600 focus:ring-forest-500" />
                            <label for="aplica_reteica" class="text-sm text-slate-700">
                                Aplica retención ICA
                                <span class="text-slate-400 text-xs">(0.4‰)</span>
                            </label>
                        </div>
                    </div>
                </div>

                {{-- Cuenta destino del vendedor --}}
                @if($seller_id)
                <div class="px-6 pb-3">
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Cuenta destino del vendedor</p>
                    @if($vendedorCuentaReceptora)
                        @php
                            $dotDestino = match($vendedorCuentaReceptora->bank) {
                                'bancolombia'  => '#3db872',
                                'davivienda'   => '#d4a017',
                                'banco_bogota' => '#71c99c',
                                default        => '#94a3b8',
                            };
                            $vendedorNombre = $companeros->firstWhere('id', $seller_id)?->company_name ?? '';
                        @endphp
                        <div class="flex items-center gap-2 text-sm bg-slate-50 border border-slate-200 rounded-xl px-4 py-2.5">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color:{{ $dotDestino }}"></span>
                            <span class="font-medium text-slate-800">{{ $vendedorCuentaReceptora->nombreBanco() }} ***{{ $vendedorCuentaReceptora->ultimosDigitos() }}</span>
                            @if($vendedorNombre)
                                <span class="text-slate-400 text-xs">— {{ $vendedorNombre }}</span>
                            @endif
                        </div>
                    @else
                        <p class="text-xs text-amber-700 bg-amber-50 border border-amber-200 rounded-xl px-4 py-2.5">
                            Este vendedor aún no tiene cuenta bancaria activa. Solo puedes hacer la compra a crédito (queda en 2205 Proveedores).
                        </p>
                    @endif
                </div>
                @endif

                {{-- Forma de pago: banco o crédito --}}
                @if(! session('audit_mode') && ! session('reference_mode') && $cuentasBancarias->isNotEmpty())
                <div class="px-6 pb-4">
                    <p class="text-xs font-semibold text-slate-600 uppercase tracking-wide mb-2">Forma de pago</p>
                    <div class="grid grid-cols-1 sm:grid-cols-{{ min(3, $cuentasBancarias->count() + 1) }} gap-2">
                        {{-- Opción crédito (CxP) --}}
                        <button type="button" wire:click="$set('buyer_bank_account_id', null)"
                            class="flex items-center gap-2 px-3 py-2.5 rounded-xl border text-sm font-medium transition text-left
                                {{ is_null($buyer_bank_account_id) ? 'border-forest-500 bg-forest-50 text-forest-700' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                            <span class="w-2 h-2 rounded-full bg-slate-400 shrink-0"></span>
                            <div>
                                <p class="font-semibold text-xs">A crédito</p>
                                <p class="text-xs opacity-70">Queda en 2205 Proveedores</p>
                            </div>
                        </button>
                        {{-- Opciones de banco --}}
                        @foreach($cuentasBancarias as $cta)
                            @php
                                $dotStyle = match($cta->bank) {
                                    'bancolombia'  => 'background-color:#3db872',
                                    'davivienda'   => 'background-color:#d4a017',
                                    'banco_bogota' => 'background-color:#71c99c',
                                    default        => 'background-color:#94a3b8',
                                };
                                $saldoOk   = $cta->saldoDisponible() > 0;
                                $mismoBank = $vendedorCuentaReceptora && $cta->bank === $vendedorCuentaReceptora->bank;
                                $achCosto  = $vendedorCuentaReceptora && !$mismoBank
                                    ? \App\Services\BankService::costoAch($cta->bank, $vendedorCuentaReceptora->bank)
                                    : 0;
                            @endphp
                            <button type="button" wire:click="$set('buyer_bank_account_id', {{ $cta->id }})"
                                @if(! $saldoOk) disabled @endif
                                class="flex items-start gap-2 px-3 py-2.5 rounded-xl border text-sm font-medium transition text-left
                                    {{ $buyer_bank_account_id === $cta->id ? 'border-gold-500 bg-gold-50 text-gold-700' : ($saldoOk ? 'border-slate-200 text-slate-600 hover:border-slate-300' : 'border-slate-100 text-slate-300 cursor-not-allowed') }}">
                                <span class="w-2 h-2 rounded-full shrink-0 mt-1" style="{{ $dotStyle }}"></span>
                                <div class="min-w-0">
                                    <p class="font-semibold text-xs truncate">{{ $cta->nombreBanco() }} ***{{ $cta->ultimosDigitos() }}</p>
                                    <p class="text-xs opacity-70">${{ number_format($cta->saldo, 0, ',', '.') }}</p>
                                    @if($vendedorCuentaReceptora && $saldoOk)
                                        @if($mismoBank)
                                            <span class="inline-block mt-1 text-[10px] font-semibold text-green-700 bg-green-100 rounded px-1.5 py-0.5">Sin costo ACH</span>
                                        @else
                                            <span class="inline-block mt-1 text-[10px] font-semibold text-amber-700 bg-amber-100 rounded px-1.5 py-0.5">ACH ${{ number_format($achCosto, 0, ',', '.') }} + GMF</span>
                                        @endif
                                    @endif
                                </div>
                            </button>
                        @endforeach
                    </div>
                    @if($buyer_bank_account_id && $vendedorCuentaReceptora)
                        @php
                            $ctaSel    = $cuentasBancarias->firstWhere('id', $buyer_bank_account_id);
                            $mismoBankSel = $ctaSel && $ctaSel->bank === $vendedorCuentaReceptora->bank;
                            $achSel    = $mismoBankSel ? 0 : \App\Services\BankService::costoAch($ctaSel?->bank ?? '', $vendedorCuentaReceptora->bank);
                            $gmfSel    = isset($totalFinal) ? round($totalFinal * 0.004) : 0;
                        @endphp
                        @if($mismoBankSel)
                            <p class="text-xs text-green-700 mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                Mismo banco que el vendedor — sin comisión ACH. Solo aplica GMF 4x1000.
                            </p>
                        @else
                            <p class="text-xs text-amber-700 mt-1.5 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                Banco diferente — se aplica GMF 4x1000{{ $achSel > 0 ? ' + comisión ACH $'.number_format($achSel, 0, ',', '.') : '' }}.
                            </p>
                        @endif
                    @elseif($buyer_bank_account_id)
                        <p class="text-xs text-amber-600 mt-1.5">Al confirmar se cobrará GMF 4x1000 sobre el valor a pagar.</p>
                    @endif
                </div>
                @endif

                {{-- Botón enviar --}}
                @if(! session('audit_mode') && ! session('reference_mode'))
                <div class="px-6 py-4 flex justify-end gap-3">
                    <button wire:click="$set('items', [])"
                        class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">
                        Vaciar carrito
                    </button>
                    <button wire:click="sendOrder" wire:loading.attr="disabled"
                        class="px-5 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="sendOrder">Enviar pedido</span>
                        <span wire:loading wire:target="sendOrder">Enviando…</span>
                    </button>
                </div>
                @endif
                @endif

            </div>
            @endif

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- TAB: MI PORTAFOLIO                                              --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            @if($tab === 'portafolio')
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-cream-100">
                    <h3 class="text-sm font-semibold text-slate-800">Mi portafolio</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Define los productos y servicios que ofreces al mercado del grupo</p>
                </div>

                @if($miPortafolio->isEmpty())
                    <div class="px-6 py-12 text-center text-slate-400">
                        <p class="text-sm">Aún no tienes productos o servicios publicados.</p>
                        @if(! session('audit_mode') && ! session('reference_mode'))
                            <button wire:click="openPortafolioForm()"
                                class="mt-3 text-xs text-forest-700 font-medium hover:underline">
                                Agregar primer ítem →
                            </button>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-cream-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Nombre</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Tipo</th>
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-slate-600">Precio</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">IVA</th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-600">Cuenta ingreso</th>
                                    <th class="px-4 py-3 text-center text-xs font-semibold text-slate-600">Estado</th>
                                    @if(! session('audit_mode') && ! session('reference_mode'))
                                        <th class="px-4 py-3 w-28"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-cream-50">
                                @foreach($miPortafolio as $pi)
                                <tr wire:key="pi-{{ $pi->id }}" class="{{ $pi->activo ? '' : 'opacity-50' }}">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-slate-800">{{ $pi->nombre }}</p>
                                        @if($pi->descripcion)
                                            <p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($pi->descripcion, 60) }}</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="px-2 py-0.5 rounded-lg text-xs font-medium
                                            {{ $pi->tipo === 'servicio' ? 'bg-blue-50 text-blue-700' : 'bg-amber-50 text-amber-700' }}">
                                            {{ ucfirst($pi->tipo) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                        $ {{ number_format($pi->precio, 0, ',', '.') }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-slate-600">{{ $pi->iva }}%</td>
                                    <td class="px-4 py-3">
                                        <span class="font-mono text-xs text-forest-700">{{ $pi->cuenta_ingreso_codigo }}</span>
                                        <span class="text-xs text-slate-500 ml-1">— {{ Str::limit($pi->cuenta_ingreso_nombre, 30) }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-xs font-medium {{ $pi->activo ? 'text-green-700' : 'text-slate-400' }}">
                                            {{ $pi->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    @if(! session('audit_mode') && ! session('reference_mode'))
                                    <td class="px-4 py-3 text-right space-x-2">
                                        <button wire:click="openPortafolioForm({{ $pi->id }})"
                                            class="text-xs text-forest-700 hover:text-forest-900 font-medium">Editar</button>
                                        <button wire:click="togglePortafolioActivo({{ $pi->id }})"
                                            class="text-xs {{ $pi->activo ? 'text-slate-400 hover:text-red-500' : 'text-green-600 hover:text-green-800' }}">
                                            {{ $pi->activo ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </td>
                                    @endif
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Configuración de cobros --}}
                @if(! session('audit_mode') && ! session('reference_mode') && $cuentasBancarias->isNotEmpty())
                <div class="border-t border-cream-100 px-6 py-5">
                    <h4 class="text-sm font-semibold text-slate-700 mb-0.5">Configuración de cobros</h4>
                    <p class="text-xs text-slate-500 mb-3">¿En qué cuenta deseas recibir los pagos de tus ventas en Negocios?</p>
                    @php
                        $tieneReceptoraExplicita = $cuentasBancarias->where('recibe_pagos_negocios', true)->isNotEmpty();
                    @endphp
                    @if($cuentasBancarias->count() === 1)
                        @php $ctaUnica = $cuentasBancarias->first(); @endphp
                        <div class="flex items-center gap-3 text-sm text-slate-600 bg-slate-50 rounded-xl px-4 py-3 border border-slate-200">
                            @php $dotU = match($ctaUnica->bank) { 'bancolombia' => '#3db872', 'davivienda' => '#d4a017', 'banco_bogota' => '#71c99c', default => '#94a3b8' }; @endphp
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color:{{ $dotU }}"></span>
                            <span class="font-medium">{{ $ctaUnica->nombreBanco() }} ***{{ $ctaUnica->ultimosDigitos() }}</span>
                            <span class="text-xs text-slate-400">— se usa automáticamente (única cuenta)</span>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($cuentasBancarias as $cta)
                                @php
                                    $esReceptora = $tieneReceptoraExplicita ? $cta->recibe_pagos_negocios : $cta->es_principal;
                                    $dotC = match($cta->bank) { 'bancolombia' => '#3db872', 'davivienda' => '#d4a017', 'banco_bogota' => '#71c99c', default => '#94a3b8' };
                                @endphp
                                <button wire:click="setRecibePageos({{ $cta->id }})"
                                    class="flex items-center gap-3 px-4 py-3 rounded-xl border text-left transition
                                        {{ $esReceptora ? 'border-forest-500 bg-forest-50' : 'border-slate-200 hover:border-slate-300' }}">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color:{{ $dotC }}"></span>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium {{ $esReceptora ? 'text-forest-800' : 'text-slate-700' }} truncate">
                                            {{ $cta->nombreBanco() }} ***{{ $cta->ultimosDigitos() }}
                                        </p>
                                        <p class="text-xs text-slate-400">{{ ucfirst($cta->account_type) }} — ${{ number_format($cta->saldo, 0, ',', '.') }}</p>
                                    </div>
                                    @if($esReceptora)
                                        <svg class="w-4 h-4 text-forest-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                        <p class="text-xs text-slate-400 mt-2">Los compradores verán esta cuenta al hacer un pedido.</p>
                    @endif
                </div>
                @endif
            </div>
            @endif

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- TAB: MIS PEDIDOS (yo como comprador)                            --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            @if($tab === 'mis_pedidos')
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-cream-100">
                    <h3 class="text-sm font-semibold text-slate-800">Mis pedidos enviados</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Compras que iniciaste a otras empresas</p>
                </div>

                @if($misPedidos->isEmpty())
                    <div class="px-6 py-12 text-center text-slate-400">
                        <p class="text-sm">Aún no has realizado ningún pedido.</p>
                        <button wire:click="$set('tab', 'comprar')"
                            class="mt-3 text-xs text-forest-700 font-medium hover:underline">
                            Ir a comprar →
                        </button>
                    </div>
                @else
                    <div class="divide-y divide-cream-100">
                        @foreach($misPedidos as $inv)
                        <div class="px-6 py-4 flex flex-wrap items-start gap-4 justify-between">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-700">{{ $inv->consecutive }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $inv->statusClasses() }}">
                                        {{ $inv->statusLabel() }}
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-slate-800">Vendedor: {{ $inv->seller->company_name }}</p>
                                <p class="text-xs text-slate-500 truncate max-w-xs">{{ $inv->concepto }}</p>
                                <p class="text-xs text-slate-400">{{ $inv->created_at->format('d/m/Y H:i') }}</p>
                                @php
                                    $metodoPago = $inv->buyer_bank
                                        ? match($inv->buyer_bank) {
                                            'bancolombia'  => 'Bancolombia',
                                            'davivienda'   => 'Davivienda',
                                            'banco_bogota' => 'Banco de Bogotá',
                                            default        => ucfirst($inv->buyer_bank),
                                          }
                                        : null;
                                @endphp
                                @if($metodoPago)
                                    <p class="text-xs text-forest-600 flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                                        Pago desde {{ $metodoPago }}
                                    </p>
                                @elseif(! $inv->isPendiente())
                                    <p class="text-xs text-slate-400 flex items-center gap-1 mt-0.5">
                                        <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14.25l6-6m4.5-3.493V21.75l-3.75-1.5-3.75 1.5-3.75-1.5-3.75 1.5V4.757c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0 1 11.186 0c1.1.128 1.907 1.077 1.907 2.185Z"/></svg>
                                        A crédito (queda en CxP)
                                    </p>
                                @endif
                                @if($inv->isRechazada() && $inv->rechazo_motivo)
                                    <p class="text-xs text-red-600 italic mt-1">Motivo rechazo: {{ $inv->rechazo_motivo }}</p>
                                @endif
                            </div>
                            <div class="text-right space-y-1">
                                <p class="text-base font-bold text-slate-800">$ {{ number_format($inv->total, 0, ',', '.') }}</p>
                                <p class="text-xs text-slate-400">{{ $inv->items->count() }} ítem(s)</p>
                                @if($inv->isPendiente() && ! session('audit_mode') && ! session('reference_mode'))
                                    <button x-on:click="confirmAction(
                                        '¿Cancelar el pedido {{ $inv->consecutive }}?',
                                        () => $wire.cancelOrder({{ $inv->id }}),
                                        { danger: true, confirmText: 'Sí, cancelar' })"
                                        class="text-xs text-red-500 hover:text-red-700 font-medium transition">
                                        Cancelar pedido
                                    </button>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- TAB: PEDIDOS RECIBIDOS (yo como vendedor — confirmo o rechazo)  --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            @if($tab === 'recibidas')
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-cream-100">
                    <h3 class="text-sm font-semibold text-slate-800">Pedidos de compra recibidos</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Compañeros que quieren comprarte — confirma o rechaza cada pedido</p>
                </div>

                @if($recibidas->isEmpty())
                    <div class="px-6 py-12 text-center text-slate-400">
                        <p class="text-sm">No tienes pedidos pendientes de confirmar.</p>
                    </div>
                @else
                    <div class="divide-y divide-cream-100">
                        @foreach($recibidas as $inv)
                        <div class="px-6 py-5" wire:key="rec-{{ $inv->id }}">
                            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="font-mono text-xs font-bold text-slate-700">{{ $inv->consecutive }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pendiente confirmación</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800">Comprador: {{ $inv->buyer->company_name }}</p>
                                    <p class="text-xs text-slate-500 mt-0.5">{{ $inv->concepto }}</p>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $inv->created_at->format('d/m/Y H:i') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-lg font-bold text-slate-800">$ {{ number_format($inv->total, 0, ',', '.') }}</p>
                                    @if((float)$inv->iva > 0)
                                        <p class="text-xs text-slate-500">IVA: $ {{ number_format($inv->iva, 0, ',', '.') }}</p>
                                    @endif
                                    @if((float)$inv->retencion_fuente > 0)
                                        <p class="text-xs text-amber-700">Ret. fte.: −$ {{ number_format($inv->retencion_fuente, 0, ',', '.') }}</p>
                                    @endif
                                </div>
                            </div>

                            <div class="rounded-xl border border-cream-100 overflow-hidden mb-4">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-cream-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-semibold text-slate-600">Producto/Servicio</th>
                                            <th class="px-3 py-2 text-right font-semibold text-slate-600">Cant.</th>
                                            <th class="px-3 py-2 text-right font-semibold text-slate-600">Precio</th>
                                            <th class="px-3 py-2 text-right font-semibold text-slate-600">IVA</th>
                                            <th class="px-3 py-2 text-right font-semibold text-slate-600">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-cream-50">
                                        @foreach($inv->items as $item)
                                        <tr>
                                            <td class="px-3 py-2 text-slate-700">{{ $item->descripcion }}</td>
                                            <td class="px-3 py-2 text-right text-slate-600">{{ number_format($item->cantidad, 2, ',', '.') }}</td>
                                            <td class="px-3 py-2 text-right text-slate-600">$ {{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
                                            <td class="px-3 py-2 text-right text-slate-500">{{ $item->porcentaje_iva }}%</td>
                                            <td class="px-3 py-2 text-right font-semibold text-slate-800">$ {{ number_format($item->subtotal + $item->iva, 0, ',', '.') }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @if(! session('audit_mode') && ! session('reference_mode'))
                            <div class="flex gap-2 justify-end">
                                <button wire:click="openReject({{ $inv->id }})" @click.stop
                                    class="px-4 py-2 bg-red-50 text-red-700 text-sm font-semibold rounded-xl hover:bg-red-100 transition">
                                    Rechazar
                                </button>
                                <button wire:click="openConfirm({{ $inv->id }})" @click.stop
                                    class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                    Confirmar venta
                                </button>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- TAB: HISTORIAL                                                   --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            @if($tab === 'historial')
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-cream-100">
                    <h3 class="text-sm font-semibold text-slate-800">Historial de transacciones</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Pedidos confirmados, rechazados y anulados</p>
                </div>

                @if($historial->isEmpty())
                    <div class="px-6 py-12 text-center text-slate-400">
                        <p class="text-sm">Aún no hay transacciones completadas.</p>
                    </div>
                @else
                    <div class="divide-y divide-cream-100">
                        @foreach($historial as $inv)
                        @php
                            $soyVendedor = $inv->seller_tenant_id === tenancy()->tenant->id;
                            $contraparte = $soyVendedor ? $inv->buyer : $inv->seller;
                            $rol         = $soyVendedor ? 'Vendedor' : 'Comprador';
                            $rolClasses  = $soyVendedor ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700';
                        @endphp
                        <div class="px-6 py-4 flex flex-wrap items-center gap-4 justify-between">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-700">{{ $inv->consecutive }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $inv->statusClasses() }}">{{ $inv->statusLabel() }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $rolClasses }}">{{ $rol }}</span>
                                </div>
                                <p class="text-sm text-slate-700">
                                    {{ $soyVendedor ? 'Comprador' : 'Vendedor' }}: <span class="font-medium">{{ $contraparte->company_name }}</span>
                                </p>
                                <p class="text-xs text-slate-500">{{ $inv->concepto }}</p>
                                @if($inv->isRechazada() && $inv->rechazo_motivo)
                                    <p class="text-xs text-red-500 italic">Motivo: {{ $inv->rechazo_motivo }}</p>
                                @endif
                                <p class="text-xs text-slate-400">
                                    {{ $inv->isAceptada()
                                        ? 'Confirmada: ' . $inv->accepted_at?->format('d/m/Y H:i')
                                        : 'Rechazada: ' . $inv->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <div class="text-right space-y-1">
                                <p class="text-base font-bold {{ $soyVendedor ? 'text-green-700' : 'text-slate-800' }}">
                                    {{ $soyVendedor ? '+' : '-' }}$ {{ number_format($inv->total, 0, ',', '.') }}
                                </p>
                                @if($inv->isAceptada())
                                    @if($inv->buyer_bank)
                                        @php $bLabel = match($inv->buyer_bank) { 'bancolombia' => 'Bancolombia', 'davivienda' => 'Davivienda', 'banco_bogota' => 'Banco de Bogotá', default => ucfirst($inv->buyer_bank) }; @endphp
                                        <p class="text-xs {{ $soyVendedor ? 'text-green-600' : 'text-forest-600' }}">
                                            Banco: {{ $bLabel }}
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-400">A crédito</p>
                                    @endif
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
            @endif

        </div>
    </div>

    {{-- ── Modal: Confirmar venta ──────────────────────────────────────────── --}}
    @if($showConfirmModal)
    <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" @click.self="$wire.set('showConfirmModal', false)">
        <div class="bg-white rounded-2xl shadow-card-lg w-full max-w-md">
            <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Confirmar venta</h3>
                <button wire:click="$set('showConfirmModal', false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
            </div>
            <div class="px-6 py-5 space-y-3">
                <p class="text-sm text-slate-600">
                    Al confirmar, se registrarán los asientos contables automáticamente en <strong>tu empresa</strong> (vendedor) y en la del <strong>comprador</strong>.
                </p>
                <div class="p-3 bg-forest-50 rounded-xl text-xs text-forest-800 space-y-1">
                    <p class="font-semibold">Asiento en tu empresa (vendedor):</p>
                    <p>DR 1305 — Cuentas por cobrar</p>
                    <p>CR 4xxx — Ingresos (según tu portafolio)</p>
                    <p>CR 2408 — IVA generado (si aplica)</p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                <button wire:click="$set('showConfirmModal', false)"
                    class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">
                    Cancelar
                </button>
                <button wire:click="confirmSale" wire:loading.attr="disabled"
                    class="px-5 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="confirmSale">Confirmar y contabilizar</span>
                    <span wire:loading wire:target="confirmSale">Procesando…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Modal: Rechazar ────────────────────────────────────────────────── --}}
    @if($showRejectModal)
    <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" @click.self="$wire.set('showRejectModal', false)">
        <div class="bg-white rounded-2xl shadow-card-lg w-full max-w-md">
            <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Rechazar pedido</h3>
                <button wire:click="$set('showRejectModal', false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
            </div>
            <div class="px-6 py-5">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Motivo del rechazo <span class="text-red-500">*</span></label>
                <textarea wire:model="rechazo_motivo" rows="3"
                    placeholder="Ej: No tengo stock disponible en este momento…"
                    class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500"></textarea>
                @error('rechazo_motivo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                <button wire:click="$set('showRejectModal', false)"
                    class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">Cancelar</button>
                <button wire:click="confirmReject" wire:loading.attr="disabled"
                    class="px-5 py-2 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-500 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="confirmReject">Rechazar pedido</span>
                    <span wire:loading wire:target="confirmReject">Rechazando…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Modal: Portafolio — Crear/Editar ítem ───────────────────────────── --}}
    @if($showPortafolioForm)
    <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" @click.self="$wire.set('showPortafolioForm', false)">
        <div class="bg-white rounded-2xl shadow-card-lg w-full max-w-lg">
            <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">
                    {{ $editingPortafolioId ? 'Editar ítem' : 'Agregar al portafolio' }}
                </h3>
                <button wire:click="$set('showPortafolioForm', false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2 sm:col-span-1">
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                        <input wire:model="p_nombre" type="text"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('p_nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo <span class="text-red-500">*</span></label>
                        <div class="flex gap-2 mt-1">
                            <button type="button" wire:click="$set('p_tipo', 'producto')"
                                class="flex-1 py-2 rounded-xl text-sm font-medium border transition
                                    {{ $p_tipo === 'producto' ? 'bg-forest-800 text-white border-forest-800' : 'bg-white text-slate-600 border-cream-200 hover:border-forest-400' }}">
                                Producto
                            </button>
                            <button type="button" wire:click="$set('p_tipo', 'servicio')"
                                class="flex-1 py-2 rounded-xl text-sm font-medium border transition
                                    {{ $p_tipo === 'servicio' ? 'bg-forest-800 text-white border-forest-800' : 'bg-white text-slate-600 border-cream-200 hover:border-forest-400' }}">
                                Servicio
                            </button>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción <span class="text-slate-400 font-normal">(opcional)</span></label>
                    <textarea wire:model="p_descripcion" rows="2"
                        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Precio de venta <span class="text-red-500">*</span></label>
                        <input wire:model="p_precio" type="number" min="0.01" step="1"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('p_precio') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">IVA <span class="text-red-500">*</span></label>
                        <select wire:model="p_iva"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                            <option value="19">19%</option>
                            <option value="5">5%</option>
                            <option value="0">0% — Exento</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Cuenta de ingreso <span class="text-red-500">*</span></label>
                    <select wire:model.live="p_cuenta_codigo"
                        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                        <option value="">— Seleccionar cuenta —</option>
                        @foreach($cuentasIngreso as $cta)
                            <option value="{{ $cta->code }}">{{ $cta->code }} — {{ Str::limit($cta->name, 50) }}</option>
                        @endforeach
                    </select>
                    @error('p_cuenta_codigo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @if($sectorSugerida)
                        <p class="text-xs text-forest-700 mt-1">
                            Sugerida para tu sector:
                            <button type="button"
                                wire:click="$set('p_cuenta_codigo', '{{ $sectorSugerida['codigo'] }}')"
                                class="font-semibold hover:underline">
                                {{ $sectorSugerida['codigo'] }} — {{ $sectorSugerida['nombre'] }}
                            </button>
                        </p>
                    @endif
                </div>
            </div>
            <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                <button wire:click="$set('showPortafolioForm', false)"
                    class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">Cancelar</button>
                <button wire:click="savePortafolioItem" wire:loading.attr="disabled"
                    class="px-5 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="savePortafolioItem">{{ $editingPortafolioId ? 'Actualizar' : 'Guardar' }}</span>
                    <span wire:loading wire:target="savePortafolioItem">Guardando…</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
