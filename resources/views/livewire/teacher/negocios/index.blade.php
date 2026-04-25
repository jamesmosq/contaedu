<div class="p-6 space-y-6">

    {{-- ── Header ──────────────────────────────────────────────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="font-display text-2xl font-bold text-slate-800">Mercado del grupo</h1>
            <p class="text-sm text-slate-500 mt-0.5">Transacciones interempresariales entre estudiantes</p>
        </div>

        {{-- Filtro de grupo --}}
        @if($groups->count() > 1)
            <select wire:model.live="groupFilter"
                class="text-sm border border-cream-300 rounded-xl px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-forest-400">
                <option value="0">Todos los grupos</option>
                @foreach($groups as $g)
                    <option value="{{ $g->id }}">{{ $g->name }} — {{ $g->period }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- ── Tarjetas de estadísticas ─────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
        @php
            $statCards = [
                ['label' => 'Pendientes',  'value' => $stats['pendientes'],                                    'color' => 'amber'],
                ['label' => 'Aceptadas',   'value' => $stats['aceptadas'],                                     'color' => 'green'],
                ['label' => 'Rechazadas',  'value' => $stats['rechazadas'],                                    'color' => 'red'],
                ['label' => 'Anuladas',    'value' => $stats['anuladas'],                                      'color' => 'slate'],
                ['label' => 'Volumen',     'value' => '$' . number_format($stats['volumen'], 0, ',', '.'),     'color' => 'forest'],
            ];
        @endphp
        @foreach($statCards as $card)
            <div class="bg-white rounded-2xl border border-cream-200 p-4 shadow-card-sm">
                <p class="text-xs text-slate-500 font-medium uppercase tracking-wide">{{ $card['label'] }}</p>
                <p class="text-xl font-bold text-slate-800 mt-1">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    {{-- ── Tabs ─────────────────────────────────────────────────────────────── --}}
    <div class="flex gap-1 border-b border-cream-200">
        @foreach([
            ['key' => 'pendientes', 'label' => 'Pendientes', 'badge' => $stats['pendientes']],
            ['key' => 'historial',  'label' => 'Historial',  'badge' => null],
            ['key' => 'ranking',    'label' => 'Ranking',    'badge' => null],
        ] as $t)
            <button wire:click="$set('tab', '{{ $t['key'] }}')"
                class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium border-b-2 transition-colors
                    {{ $tab === $t['key']
                        ? 'border-forest-600 text-forest-700'
                        : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                {{ $t['label'] }}
                @if($t['badge'])
                    <span class="bg-amber-100 text-amber-700 text-xs font-bold px-1.5 py-0.5 rounded-full">
                        {{ $t['badge'] }}
                    </span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- ── Tab: Pendientes ─────────────────────────────────────────────────── --}}
    @if($tab === 'pendientes')
        @if($pendientes->isEmpty())
            <div class="bg-white rounded-2xl border border-cream-200 p-12 text-center shadow-card-sm">
                <svg class="w-10 h-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z"/>
                </svg>
                <p class="text-slate-500 font-medium">No hay ofertas pendientes</p>
                <p class="text-slate-400 text-sm mt-1">Las transacciones enviadas por los estudiantes aparecerán aquí</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($pendientes as $inv)
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
                        <div class="p-4">
                            <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                                <div>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-mono text-xs text-slate-500">{{ $inv->consecutive }}</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $inv->statusClasses() }}">
                                            {{ $inv->statusLabel() }}
                                        </span>
                                        <span class="text-xs text-slate-400">{{ $inv->created_at->diffForHumans() }}</span>
                                    </div>
                                    <p class="text-sm font-semibold text-slate-800 mt-1">{{ $inv->concepto }}</p>
                                    <div class="flex items-center gap-3 mt-1.5 text-xs text-slate-500">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016 2.993 2.993 0 0 0 2.25-1.016 3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/>
                                            </svg>
                                            Vendedor: <strong>{{ $inv->seller?->company_name ?? '—' }}</strong>
                                        </span>
                                        <span>→</span>
                                        <span>Comprador: <strong>{{ $inv->buyer?->company_name ?? '—' }}</strong></span>
                                    </div>
                                </div>
                                <div class="text-right shrink-0 flex flex-col items-end gap-1">
                                    <p class="text-lg font-bold text-slate-800">${{ number_format($inv->total, 0, ',', '.') }}</p>
                                    @if($inv->retencion_fuente > 0)
                                        <p class="text-xs text-orange-600">Ret. fte: ${{ number_format($inv->retencion_fuente, 0, ',', '.') }}</p>
                                    @endif
                                    @if($inv->retencion_iva > 0)
                                        <p class="text-xs text-orange-600">Ret. IVA: ${{ number_format($inv->retencion_iva, 0, ',', '.') }}</p>
                                    @endif
                                    @if($inv->retencion_ica > 0)
                                        <p class="text-xs text-orange-600">Ret. ICA: ${{ number_format($inv->retencion_ica, 0, ',', '.') }}</p>
                                    @endif
                                    <p class="text-xs text-slate-400">IVA: ${{ number_format($inv->iva, 0, ',', '.') }}</p>
                                    <button wire:click="openDetail({{ $inv->id }})"
                                        class="mt-1 text-xs text-forest-600 hover:text-forest-800 font-medium hover:underline transition-colors">
                                        Ver detalle →
                                    </button>
                                </div>
                            </div>

                            {{-- Ítems --}}
                            @if($inv->items->count())
                                <div class="mt-3 border-t border-cream-100 pt-3">
                                    <table class="w-full text-xs text-slate-600">
                                        <thead>
                                            <tr class="text-slate-400 uppercase tracking-wide">
                                                <th class="text-left pb-1 font-medium">Descripción</th>
                                                <th class="text-right pb-1 font-medium">Cant.</th>
                                                <th class="text-right pb-1 font-medium">Precio</th>
                                                <th class="text-right pb-1 font-medium">IVA</th>
                                                <th class="text-right pb-1 font-medium">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-cream-100">
                                            @foreach($inv->items as $item)
                                                <tr>
                                                    <td class="py-1 pr-2">{{ $item->descripcion }}</td>
                                                    <td class="py-1 text-right">{{ number_format($item->cantidad, 0) }}</td>
                                                    <td class="py-1 text-right">${{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
                                                    <td class="py-1 text-right">{{ $item->porcentaje_iva }}%</td>
                                                    <td class="py-1 text-right font-medium">${{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    @endif

    {{-- ── Tab: Historial ──────────────────────────────────────────────────── --}}
    @if($tab === 'historial')
        @if($historial->isEmpty())
            <div class="bg-white rounded-2xl border border-cream-200 p-12 text-center shadow-card-sm">
                <p class="text-slate-500">Aún no hay transacciones cerradas en este grupo.</p>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-cream-200">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Consecutivo</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Vendedor</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Comprador</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cream-100">
                        @foreach($historial as $inv)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-mono text-xs text-slate-600">{{ $inv->consecutive }}</span>
                                    <p class="text-xs text-slate-400 mt-0.5">{{ $inv->updated_at->format('d/m/Y') }}</p>
                                </td>
                                <td class="px-4 py-3 text-slate-700 font-medium">{{ $inv->seller?->company_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-slate-700">{{ $inv->buyer?->company_name ?? '—' }}</td>
                                <td class="px-4 py-3 text-right font-semibold text-slate-800">${{ number_format($inv->total, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $inv->statusClasses() }}">
                                        {{ $inv->statusLabel() }}
                                    </span>
                                    @if($inv->isAnulada() && $inv->anuladoPor)
                                        <p class="text-xs text-slate-400 mt-0.5">por {{ $inv->anuladoPor->name }}</p>
                                    @endif
                                    @if($inv->isRechazada() && $inv->rechazo_motivo)
                                        <p class="text-xs text-slate-400 mt-0.5 max-w-xs truncate">{{ $inv->rechazo_motivo }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button wire:click="openDetail({{ $inv->id }})"
                                            class="text-xs text-forest-600 hover:text-forest-800 font-medium hover:underline transition-colors">
                                            Ver
                                        </button>
                                        @if($inv->isAceptada())
                                            <span class="text-slate-200">|</span>
                                            <button wire:click.stop="openAnnul({{ $inv->id }})"
                                                class="text-xs text-red-600 hover:text-red-700 font-medium hover:underline transition-colors">
                                                Anular
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endif

    {{-- ── Tab: Ranking ─────────────────────────────────────────────────────── --}}
    @if($tab === 'ranking')
        @if($ranking->isEmpty())
            <div class="bg-white rounded-2xl border border-cream-200 p-12 text-center shadow-card-sm">
                <p class="text-slate-500">No hay estudiantes en este grupo.</p>
            </div>
        @else
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 border-b border-cream-200">
                        <tr>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">#</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Ventas</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Vol. Ventas</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Compras</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Vol. Compras</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Con retención</th>
                            <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Ciclo completo</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cream-100">
                        @foreach($ranking->values() as $i => $row)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-4 py-3">
                                    @if($i < 3)
                                        <span class="text-lg">{{ ['🥇','🥈','🥉'][$i] }}</span>
                                    @else
                                        <span class="text-slate-400 font-mono text-xs">{{ $i + 1 }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-semibold text-slate-800">{{ $row['tenant']->company_name }}</p>
                                    <p class="text-xs text-slate-400">{{ $row['tenant']->student_name }}</p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold text-forest-700">{{ $row['ventas'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-700">${{ number_format($row['total_ventas'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="font-bold text-blue-700">{{ $row['compras'] }}</span>
                                </td>
                                <td class="px-4 py-3 text-right text-slate-700">${{ number_format($row['total_compras'], 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-center">
                                    @if($row['con_retencion'] > 0)
                                        <span class="text-orange-600 font-semibold">{{ $row['con_retencion'] }}</span>
                                    @else
                                        <span class="text-slate-300">0</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($row['ciclo_completo'])
                                        <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700 bg-green-50 px-2 py-0.5 rounded-full">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                            </svg>
                                            Sí
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Leyenda ciclo completo --}}
            <p class="text-xs text-slate-400 text-center mt-2">
                "Ciclo completo" = al menos 1 venta aceptada + 1 compra aceptada en el periodo
            </p>
        @endif
    @endif

    {{-- ── Modal: Detalle de transacción ──────────────────────────────────── --}}
    @if($showDetailModal && $detailInvoice)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl max-h-[90vh] flex flex-col" @click.stop>

                {{-- Cabecera --}}
                <div class="px-6 py-4 border-b border-cream-200 flex items-center justify-between gap-4 shrink-0">
                    <div class="flex items-center gap-3">
                        <h3 class="font-display text-base font-bold text-slate-800">
                            Transacción {{ $detailInvoice->consecutive }}
                        </h3>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $detailInvoice->statusClasses() }}">
                            {{ $detailInvoice->statusLabel() }}
                        </span>
                    </div>
                    <button wire:click="closeDetail"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition text-sm shrink-0">✕</button>
                </div>

                {{-- Cuerpo con scroll --}}
                <div class="overflow-y-auto flex-1 px-6 py-5 space-y-5">

                    {{-- Flujo vendedor → comprador --}}
                    <div class="flex items-center gap-3 flex-wrap">
                        <div class="flex-1 min-w-0 bg-forest-50 rounded-xl px-4 py-3">
                            <p class="text-xs text-forest-500 font-medium uppercase tracking-wide mb-0.5">Vendedor</p>
                            <p class="font-semibold text-forest-900 truncate">{{ $detailInvoice->seller?->company_name ?? '—' }}</p>
                            <p class="text-xs text-forest-600">{{ $detailInvoice->seller?->student_name ?? '' }}</p>
                        </div>
                        <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/>
                        </svg>
                        <div class="flex-1 min-w-0 bg-blue-50 rounded-xl px-4 py-3">
                            <p class="text-xs text-blue-500 font-medium uppercase tracking-wide mb-0.5">Comprador</p>
                            <p class="font-semibold text-blue-900 truncate">{{ $detailInvoice->buyer?->company_name ?? '—' }}</p>
                            <p class="text-xs text-blue-600">{{ $detailInvoice->buyer?->student_name ?? '' }}</p>
                        </div>
                    </div>

                    {{-- Concepto y montos --}}
                    <div class="bg-slate-50 rounded-xl px-4 py-3 space-y-1.5">
                        <p class="text-sm font-semibold text-slate-800">{{ $detailInvoice->concepto }}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs pt-1">
                            <div>
                                <p class="text-slate-500">Subtotal</p>
                                <p class="font-semibold text-slate-800">${{ number_format($detailInvoice->subtotal, 0, ',', '.') }}</p>
                            </div>
                            <div>
                                <p class="text-slate-500">IVA</p>
                                <p class="font-semibold text-slate-800">${{ number_format($detailInvoice->iva, 0, ',', '.') }}</p>
                            </div>
                            @if($detailInvoice->retencion_fuente > 0)
                            <div>
                                <p class="text-orange-500">Ret. Fuente</p>
                                <p class="font-semibold text-orange-700">-${{ number_format($detailInvoice->retencion_fuente, 0, ',', '.') }}</p>
                            </div>
                            @endif
                            @if($detailInvoice->retencion_iva > 0)
                            <div>
                                <p class="text-orange-500">Ret. IVA</p>
                                <p class="font-semibold text-orange-700">-${{ number_format($detailInvoice->retencion_iva, 0, ',', '.') }}</p>
                            </div>
                            @endif
                            @if($detailInvoice->retencion_ica > 0)
                            <div>
                                <p class="text-orange-500">Ret. ICA</p>
                                <p class="font-semibold text-orange-700">-${{ number_format($detailInvoice->retencion_ica, 0, ',', '.') }}</p>
                            </div>
                            @endif
                            <div>
                                <p class="text-slate-500">Total</p>
                                <p class="font-bold text-forest-800 text-sm">${{ number_format($detailInvoice->total, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Ítems --}}
                    @if($detailInvoice->items->count())
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Ítems de la oferta</p>
                            <div class="border border-cream-200 rounded-xl overflow-hidden">
                                <table class="w-full text-xs">
                                    <thead class="bg-slate-50 border-b border-cream-100">
                                        <tr>
                                            <th class="text-left px-3 py-2 text-slate-500 font-medium">Descripción</th>
                                            <th class="text-right px-3 py-2 text-slate-500 font-medium">Cant.</th>
                                            <th class="text-right px-3 py-2 text-slate-500 font-medium">Precio</th>
                                            <th class="text-right px-3 py-2 text-slate-500 font-medium">IVA</th>
                                            <th class="text-right px-3 py-2 text-slate-500 font-medium">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-cream-100">
                                        @foreach($detailInvoice->items as $item)
                                            <tr>
                                                <td class="px-3 py-2 text-slate-700">{{ $item->descripcion }}</td>
                                                <td class="px-3 py-2 text-right">{{ number_format($item->cantidad, 0) }}</td>
                                                <td class="px-3 py-2 text-right">${{ number_format($item->precio_unitario, 0, ',', '.') }}</td>
                                                <td class="px-3 py-2 text-right">{{ $item->porcentaje_iva }}%</td>
                                                <td class="px-3 py-2 text-right font-medium">${{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    {{-- Asientos contables --}}
                    @if($detailInvoice->isAceptada() || $detailInvoice->isAnulada())
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">
                                Asientos contables generados
                            </p>

                            @php
                                $sellerEntry = $detailJournalData['seller'] ?? null;
                                $buyerEntry  = $detailJournalData['buyer']  ?? null;
                                $bothPresent = $sellerEntry && $buyerEntry;
                                $bothBalanced = ($sellerEntry['balanced'] ?? false) && ($buyerEntry['balanced'] ?? false);
                            @endphp

                            {{-- Indicador global --}}
                            <div class="flex items-center gap-2 mb-3">
                                @if($bothPresent && $bothBalanced)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-3 py-1.5 rounded-full">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
                                        </svg>
                                        Contabilización correcta — ambos asientos cuadran
                                    </span>
                                @elseif($bothPresent && !$bothBalanced)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-700 bg-red-50 border border-red-200 px-3 py-1.5 rounded-full">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                        </svg>
                                        Asientos generados pero desbalanceados
                                    </span>
                                @elseif(!$sellerEntry || !$buyerEntry)
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-700 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-full">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                                        </svg>
                                        Faltan asientos — la contabilización pudo ser incompleta
                                    </span>
                                @endif
                            </div>

                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                {{-- Asiento vendedor --}}
                                <div class="border border-forest-200 rounded-xl overflow-hidden">
                                    <div class="bg-forest-50 px-3 py-2 flex items-center justify-between">
                                        <p class="text-xs font-semibold text-forest-700">Vendedor — {{ $detailInvoice->seller?->company_name }}</p>
                                        @if($sellerEntry)
                                            @if($sellerEntry['balanced'])
                                                <span class="text-xs text-emerald-600 font-medium">Cuadra</span>
                                            @else
                                                <span class="text-xs text-red-600 font-medium">Desbalanceado</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-amber-600 font-medium">Sin asiento</span>
                                        @endif
                                    </div>
                                    @if($sellerEntry)
                                        <div class="px-3 py-2 border-b border-forest-100">
                                            <p class="text-xs text-slate-500">Ref: <span class="font-mono font-medium text-slate-700">{{ $sellerEntry['reference'] }}</span></p>
                                        </div>
                                        <table class="w-full text-xs">
                                            <thead class="bg-slate-50">
                                                <tr>
                                                    <th class="text-left px-3 py-1.5 text-slate-400 font-medium">Cuenta</th>
                                                    <th class="text-right px-2 py-1.5 text-slate-400 font-medium">Débito</th>
                                                    <th class="text-right px-2 py-1.5 text-slate-400 font-medium">Crédito</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50">
                                                @foreach($sellerEntry['lines'] as $line)
                                                    <tr>
                                                        <td class="px-3 py-1.5">
                                                            <span class="font-mono text-slate-500">{{ $line['code'] }}</span>
                                                            <span class="text-slate-600 ml-1">{{ Str::limit($line['name'], 25) }}</span>
                                                        </td>
                                                        <td class="px-2 py-1.5 text-right {{ $line['debit'] > 0 ? 'font-medium text-slate-800' : 'text-slate-300' }}">
                                                            {{ $line['debit'] > 0 ? '$'.number_format($line['debit'], 0, ',', '.') : '—' }}
                                                        </td>
                                                        <td class="px-2 py-1.5 text-right {{ $line['credit'] > 0 ? 'font-medium text-slate-800' : 'text-slate-300' }}">
                                                            {{ $line['credit'] > 0 ? '$'.number_format($line['credit'], 0, ',', '.') : '—' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="border-t border-forest-200 bg-forest-50">
                                                <tr>
                                                    <td class="px-3 py-1.5 text-xs font-semibold text-forest-700">Total</td>
                                                    <td class="px-2 py-1.5 text-right text-xs font-semibold text-slate-700">${{ number_format($sellerEntry['total_debit'], 0, ',', '.') }}</td>
                                                    <td class="px-2 py-1.5 text-right text-xs font-semibold text-slate-700">${{ number_format($sellerEntry['total_credit'], 0, ',', '.') }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    @else
                                        <div class="px-3 py-6 text-center">
                                            <p class="text-xs text-slate-400">No se encontró el asiento contable.</p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Asiento comprador --}}
                                <div class="border border-blue-200 rounded-xl overflow-hidden">
                                    <div class="bg-blue-50 px-3 py-2 flex items-center justify-between">
                                        <p class="text-xs font-semibold text-blue-700">Comprador — {{ $detailInvoice->buyer?->company_name }}</p>
                                        @if($buyerEntry)
                                            @if($buyerEntry['balanced'])
                                                <span class="text-xs text-emerald-600 font-medium">Cuadra</span>
                                            @else
                                                <span class="text-xs text-red-600 font-medium">Desbalanceado</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-amber-600 font-medium">Sin asiento</span>
                                        @endif
                                    </div>
                                    @if($buyerEntry)
                                        <div class="px-3 py-2 border-b border-blue-100">
                                            <p class="text-xs text-slate-500">Ref: <span class="font-mono font-medium text-slate-700">{{ $buyerEntry['reference'] }}</span></p>
                                        </div>
                                        <table class="w-full text-xs">
                                            <thead class="bg-slate-50">
                                                <tr>
                                                    <th class="text-left px-3 py-1.5 text-slate-400 font-medium">Cuenta</th>
                                                    <th class="text-right px-2 py-1.5 text-slate-400 font-medium">Débito</th>
                                                    <th class="text-right px-2 py-1.5 text-slate-400 font-medium">Crédito</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-50">
                                                @foreach($buyerEntry['lines'] as $line)
                                                    <tr>
                                                        <td class="px-3 py-1.5">
                                                            <span class="font-mono text-slate-500">{{ $line['code'] }}</span>
                                                            <span class="text-slate-600 ml-1">{{ Str::limit($line['name'], 25) }}</span>
                                                        </td>
                                                        <td class="px-2 py-1.5 text-right {{ $line['debit'] > 0 ? 'font-medium text-slate-800' : 'text-slate-300' }}">
                                                            {{ $line['debit'] > 0 ? '$'.number_format($line['debit'], 0, ',', '.') : '—' }}
                                                        </td>
                                                        <td class="px-2 py-1.5 text-right {{ $line['credit'] > 0 ? 'font-medium text-slate-800' : 'text-slate-300' }}">
                                                            {{ $line['credit'] > 0 ? '$'.number_format($line['credit'], 0, ',', '.') : '—' }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot class="border-t border-blue-200 bg-blue-50">
                                                <tr>
                                                    <td class="px-3 py-1.5 text-xs font-semibold text-blue-700">Total</td>
                                                    <td class="px-2 py-1.5 text-right text-xs font-semibold text-slate-700">${{ number_format($buyerEntry['total_debit'], 0, ',', '.') }}</td>
                                                    <td class="px-2 py-1.5 text-right text-xs font-semibold text-slate-700">${{ number_format($buyerEntry['total_credit'], 0, ',', '.') }}</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    @else
                                        <div class="px-3 py-6 text-center">
                                            <p class="text-xs text-slate-400">No se encontró el asiento contable.</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                    @elseif($detailInvoice->isPendiente())
                        <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                            <p class="text-xs font-semibold text-amber-700">Transacción pendiente</p>
                            <p class="text-xs text-amber-600 mt-0.5">Los asientos contables se generarán automáticamente cuando el comprador acepte la oferta.</p>
                        </div>

                    @elseif($detailInvoice->isRechazada())
                        <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3">
                            <p class="text-xs font-semibold text-slate-600">Transacción rechazada</p>
                            @if($detailInvoice->rechazo_motivo)
                                <p class="text-xs text-slate-500 mt-0.5">Motivo: {{ $detailInvoice->rechazo_motivo }}</p>
                            @endif
                        </div>
                    @endif

                    {{-- Info de anulación --}}
                    @if($detailInvoice->isAnulada() && $detailInvoice->anulacion_motivo)
                        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3">
                            <p class="text-xs font-semibold text-red-700">Motivo de anulación</p>
                            <p class="text-xs text-red-600 mt-0.5">{{ $detailInvoice->anulacion_motivo }}</p>
                        </div>
                    @endif

                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-cream-100 flex items-center justify-between shrink-0">
                    <p class="text-xs text-slate-400">
                        Creada {{ $detailInvoice->created_at->format('d/m/Y H:i') }}
                        @if($detailInvoice->accepted_at)
                            · Aceptada {{ $detailInvoice->accepted_at->format('d/m/Y H:i') }}
                        @endif
                    </p>
                    <div class="flex items-center gap-3">
                        @if($detailInvoice->isAceptada())
                            <button
                                x-on:click="confirmAction(
                                    '¿Anular la transacción {{ addslashes($detailInvoice->consecutive) }}? Se revertirán los asientos en ambas empresas.',
                                    () => $wire.openAnnul({{ $detailInvoice->id }}),
                                    { danger: true, confirmText: 'Sí, anular' }
                                )"
                                class="px-4 py-2 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition">
                                Anular transacción
                            </button>
                        @endif
                        <button wire:click="closeDetail"
                            class="px-4 py-2 text-sm {{ $detailInvoice->isAceptada() ? 'text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50' : 'bg-forest-800 text-white font-semibold rounded-xl hover:bg-forest-700' }} transition">
                            {{ $detailInvoice->isAceptada() ? 'Dejar aceptada' : 'Cerrar' }}
                        </button>
                    </div>
                </div>

            </div>
        </div>
    @endif

    {{-- ── Modal: Anular transacción ───────────────────────────────────────── --}}
    @if($showAnnulModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" @click.stop>
                <div class="p-6 border-b border-cream-200">
                    <h3 class="font-display text-lg font-bold text-red-700">Anular transacción</h3>
                    <p class="text-sm text-slate-500 mt-1">
                        Esta acción revertirá los asientos contables en ambas empresas. No se puede deshacer.
                    </p>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Motivo de anulación <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="anulacion_motivo" rows="3"
                            placeholder="Describe el motivo de la anulación (mínimo 10 caracteres)..."
                            class="w-full border border-cream-300 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none"></textarea>
                        @error('anulacion_motivo')
                            <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="p-6 pt-0 flex gap-3 justify-end">
                    <button wire:click="$set('showAnnulModal', false)"
                        class="px-4 py-2 text-sm font-medium text-slate-600 hover:text-slate-800 transition">
                        Cancelar
                    </button>
                    <button wire:click.stop="confirmAnnul"
                        class="px-5 py-2 bg-red-600 text-white text-sm font-semibold rounded-xl hover:bg-red-700 transition">
                        Confirmar anulación
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
