<x-tenant-layout title="Facturación Electrónica">

    <x-slot name="header">
        <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Facturación electrónica</p>
        <h2 class="font-display text-2xl font-bold text-white">Facturación Electrónica</h2>
        <p class="text-forest-300 text-sm mt-1">Simulador DIAN — Ambiente de Pruebas (código 02)</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Resolución activa --}}
            @if($resolucionActiva)
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg flex flex-wrap gap-4 text-sm">
                    <div>
                        <span class="font-semibold text-blue-800">Resolución activa:</span>
                        <span class="text-blue-700">{{ $resolucionActiva->numero_resolucion }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-blue-800">Prefijo:</span>
                        <span class="text-blue-700">{{ $resolucionActiva->prefijo ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-blue-800">Rango disponible:</span>
                        <span class="text-blue-700">{{ $resolucionActiva->numero_actual }} / {{ $resolucionActiva->numero_hasta }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-blue-800">Vigencia:</span>
                        <span class="{{ $resolucionActiva->estaVigente() ? 'text-green-700' : 'text-red-700' }}">
                            {{ $resolucionActiva->fecha_desde->format('d/m/Y') }} — {{ $resolucionActiva->fecha_hasta->format('d/m/Y') }}
                            @if(! $resolucionActiva->estaVigente()) <strong>(VENCIDA)</strong> @endif
                        </span>
                    </div>
                </div>
            @else
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm">
                    No hay resolución activa. <a href="{{ fe_route('resoluciones.create') }}" class="underline font-semibold">Registrar resolución</a> para poder emitir facturas.
                </div>
            @endif

            {{-- Nota pedagógica --}}
            <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 text-sm text-sky-800 mb-6">
                <p class="font-semibold mb-1">¿Qué es la facturación electrónica en Colombia y por qué es obligatoria?</p>
                <p>Desde 2020 la DIAN exige facturación electrónica para la mayoría de contribuyentes. Una factura electrónica es un documento XML firmado digitalmente, validado por la DIAN antes de entregarse al cliente. El ciclo es: <strong>Emisión → Envío al simulador DIAN → Validación → Entrega al cliente</strong>. En ContaEdu simulamos este proceso con los estados: Borrador → Generada → Enviada → Validada/Rechazada. El receptor también puede registrar <strong>eventos</strong>: acuse de recibo, aceptación expresa o reclamo.</p>
            </div>

            {{-- Mensajes flash --}}
            @if(session('success'))
                <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-800 text-sm rounded-xl">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-800 text-sm rounded-xl">{{ session('error') }}</div>
            @endif
            @if(session('info'))
                <div class="mb-4 p-3 bg-blue-50 border border-blue-200 text-blue-800 text-sm rounded-xl">{{ session('info') }}</div>
            @endif

            {{-- Panel resumen IVA --}}
            <div x-data="{ showPagoModal: false }" class="mb-6">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-5 py-4">
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">IVA cobrado (ventas)</p>
                        <p class="text-xl font-bold text-slate-800 font-mono">$ {{ number_format($totalIvaVentas, 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-400 mt-1">Facturas emitidas + F. Electrónica</p>
                    </div>
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-5 py-4">
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">IVA pagado (compras)</p>
                        <p class="text-xl font-bold text-slate-800 font-mono">$ {{ number_format($totalIvaCompras, 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-400 mt-1">IVA descontable de facturas de compra</p>
                    </div>
                    @if($diferenciaIva > 0)
                        <div class="bg-red-50 rounded-2xl border border-red-200 shadow-card px-5 py-4">
                            <p class="text-xs font-medium text-red-600 uppercase tracking-wide mb-1">Saldo a pagar a la DIAN</p>
                            <p class="text-xl font-bold text-red-700 font-mono">$ {{ number_format($diferenciaIva, 0, ',', '.') }}</p>
                            <p class="text-xs text-red-400 mt-1">IVA ventas &minus; IVA compras</p>
                            @if(! session('audit_mode') && ! session('reference_mode'))
                                <button @click="showPagoModal = true"
                                    class="mt-3 w-full px-3 py-1.5 bg-red-600 hover:bg-red-700 text-white text-xs font-semibold rounded-xl transition">
                                    Declarar y pagar
                                </button>
                            @endif
                        </div>
                    @elseif($diferenciaIva < 0)
                        <div class="bg-green-50 rounded-2xl border border-green-200 shadow-card px-5 py-4">
                            <p class="text-xs font-medium text-green-700 uppercase tracking-wide mb-1">Saldo a favor</p>
                            <p class="text-xl font-bold text-green-700 font-mono">$ {{ number_format(abs($diferenciaIva), 0, ',', '.') }}</p>
                            <p class="text-xs text-green-500 mt-1">El IVA pagado supera el cobrado</p>
                        </div>
                    @else
                        <div class="bg-slate-50 rounded-2xl border border-slate-200 shadow-card px-5 py-4">
                            <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">IVA en equilibrio</p>
                            <p class="text-xl font-bold text-slate-600 font-mono">$ 0</p>
                            <p class="text-xs text-slate-400 mt-1">IVA ventas = IVA compras</p>
                        </div>
                    @endif
                </div>

                {{-- Modal pago IVA DIAN --}}
                <div x-show="showPagoModal" x-cloak
                    class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6" @click.stop>
                        <div class="flex items-center justify-between mb-5">
                            <h3 class="text-base font-bold text-slate-800">Pago IVA a la DIAN</h3>
                            <button @click="showPagoModal = false" class="text-slate-400 hover:text-slate-600">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>

                        <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-5">
                            <p class="text-xs text-red-600 font-medium mb-0.5">Monto a pagar a la DIAN</p>
                            <p class="text-2xl font-bold text-red-700 font-mono">$ {{ number_format($diferenciaIva, 0, ',', '.') }}</p>
                            <p class="text-xs text-red-400 mt-1">Se generará: DR 2408 IVA por pagar / CR 1110 Bancos</p>
                        </div>

                        <form method="POST" action="{{ fe_route('pagar-iva') }}">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Fecha del pago</label>
                                    <input name="fecha_pago" type="date" value="{{ old('fecha_pago', now()->toDateString()) }}"
                                        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                    @error('fecha_pago') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Cuenta bancaria</label>
                                    <select name="bank_account_id"
                                        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                        <option value="">Selecciona una cuenta…</option>
                                        @foreach($cuentasBancarias as $cuenta)
                                            <option value="{{ $cuenta->id }}" {{ old('bank_account_id') == $cuenta->id ? 'selected' : '' }}>
                                                {{ ucfirst($cuenta->bank) }} — {{ $cuenta->account_number }}
                                                (Saldo: $ {{ number_format($cuenta->saldo, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('bank_account_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-xs text-amber-800">
                                    Se aplicará el GMF (4×1000) sobre el monto pagado. El saldo bancario se reducirá en el total más el impuesto.
                                </div>
                            </div>
                            <div class="flex justify-end gap-3 mt-6">
                                <button type="button" @click="showPagoModal = false"
                                    class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancelar</button>
                                <button type="submit"
                                    class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition">
                                    Confirmar pago
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Acciones --}}
            @if(! session('audit_mode'))
            <div class="flex gap-3 mb-6 justify-end">
                <a href="{{ fe_route('resoluciones.index') }}"
                   class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition">
                    Resoluciones
                </a>
                @if($resolucionActiva && $resolucionActiva->estaVigente())
                <a href="{{ fe_route('crear') }}"
                   class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                    + Nueva Factura Electrónica
                </a>
                @endif
            </div>
            @endif

            {{-- Tabla de facturas --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">N° Factura</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Adquirente</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Fecha</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Total</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Estado</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($facturas as $factura)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-mono font-semibold text-slate-800">
                                {{ $factura->numero_completo !== 'PENDIENTE' ? $factura->numero_completo : '(borrador)' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div class="font-medium">{{ $factura->nombre_adquirente }}</div>
                                <div class="text-xs text-slate-400">{{ $factura->num_doc_adquirente }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $factura->fecha_emision->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                ${{ number_format((float) $factura->total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $factura->estado->badgeClasses() }}">
                                    {{ $factura->estado->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ fe_route('show', $factura) }}"
                                   class="text-brand-700 hover:underline text-xs font-medium">
                                    Ver detalle
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                No hay facturas electrónicas registradas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($facturas->hasPages())
                <div class="px-4 py-3 border-t border-slate-200">
                    {{ $facturas->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-tenant-layout>
