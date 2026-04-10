<div>
    {{-- ── Hero ──────────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-5xl mx-auto flex items-center justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Mercado interempresarial</p>
                <h1 class="font-display text-2xl font-bold text-white">Negocios</h1>
                <p class="text-forest-300 text-sm mt-1">Compra y vende con las empresas de tu grupo</p>
            </div>
            @if(! session('audit_mode') && ! session('reference_mode'))
                <button wire:click="openCreate" @click.stop
                    class="px-4 py-2 bg-gold-500 text-forest-950 text-sm font-bold rounded-xl hover:bg-gold-400 transition shrink-0">
                    + Nueva oferta
                </button>
            @endif
        </div>
    </div>

    <div class="px-6 py-6 lg:px-10">
        <div class="max-w-5xl mx-auto space-y-6">

            {{-- ── Tabs ──────────────────────────────────────────────────────── --}}
            <div class="flex gap-1 bg-cream-100 rounded-xl p-1">
                @foreach([
                    ['key' => 'nueva',    'label' => 'Nueva oferta'],
                    ['key' => 'enviadas', 'label' => 'Enviadas'],
                    ['key' => 'recibidas','label' => 'Recibidas', 'badge' => $recibidasCount],
                    ['key' => 'historial','label' => 'Historial'],
                ] as $t)
                    <button wire:click="$set('tab', '{{ $t['key'] }}')"
                        class="flex-1 py-2 px-3 rounded-lg text-sm font-medium transition
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
            {{-- TAB: NUEVA OFERTA                                               --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            @if($tab === 'nueva')
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card divide-y divide-cream-100"
                 x-data="negociosForm()" x-init="init()">

                {{-- Encabezado --}}
                <div class="px-6 py-4">
                    <h3 class="text-sm font-semibold text-slate-800">Crear oferta de venta</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Envía una propuesta comercial a un compañero del grupo</p>
                </div>

                {{-- Comprador + Concepto --}}
                <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Empresa compradora <span class="text-red-500">*</span></label>
                        <select wire:model="buyer_id" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                            <option value="">— Seleccionar empresa del grupo —</option>
                            @foreach($companeros as $c)
                                <option value="{{ $c->id }}">{{ $c->company_name }} ({{ $c->student_name }})</option>
                            @endforeach
                        </select>
                        @error('buyer_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        @if($companeros->isEmpty())
                            <p class="text-xs text-amber-600 mt-1">No hay compañeros activos en tu grupo aún.</p>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Concepto <span class="text-red-500">*</span></label>
                        <input wire:model="concepto" type="text" placeholder="Ej: Prestación de servicios contables"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('concepto') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Ítems --}}
                <div class="px-6 py-5">
                    <div class="flex items-center justify-between mb-3">
                        <h4 class="text-sm font-semibold text-slate-700">Ítems</h4>
                        <button type="button" wire:click="addItem"
                            class="text-xs text-forest-700 font-semibold hover:text-forest-900 transition">+ Agregar ítem</button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-cream-50">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600">Descripción</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 w-20">Cant.</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 w-32">Precio unit.</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 w-20">%IVA</th>
                                    <th class="px-3 py-2 text-left text-xs font-semibold text-slate-600 w-44">Cta. ingreso</th>
                                    <th class="px-3 py-2 text-right text-xs font-semibold text-slate-600 w-32">Total</th>
                                    <th class="w-8"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $idx => $item)
                                <tr class="border-t border-cream-100" wire:key="item-{{ $idx }}">
                                    <td class="px-2 py-2">
                                        <input wire:model="items.{{ $idx }}.descripcion" type="text"
                                            placeholder="Descripción"
                                            class="w-full text-xs border border-cream-200 rounded-lg px-2 py-1.5 focus:ring-forest-500 focus:border-forest-500" />
                                        @error("items.{$idx}.descripcion") <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-2 py-2">
                                        <input wire:model.live="items.{{ $idx }}.cantidad" type="number" min="0.01" step="0.01"
                                            class="w-full text-xs text-right border border-cream-200 rounded-lg px-2 py-1.5" />
                                    </td>
                                    <td class="px-2 py-2">
                                        <input wire:model.live="items.{{ $idx }}.precio" type="number" min="0" step="1"
                                            class="w-full text-xs text-right border border-cream-200 rounded-lg px-2 py-1.5" />
                                    </td>
                                    <td class="px-2 py-2">
                                        <select wire:model.live="items.{{ $idx }}.iva"
                                            class="w-full text-xs border border-cream-200 rounded-lg px-2 py-1.5">
                                            <option value="0">0%</option>
                                            <option value="5">5%</option>
                                            <option value="19">19%</option>
                                        </select>
                                    </td>
                                    <td class="px-2 py-2">
                                        <select wire:model="items.{{ $idx }}.cuenta"
                                            class="w-full text-xs border border-cream-200 rounded-lg px-2 py-1.5">
                                            @foreach($cuentasIngreso as $cta)
                                                <option value="{{ $cta->code }}">{{ $cta->code }} — {{ Str::limit($cta->name, 28) }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="px-2 py-2 text-right text-xs font-semibold text-slate-800">
                                        @php
                                            $sub = round(($item['cantidad'] ?? 0) * ($item['precio'] ?? 0), 2);
                                            $iva = round($sub * ($item['iva'] ?? 0) / 100, 2);
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

                    {{-- Totales ──────────────────────────────────────── --}}
                    @php
                        $totalSub   = collect($items)->sum(fn($i) => round(($i['cantidad'] ?? 0) * ($i['precio'] ?? 0), 2));
                        $totalIva   = collect($items)->sum(fn($i) => round(($i['cantidad'] ?? 0) * ($i['precio'] ?? 0) * ($i['iva'] ?? 0) / 100, 2));
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
                        @if($aplica_retencion && $retefte > 0)
                        <div class="flex gap-8 text-amber-700">
                            <span>Ret. fuente (3.5%):</span>
                            <span class="font-medium">−$ {{ number_format($retefte, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($aplica_reteiva && $reteiva > 0)
                        <div class="flex gap-8 text-amber-700">
                            <span>Ret. IVA (15%):</span>
                            <span class="font-medium">−$ {{ number_format($reteiva, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        @if($aplica_reteica && $reteica > 0)
                        <div class="flex gap-8 text-amber-700">
                            <span>Ret. ICA (0.4‰):</span>
                            <span class="font-medium">−$ {{ number_format($reteica, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        <div class="flex gap-8 text-forest-900 font-bold text-base border-t border-cream-200 pt-1 mt-1">
                            <span>Total a cobrar:</span>
                            <span>$ {{ number_format($totalFinal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Retenciones --}}
                <div class="px-6 py-4 space-y-2.5">
                    <div class="flex items-center gap-3">
                        <input wire:model.live="aplica_retencion" type="checkbox" id="aplica_retencion"
                            class="rounded border-cream-300 text-forest-600 focus:ring-forest-500" />
                        <label for="aplica_retencion" class="text-sm text-slate-700">
                            Aplica retención en la fuente
                            <span class="text-slate-400 text-xs">(3.5% servicios)</span>
                            @if($totalSub > 0 && $totalSub < \App\Services\IntercompanyService::RETEFTE_THRESHOLD)
                                <span class="text-xs text-amber-600 ml-1">— subtotal inferior al umbral (${{ number_format(\App\Services\IntercompanyService::RETEFTE_THRESHOLD, 0, ',', '.') }})</span>
                            @endif
                        </label>
                    </div>
                    @if($totalIva > 0)
                    <div class="flex items-center gap-3">
                        <input wire:model.live="aplica_reteiva" type="checkbox" id="aplica_reteiva"
                            class="rounded border-cream-300 text-forest-600 focus:ring-forest-500" />
                        <label for="aplica_reteiva" class="text-sm text-slate-700">
                            Aplica retención IVA
                            <span class="text-slate-400 text-xs">(15% del IVA — solo si el comprador es agente retenedor)</span>
                        </label>
                    </div>
                    @endif
                    <div class="flex items-center gap-3">
                        <input wire:model.live="aplica_reteica" type="checkbox" id="aplica_reteica"
                            class="rounded border-cream-300 text-forest-600 focus:ring-forest-500" />
                        <label for="aplica_reteica" class="text-sm text-slate-700">
                            Aplica retención ICA
                            <span class="text-slate-400 text-xs">(0.4‰ del subtotal — tarifa mínima)</span>
                        </label>
                    </div>
                </div>

                {{-- Acciones --}}
                @if(! session('audit_mode') && ! session('reference_mode'))
                <div class="px-6 py-4 flex justify-end gap-3">
                    <button wire:click="$set('tab', 'enviadas')"
                        class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">
                        Cancelar
                    </button>
                    <button wire:click="sendOffer" wire:loading.attr="disabled"
                        class="px-5 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="sendOffer">Enviar oferta</span>
                        <span wire:loading wire:target="sendOffer">Enviando…</span>
                    </button>
                </div>
                @endif
            </div>
            @endif

            {{-- ════════════════════════════════════════════════════════════════ --}}
            {{-- TAB: ENVIADAS                                                    --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            @if($tab === 'enviadas')
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-cream-100">
                    <h3 class="text-sm font-semibold text-slate-800">Mis ofertas enviadas</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Facturas que enviaste como vendedor</p>
                </div>

                @if($enviadas->isEmpty())
                    <div class="px-6 py-12 text-center text-slate-400">
                        <p class="text-sm">Aún no has enviado ninguna oferta.</p>
                        <button wire:click="$set('tab', 'nueva')"
                            class="mt-3 text-xs text-forest-700 font-medium hover:underline">Crear primera oferta →</button>
                    </div>
                @else
                    <div class="divide-y divide-cream-100">
                        @foreach($enviadas as $inv)
                        <div class="px-6 py-4 flex flex-wrap items-start gap-4 justify-between">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-700">{{ $inv->consecutive }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $inv->statusClasses() }}">
                                        {{ $inv->statusLabel() }}
                                    </span>
                                </div>
                                <p class="text-sm font-medium text-slate-800">{{ $inv->buyer->company_name }}</p>
                                <p class="text-xs text-slate-500 truncate max-w-xs">{{ $inv->concepto }}</p>
                                <p class="text-xs text-slate-400">{{ $inv->created_at->format('d/m/Y H:i') }}</p>
                                @if($inv->isRechazada() && $inv->rechazo_motivo)
                                    <p class="text-xs text-red-600 italic mt-1">Motivo: {{ $inv->rechazo_motivo }}</p>
                                @endif
                            </div>
                            <div class="text-right space-y-1">
                                <p class="text-base font-bold text-slate-800">$ {{ number_format($inv->total, 0, ',', '.') }}</p>
                                <p class="text-xs text-slate-400">{{ $inv->items->count() }} ítem(s)</p>
                                @if($inv->isPendiente() && ! session('audit_mode') && ! session('reference_mode'))
                                    <button x-on:click="confirmAction(
                                        '¿Cancelar la oferta {{ $inv->consecutive }}?',
                                        () => $wire.cancelOffer({{ $inv->id }}),
                                        { danger: true, confirmText: 'Sí, cancelar' })"
                                        class="text-xs text-red-500 hover:text-red-700 font-medium transition">
                                        Cancelar oferta
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
            {{-- TAB: RECIBIDAS                                                   --}}
            {{-- ════════════════════════════════════════════════════════════════ --}}
            @if($tab === 'recibidas')
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                <div class="px-6 py-4 border-b border-cream-100">
                    <h3 class="text-sm font-semibold text-slate-800">Ofertas recibidas</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Facturas de compañeros pendientes de tu respuesta</p>
                </div>

                @if($recibidas->isEmpty())
                    <div class="px-6 py-12 text-center text-slate-400">
                        <p class="text-sm">No tienes ofertas pendientes.</p>
                    </div>
                @else
                    <div class="divide-y divide-cream-100">
                        @foreach($recibidas as $inv)
                        <div class="px-6 py-5" wire:key="rec-{{ $inv->id }}">
                            {{-- Cabecera --}}
                            <div class="flex flex-wrap items-start justify-between gap-3 mb-3">
                                <div>
                                    <div class="flex items-center gap-2 mb-0.5">
                                        <span class="font-mono text-xs font-bold text-slate-700">{{ $inv->consecutive }}</span>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pendiente</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800">{{ $inv->seller->company_name }}</p>
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

                            {{-- Ítems --}}
                            <div class="rounded-xl border border-cream-100 overflow-hidden mb-4">
                                <table class="min-w-full text-xs">
                                    <thead class="bg-cream-50">
                                        <tr>
                                            <th class="px-3 py-2 text-left font-semibold text-slate-600">Descripción</th>
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

                            {{-- Acciones --}}
                            @if(! session('audit_mode') && ! session('reference_mode'))
                            <div class="flex gap-2 justify-end">
                                <button wire:click="openReject({{ $inv->id }})" @click.stop
                                    class="px-4 py-2 bg-red-50 text-red-700 text-sm font-semibold rounded-xl hover:bg-red-100 transition">
                                    Rechazar
                                </button>
                                <button wire:click="openAccept({{ $inv->id }})" @click.stop
                                    class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                    Aceptar y contabilizar
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
                    <p class="text-xs text-slate-500 mt-0.5">Ofertas aceptadas y rechazadas</p>
                </div>

                @if($historial->isEmpty())
                    <div class="px-6 py-12 text-center text-slate-400">
                        <p class="text-sm">Aún no hay transacciones completadas.</p>
                    </div>
                @else
                    <div class="divide-y divide-cream-100">
                        @foreach($historial as $inv)
                        @php
                            $soyVendedor  = $inv->seller_tenant_id === tenancy()->tenant->id;
                            $contraparte  = $soyVendedor ? $inv->buyer : $inv->seller;
                            $rol          = $soyVendedor ? 'Vendedor' : 'Comprador';
                            $rolClasses   = $soyVendedor ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700';
                        @endphp
                        <div class="px-6 py-4 flex flex-wrap items-center gap-4 justify-between">
                            <div class="space-y-0.5">
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-xs font-bold text-slate-700">{{ $inv->consecutive }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $inv->statusClasses() }}">{{ $inv->statusLabel() }}</span>
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $rolClasses }}">{{ $rol }}</span>
                                </div>
                                <p class="text-sm text-slate-700">
                                    {{ $soyVendedor ? 'A' : 'De' }}: <span class="font-medium">{{ $contraparte->company_name }}</span>
                                </p>
                                <p class="text-xs text-slate-500">{{ $inv->concepto }}</p>
                                @if($inv->isRechazada() && $inv->rechazo_motivo)
                                    <p class="text-xs text-red-500 italic">Motivo: {{ $inv->rechazo_motivo }}</p>
                                @endif
                                <p class="text-xs text-slate-400">
                                    {{ $inv->isAceptada()
                                        ? 'Aceptada: ' . $inv->accepted_at?->format('d/m/Y H:i')
                                        : 'Rechazada: ' . $inv->updated_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-base font-bold {{ $soyVendedor ? 'text-green-700' : 'text-slate-800' }}">
                                    {{ $soyVendedor ? '+' : '' }}$ {{ number_format($inv->total, 0, ',', '.') }}
                                </p>
                                @if((float)$inv->retencion_fuente > 0)
                                    <p class="text-xs text-amber-600">Ret. fte.: $ {{ number_format($inv->retencion_fuente, 0, ',', '.') }}</p>
                                @endif
                                @if((float)$inv->retencion_iva > 0)
                                    <p class="text-xs text-amber-600">Ret. IVA: $ {{ number_format($inv->retencion_iva, 0, ',', '.') }}</p>
                                @endif
                                @if((float)$inv->retencion_ica > 0)
                                    <p class="text-xs text-amber-600">Ret. ICA: $ {{ number_format($inv->retencion_ica, 0, ',', '.') }}</p>
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

    {{-- ── Modal: Aceptar oferta ───────────────────────────────────────────── --}}
    @if($showAcceptModal)
    <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" @click.self="$wire.set('showAcceptModal', false)">
        <div class="bg-white rounded-2xl shadow-card-lg w-full max-w-md">
            <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Aceptar y contabilizar</h3>
                <button wire:click="$set('showAcceptModal', false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
            </div>
            <div class="px-6 py-5 space-y-4">
                <p class="text-sm text-slate-600">
                    Al aceptar, se crearán asientos contables automáticamente en <strong>tu empresa</strong> y en la empresa del <strong>vendedor</strong>.
                </p>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1.5">¿Qué compras? — Cuenta de gasto o activo <span class="text-red-500">*</span></label>
                    <select wire:model="gasto_code"
                        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                        @foreach($cuentasGasto as $cta)
                            <option value="{{ $cta->code }}">{{ $cta->code }} — {{ Str::limit($cta->name, 45) }}</option>
                        @endforeach
                    </select>
                    @error('gasto_code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-400 mt-1">Esta cuenta se debitará en tu empresa (comprador).</p>
                </div>
                <div class="p-3 bg-forest-50 rounded-xl text-xs text-forest-800 space-y-1">
                    <p class="font-semibold">Asiento que se registrará en tu empresa:</p>
                    <p>DR {{ $gasto_code }} — Gasto/Activo</p>
                    <p>DR 2408 — IVA descontable</p>
                    <p>CR 2205 — Cuentas por pagar</p>
                    <p>CR 2365 — Retención practicada (si aplica)</p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                <button wire:click="$set('showAcceptModal', false)"
                    class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">Cancelar</button>
                <button wire:click="confirmAccept" wire:loading.attr="disabled"
                    class="px-5 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="confirmAccept">Confirmar y contabilizar</span>
                    <span wire:loading wire:target="confirmAccept">Contabilizando…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Modal: Rechazar oferta ──────────────────────────────────────────── --}}
    @if($showRejectModal)
    <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" @click.self="$wire.set('showRejectModal', false)">
        <div class="bg-white rounded-2xl shadow-card-lg w-full max-w-md">
            <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Rechazar oferta</h3>
                <button wire:click="$set('showRejectModal', false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
            </div>
            <div class="px-6 py-5">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Motivo del rechazo <span class="text-red-500">*</span></label>
                <textarea wire:model="rechazo_motivo" rows="3"
                    placeholder="Ej: El precio no corresponde a lo acordado previamente…"
                    class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500"></textarea>
                @error('rechazo_motivo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                <button wire:click="$set('showRejectModal', false)"
                    class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">Cancelar</button>
                <button wire:click="confirmReject" wire:loading.attr="disabled"
                    class="px-5 py-2 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-500 transition disabled:opacity-50">
                    <span wire:loading.remove wire:target="confirmReject">Rechazar oferta</span>
                    <span wire:loading wire:target="confirmReject">Rechazando…</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    @push('scripts')
    <script>
    function negociosForm() {
        return { init() {} };
    }
    </script>
    @endpush
</div>
