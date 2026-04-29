<div>
    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Operaciones</p>
                <h1 class="font-display text-2xl font-bold text-white">Facturas de venta</h1>
                <p class="text-forest-300 text-sm mt-1">Ciclo de facturación y cobro</p>
            </div>
            @if(!session('audit_mode') && !session('reference_mode'))
                <div>
                    @if($activeTab === 'facturas')
                        <button wire:click="openCreate" class="px-4 py-2 bg-gold-500 text-white text-sm font-semibold rounded-xl hover:bg-gold-400 transition">
                            + Nueva factura
                        </button>
                    @elseif($activeTab === 'compras')
                        <button wire:click="openPiCreate" class="px-4 py-2 bg-gold-500 text-white text-sm font-semibold rounded-xl hover:bg-gold-400 transition">
                            + Nueva factura de compra
                        </button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">

            <div class="flex gap-1 mb-6 border-b border-cream-200">
                <button wire:click="$set('activeTab','facturas')" class="px-4 py-2 text-sm font-medium transition {{ $activeTab === 'facturas' ? 'border-b-2 border-forest-700 text-forest-800' : 'text-slate-500 hover:text-slate-700' }}">
                    Facturas de venta
                </button>
                <button wire:click="$set('activeTab','compras')" class="px-4 py-2 text-sm font-medium transition {{ $activeTab === 'compras' ? 'border-b-2 border-forest-700 text-forest-800' : 'text-slate-500 hover:text-slate-700' }}">
                    Facturas de compra
                </button>
                <button wire:click="$set('activeTab','recibos')" class="px-4 py-2 text-sm font-medium transition {{ $activeTab === 'recibos' ? 'border-b-2 border-forest-700 text-forest-800' : 'text-slate-500 hover:text-slate-700' }}">
                    Recibos de caja
                </button>
                <button wire:click="$set('activeTab','notas')" class="px-4 py-2 text-sm font-medium transition {{ $activeTab === 'notas' ? 'border-b-2 border-forest-700 text-forest-800' : 'text-slate-500 hover:text-slate-700' }}">
                    Notas de crédito
                </button>
                <button wire:click="$set('activeTab','notas_debito')" class="px-4 py-2 text-sm font-medium transition {{ $activeTab === 'notas_debito' ? 'border-b-2 border-forest-700 text-forest-800' : 'text-slate-500 hover:text-slate-700' }}">
                    Notas débito
                </button>
                <button wire:click="$set('activeTab','fe')" class="px-4 py-2 text-sm font-medium transition {{ $activeTab === 'fe' ? 'border-b-2 border-forest-700 text-forest-800' : 'text-slate-500 hover:text-slate-700' }}">
                    F. Electrónicas
                </button>
                <button wire:click="$set('activeTab','cartera')" class="px-4 py-2 text-sm font-medium transition {{ $activeTab === 'cartera' ? 'border-b-2 border-red-600 text-red-700' : 'text-slate-500 hover:text-slate-700' }}">
                    Cartera vencida
                </button>
            </div>

            {{-- ── Panel financiero ──────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6" wire:poll.10s>
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-5 py-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Por cobrar</p>
                    <p class="text-xl font-bold font-mono {{ $porCobrar > 0 ? 'text-blue-700' : 'text-slate-400' }} truncate mt-1">
                        $ {{ number_format($porCobrar, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">cuenta 1305</p>
                </div>
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-5 py-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Por pagar</p>
                    <p class="text-xl font-bold font-mono {{ $porPagar > 0 ? 'text-red-600' : 'text-slate-400' }} truncate mt-1">
                        $ {{ number_format($porPagar, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">cuenta 2205</p>
                </div>
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-5 py-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Facturado este mes</p>
                    <p class="text-xl font-bold font-mono text-slate-800 truncate mt-1">
                        $ {{ number_format($facturadoMes, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">facturas emitidas</p>
                </div>
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-5 py-4">
                    <p class="text-xs text-slate-400 uppercase tracking-wide font-medium">Cobrado este mes</p>
                    <p class="text-xl font-bold font-mono {{ $cobradoMes > 0 ? 'text-green-700' : 'text-slate-400' }} truncate mt-1">
                        $ {{ number_format($cobradoMes, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-0.5">recibos de caja</p>
                </div>
            </div>

            {{-- Nota pedagógica --}}
            <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 text-sm text-sky-800 mb-6">
                <p class="font-semibold mb-1">¿Qué es una factura de venta y cómo impacta la contabilidad?</p>
                <p>Al confirmar una factura, el sistema genera el asiento automáticamente: <strong>Débito 1305 Cuentas por cobrar</strong> (aumenta la cartera del cliente) y <strong>Crédito 4135 Ingresos por ventas</strong>. Si el producto tiene inventario, también registra el costo: Débito 6135 / Crédito 1435. Los <strong>recibos de caja</strong> saldan esa cartera cuando el cliente paga. En Colombia las facturas deben cumplir los requisitos del Estatuto Tributario y la resolución DIAN.</p>
            </div>

            {{-- ══════════════════════════════════ MODAL FACTURA DE COMPRA ══════════════════════════════════ --}}
            @if($showPiForm && !session('audit_mode'))
                <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl my-8">
                        <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
                            <h3 class="font-semibold text-slate-800">{{ $piEditingId ? 'Editar factura de compra' : 'Nueva factura de compra' }}</h3>
                            <button wire:click="resetPiForm" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-5">

                            {{-- Cabecera --}}
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Proveedor *</label>
                                    <select wire:model="pi_third_id" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                        <option value="0">— Seleccionar proveedor —</option>
                                        @foreach($proveedores as $p)
                                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->document }})</option>
                                        @endforeach
                                    </select>
                                    @error('pi_third_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nro. factura proveedor *</label>
                                    <input wire:model="pi_numero" type="text" placeholder="ej: FV-2026-001"
                                        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                    @error('pi_numero') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha factura *</label>
                                    <input wire:model="pi_date" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Vencimiento</label>
                                    <input wire:model="pi_due_date" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                </div>
                            </div>

                            {{-- Ítems --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Ítems</p>
                                    <button wire:click="addPiLine" type="button" class="text-xs text-forest-600 hover:text-forest-800 font-medium transition">+ Agregar ítem</button>
                                </div>
                                @error('pi_lines') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror
                                <div class="border border-cream-200 rounded-xl overflow-x-auto">
                                    <table class="w-full text-sm min-w-[720px]">
                                        <thead class="bg-slate-50 border-b border-cream-200">
                                            <tr>
                                                <th class="text-left px-3 py-2 text-xs font-semibold text-slate-500">Descripción</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-20">Cant.</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-28">Costo unit.</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-16">IVA%</th>
                                                <th class="text-left px-3 py-2 text-xs font-semibold text-slate-500 w-44">Cta. gasto/costo</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-28">Total</th>
                                                <th class="px-3 py-2 w-8"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse($pi_lines as $i => $pil)
                                                @php
                                                    $piSub   = ($pil['unit_cost'] ?? 0) * ($pil['qty'] ?? 1);
                                                    $piTax   = $piSub * (($pil['tax_rate'] ?? 19) / 100);
                                                    $piTotal = $piSub + $piTax;
                                                @endphp
                                                <tr wire:key="pil-{{ $i }}">
                                                    <td class="px-3 py-2">
                                                        <input wire:model="pi_lines.{{ $i }}.description" type="text"
                                                            class="block w-full rounded border-cream-200 text-xs focus:ring-forest-500 focus:border-forest-500" />
                                                        @error("pi_lines.{$i}.description") <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model.live="pi_lines.{{ $i }}.qty" type="number" step="0.01" min="0.01"
                                                            class="block w-full rounded border-cream-200 text-xs text-right focus:ring-forest-500 focus:border-forest-500" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model.live="pi_lines.{{ $i }}.unit_cost" type="number" step="0.01" min="0"
                                                            class="block w-full rounded border-cream-200 text-xs text-right focus:ring-forest-500 focus:border-forest-500" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <select wire:model.live="pi_lines.{{ $i }}.tax_rate"
                                                            class="block w-full rounded border-cream-200 text-xs focus:ring-forest-500 focus:border-forest-500">
                                                            <option value="0">0%</option>
                                                            <option value="5">5%</option>
                                                            <option value="19">19%</option>
                                                        </select>
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <select wire:model.live="pi_lines.{{ $i }}.cuenta_gasto_codigo"
                                                            class="block w-full rounded border-cream-200 text-xs focus:ring-forest-500 focus:border-forest-500">
                                                            <option value="">— Cta. gasto —</option>
                                                            @foreach($cuentasGasto as $cg)
                                                                <option value="{{ $cg->code }}">{{ $cg->code }} {{ $cg->name }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error("pi_lines.{$i}.cuenta_gasto_codigo") <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-mono text-xs text-slate-700">
                                                        $ {{ number_format($piTotal, 0, ',', '.') }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <button wire:click="removePiLine({{ $i }})" type="button" class="text-red-400 hover:text-red-600 transition">✕</button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="px-3 py-6 text-center text-slate-400 text-xs">
                                                        Sin ítems. <button wire:click="addPiLine" type="button" class="text-forest-600 hover:underline">Agregar el primero</button>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if(count($pi_lines) > 0)
                                    @php
                                        $piGrandSub = array_sum(array_map(fn($l) => ($l['unit_cost'] ?? 0) * ($l['qty'] ?? 1), $pi_lines));
                                        $piGrandTax = array_sum(array_map(fn($l) => ($l['unit_cost'] ?? 0) * ($l['qty'] ?? 1) * (($l['tax_rate'] ?? 19) / 100), $pi_lines));
                                        $piGrandTotal = $piGrandSub + $piGrandTax;
                                    @endphp
                                    <div class="flex justify-end mt-2">
                                        <div class="text-sm space-y-1 w-64">
                                            <div class="flex justify-between text-slate-600"><span>Subtotal:</span><span class="font-mono">$ {{ number_format($piGrandSub, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between text-slate-600"><span>IVA:</span><span class="font-mono">$ {{ number_format($piGrandTax, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between font-bold text-slate-800 border-t border-cream-200 pt-1"><span>Total:</span><span class="font-mono">$ {{ number_format($piGrandTotal, 0, ',', '.') }}</span></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Observaciones --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Observaciones <span class="text-slate-400">(opcional)</span></label>
                                <textarea wire:model="pi_notes" rows="2"
                                    class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500"></textarea>
                            </div>

                            <div class="bg-blue-50 rounded-xl p-3 border border-blue-100">
                                <p class="text-xs text-blue-700">
                                    <strong>Asiento al confirmar:</strong>
                                    Db Cuenta de gasto/costo + Db 240810 IVA descontable /
                                    Cr 2205 Proveedores + Cr retenciones (si aplican).
                                    Las retenciones se configuran al momento de confirmar.
                                </p>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3 bg-white rounded-b-2xl">
                            <button wire:click="resetPiForm" type="button" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 transition">Cancelar</button>
                            <button wire:click="savePurchaseInvoice" type="button" class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                <span wire:loading.remove wire:target="savePurchaseInvoice">Guardar borrador</span>
                                <span wire:loading wire:target="savePurchaseInvoice">Guardando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ══════════════════════════════════ MODAL FACTURA ══════════════════════════════════ --}}
            @if($showForm && !session('audit_mode'))
                <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl my-8">
                        <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
                            <h3 class="font-semibold text-slate-800">{{ $editingId ? 'Editar factura' : 'Nueva factura de venta' }}</h3>
                            <button wire:click="cancelForm" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-5">
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Cliente</label>
                                    <select wire:model="third_id" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                        <option value="0">— Seleccionar —</option>
                                        @foreach($thirds as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->document }})</option>
                                        @endforeach
                                    </select>
                                    @error('third_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha</label>
                                    <input wire:model="date" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Vencimiento</label>
                                    <input wire:model="due_date" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Líneas</p>
                                    <button wire:click="addLine" type="button" class="text-xs text-forest-600 hover:text-forest-800 font-medium transition">+ Agregar línea</button>
                                </div>
                                @error('lines') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror
                                <div class="border border-cream-200 rounded-xl overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead class="bg-slate-50 border-b border-cream-200">
                                            <tr>
                                                <th class="text-left px-3 py-2 text-xs font-semibold text-slate-500">Producto</th>
                                                <th class="text-left px-3 py-2 text-xs font-semibold text-slate-500">Descripción</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-20">Cant.</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-28">Precio</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-16">Dto%</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-16">IVA%</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-28">Total</th>
                                                <th class="px-3 py-2 w-8"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse($lines as $i => $line)
                                                @php
                                                    $base      = ($line['unit_price'] ?? 0) * ($line['qty'] ?? 1);
                                                    $discount  = $base * (($line['discount_pct'] ?? 0) / 100);
                                                    $subtotal  = $base - $discount;
                                                    $tax       = $subtotal * (($line['tax_rate'] ?? 19) / 100);
                                                    $lineTotal = $subtotal + $tax;
                                                @endphp
                                                <tr wire:key="line-{{ $i }}">
                                                    <td class="px-3 py-2">
                                                        <select wire:model.live="lines.{{ $i }}.product_id" class="block w-full rounded border-cream-200 text-xs focus:ring-forest-500 focus:border-forest-500">
                                                            <option value="">— Libre —</option>
                                                            @foreach($products as $p)
                                                                <option value="{{ $p->id }}">{{ $p->code }} {{ $p->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model="lines.{{ $i }}.description" type="text" class="block w-full rounded border-cream-200 text-xs focus:ring-forest-500 focus:border-forest-500" />
                                                        @error("lines.{$i}.description") <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model.live="lines.{{ $i }}.qty" type="number" step="0.01" min="0.01" class="block w-full rounded border-cream-200 text-xs text-right focus:ring-forest-500 focus:border-forest-500" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model.live="lines.{{ $i }}.unit_price" type="number" step="0.01" min="0" class="block w-full rounded border-cream-200 text-xs text-right focus:ring-forest-500 focus:border-forest-500" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model.live="lines.{{ $i }}.discount_pct" type="number" step="0.01" min="0" max="100" class="block w-full rounded border-cream-200 text-xs text-right focus:ring-forest-500 focus:border-forest-500" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <select wire:model.live="lines.{{ $i }}.tax_rate" class="block w-full rounded border-cream-200 text-xs focus:ring-forest-500 focus:border-forest-500">
                                                            <option value="0">0%</option>
                                                            <option value="5">5%</option>
                                                            <option value="19">19%</option>
                                                        </select>
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-mono text-xs text-slate-700">
                                                        $ {{ number_format($lineTotal, 0, ',', '.') }}
                                                    </td>
                                                    <td class="px-3 py-2 text-center">
                                                        <button wire:click="removeLine({{ $i }})" type="button" class="text-red-400 hover:text-red-600 transition">✕</button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="8" class="px-3 py-6 text-center text-slate-400 text-xs">
                                                        Sin líneas. <button wire:click="addLine" type="button" class="text-forest-600 hover:underline">Agregar la primera</button>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if(count($lines) > 0)
                                    @php
                                        $totalSubtotal = array_sum(array_map(fn($l) => (($l['unit_price'] ?? 0) * ($l['qty'] ?? 1)) * (1 - (($l['discount_pct'] ?? 0) / 100)), $lines));
                                        $totalTax      = array_sum(array_map(fn($l) => (($l['unit_price'] ?? 0) * ($l['qty'] ?? 1)) * (1 - (($l['discount_pct'] ?? 0) / 100)) * (($l['tax_rate'] ?? 19) / 100), $lines));
                                        $grandTotal    = $totalSubtotal + $totalTax;
                                    @endphp
                                    <div class="flex justify-end mt-2">
                                        <div class="text-sm space-y-1 w-64">
                                            <div class="flex justify-between text-slate-600"><span>Subtotal:</span><span class="font-mono">$ {{ number_format($totalSubtotal, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between text-slate-600"><span>IVA:</span><span class="font-mono">$ {{ number_format($totalTax, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between font-bold text-slate-800 border-t border-cream-200 pt-1"><span>Total:</span><span class="font-mono">$ {{ number_format($grandTotal, 0, ',', '.') }}</span></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Notas <span class="text-slate-400">(opcional)</span></label>
                                <textarea wire:model="notes" rows="2" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500"></textarea>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3 bg-white rounded-b-2xl">
                            <button wire:click="cancelForm" type="button" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 transition">Cancelar</button>
                            <button wire:click="save" type="button" class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                <span wire:loading.remove wire:target="save">Guardar borrador</span>
                                <span wire:loading wire:target="save">Guardando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ══════════════════════════════════ MODAL RECIBO DE CAJA ══════════════════════════════════ --}}
            @if($showReceiptForm && !session('audit_mode'))
                <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                        <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                            <h3 class="font-semibold text-slate-800">Registrar recibo de caja</h3>
                            <button wire:click="$set('showReceiptForm',false)" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div class="p-3 bg-slate-50 rounded-xl text-sm text-slate-600">
                                <span class="font-medium text-slate-800">Factura:</span> {{ $receipt_ref }}
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de cobro</label>
                                <input wire:model="receipt_date" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                @error('receipt_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Monto a cobrar</label>
                                <input wire:model="receipt_amount" type="number" step="0.01" min="0.01" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                @error('receipt_amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Medio de pago</label>
                                <select wire:model.live="receipt_medio_pago" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                    <option value="efectivo">Efectivo</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="transferencia">Transferencia bancaria</option>
                                    <option value="consignacion">Consignación</option>
                                    <option value="tarjeta_debito">Tarjeta débito</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Notas <span class="text-slate-400">(opcional)</span></label>
                                <input wire:model="receipt_notes" type="text" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            </div>
                            <p class="text-xs text-slate-500">
                                Asiento: {{ $receipt_medio_pago === 'efectivo' ? 'DR 1105 Caja' : 'DR 1110 Bancos' }} / CR 1305 Cuentas por cobrar
                            </p>
                        </div>
                        <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                            <button wire:click="$set('showReceiptForm',false)" class="px-4 py-2 text-sm text-slate-600">Cancelar</button>
                            <button wire:click="saveReceipt" class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                <span wire:loading.remove wire:target="saveReceipt">Registrar cobro</span>
                                <span wire:loading wire:target="saveReceipt">Procesando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ══════════════════════════════════ MODAL NOTA DE CRÉDITO ══════════════════════════════════ --}}
            @if($showCreditNoteForm && !session('audit_mode'))
                <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl my-8">
                        <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
                            <h3 class="font-semibold text-slate-800">Nueva nota de crédito</h3>
                            <button wire:click="$set('showCreditNoteForm',false)" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div class="p-3 bg-slate-50 rounded-xl text-sm text-slate-600">
                                <span class="font-medium text-slate-800">Factura origen:</span> {{ $cn_invoice_ref }}
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha</label>
                                    <input wire:model="cn_date" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                    @error('cn_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Razón de la nota</label>
                                    <input wire:model="cn_reason" type="text" placeholder="ej: Devolución de mercancía" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                    @error('cn_reason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>

                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">
                                    Líneas a devolver <span class="font-normal normal-case text-slate-400">(ajusta la cantidad; pon 0 para excluir)</span>
                                </p>
                                @error('cn_lines') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror
                                <div class="border border-cream-200 rounded-xl overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead class="bg-slate-50 border-b border-cream-200">
                                            <tr>
                                                <th class="text-left px-3 py-2 text-xs font-semibold text-slate-500">Descripción</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-28">Precio unit.</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-16">IVA%</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-24">Cant. orig.</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-24">Cant. NC</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-28">Total NC</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($cn_lines as $i => $cnl)
                                                @php
                                                    $cnSub   = ($cnl['unit_price'] ?? 0) * ($cnl['qty'] ?? 0);
                                                    $cnTax   = $cnSub * (($cnl['tax_rate'] ?? 0) / 100);
                                                    $cnTotal = $cnSub + $cnTax;
                                                @endphp
                                                <tr wire:key="cnl-{{ $i }}">
                                                    <td class="px-3 py-2 text-slate-700 text-xs">{{ $cnl['description'] }}</td>
                                                    <td class="px-3 py-2 text-right font-mono text-xs text-slate-600">$ {{ number_format($cnl['unit_price'] ?? 0, 0, ',', '.') }}</td>
                                                    <td class="px-3 py-2 text-right text-xs text-slate-500">{{ $cnl['tax_rate'] ?? 0 }}%</td>
                                                    <td class="px-3 py-2 text-right text-xs text-slate-400">{{ number_format($cnl['max_qty'] ?? 0, 2) }}</td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model.live="cn_lines.{{ $i }}.qty" type="number" step="0.01" min="0" max="{{ $cnl['max_qty'] ?? 0 }}"
                                                            class="block w-full rounded border-cream-200 text-xs text-right focus:ring-forest-500 focus:border-forest-500" />
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-mono text-xs font-semibold {{ $cnTotal > 0 ? 'text-slate-800' : 'text-slate-300' }}">
                                                        $ {{ number_format($cnTotal, 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @php
                                    $cnGrandSub   = array_sum(array_map(fn($l) => ($l['unit_price']??0)*($l['qty']??0), $cn_lines));
                                    $cnGrandTax   = array_sum(array_map(fn($l) => ($l['unit_price']??0)*($l['qty']??0)*(($l['tax_rate']??0)/100), $cn_lines));
                                    $cnGrandTotal = $cnGrandSub + $cnGrandTax;
                                @endphp
                                @if($cnGrandTotal > 0)
                                    <div class="flex justify-end mt-2">
                                        <div class="text-sm space-y-1 w-64">
                                            <div class="flex justify-between text-slate-600"><span>Subtotal NC:</span><span class="font-mono">$ {{ number_format($cnGrandSub, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between text-slate-600"><span>IVA NC:</span><span class="font-mono">$ {{ number_format($cnGrandTax, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between font-bold text-slate-800 border-t border-cream-200 pt-1"><span>Total NC:</span><span class="font-mono">$ {{ number_format($cnGrandTotal, 0, ',', '.') }}</span></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <p class="text-xs text-slate-500">
                                Se generará el asiento: Débito 4135 Ingresos + Débito 2408 IVA / Crédito 1305 Cuentas por cobrar
                            </p>
                        </div>
                        <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3 bg-white rounded-b-2xl">
                            <button wire:click="$set('showCreditNoteForm',false)" class="px-4 py-2 text-sm text-slate-600">Cancelar</button>
                            <button wire:click="saveCreditNote" class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                <span wire:loading.remove wire:target="saveCreditNote">Aplicar nota de crédito</span>
                                <span wire:loading wire:target="saveCreditNote">Procesando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ══════════════════════════════════ TAB: FACTURAS ══════════════════════════════════ --}}
            @if($activeTab === 'facturas')
                <div class="mb-5 flex flex-wrap gap-3">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar cliente..."
                        class="w-64 rounded-xl border-cream-200 text-sm shadow-sm focus:ring-forest-500 focus:border-forest-500" />
                    <select wire:model.live="filterStatus" class="rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                        <option value="">Todos los estados</option>
                        @foreach($statuses as $s)
                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-forest-950 border-b border-forest-800">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Nro.</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Fecha</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide hidden md:table-cell">Vence</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Cliente</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Total</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Saldo</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Estado</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($invoices as $invoice)
                                <tr wire:key="inv-{{ $invoice->id }}" class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-3 font-mono text-xs font-bold text-slate-700">{{ $invoice->fullReference() }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $invoice->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 hidden md:table-cell">
                                        @if($invoice->due_date)
                                            @php $vencida = $invoice->isEmitida() && $invoice->balance() > 0 && $invoice->due_date->isPast(); @endphp
                                            <span class="{{ $vencida ? 'text-red-600 font-semibold' : 'text-slate-500' }}">
                                                {{ $invoice->due_date->format('d/m/Y') }}
                                            </span>
                                            @if($vencida)
                                                <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-700 text-xs rounded font-medium">Vencida</span>
                                            @endif
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-slate-700">{{ $invoice->third?->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm font-semibold text-slate-800">$ {{ number_format($invoice->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm {{ $invoice->isEmitida() && $invoice->balance() > 0 ? 'text-red-600 font-semibold' : 'text-slate-400' }}">
                                        @if($invoice->isEmitida())$ {{ number_format($invoice->balance(), 0, ',', '.') }}@else—@endif
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $invoice->status->color() }}">
                                            {{ $invoice->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if(!session('audit_mode') && !session('reference_mode'))
                                            <div class="flex items-center justify-end gap-3">
                                                @if($invoice->isBorrador())
                                                    <button wire:click="openEdit({{ $invoice->id }})" class="text-xs text-forest-600 hover:text-forest-800 font-medium">Editar</button>
                                                    <button x-on:click="confirmAction('¿Confirmar esta factura? Se generará el asiento contable.', () => $wire.confirm({{ $invoice->id }}), {confirmText: 'Sí, confirmar'})" class="text-xs text-gold-600 hover:text-gold-800 font-medium">Confirmar</button>
                                                    <button x-on:click="confirmAction('¿Eliminar este borrador?', () => $wire.delete({{ $invoice->id }}), {danger: true, confirmText: 'Sí, eliminar'})" class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                                @elseif($invoice->isEmitida())
                                                    @if($invoice->balance() > 0)
                                                        <button wire:click="openReceipt({{ $invoice->id }})" class="text-xs text-gold-600 hover:text-gold-800 font-medium">Cobrar</button>
                                                        <button wire:click="openCreditNote({{ $invoice->id }})" class="text-xs text-orange-600 hover:text-orange-800 font-medium">Nota crédito</button>
                                                        <button wire:click="openDebitNote({{ $invoice->id }})" class="text-xs text-rose-600 hover:text-rose-800 font-medium">Nota débito</button>
                                                    @endif
                                                    <button x-on:click="confirmAction('¿Anular esta factura? Se generará un asiento de reverso.', () => $wire.annul({{ $invoice->id }}), {danger: true, confirmText: 'Sí, anular'})" class="text-xs text-red-500 hover:text-red-700 font-medium">Anular</button>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                        No hay facturas registradas.
                                        <button wire:click="openCreate" class="ml-2 text-forest-600 hover:underline">Crear la primera</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($invoices->hasPages())
                        <div class="px-6 py-4 border-t border-cream-100">{{ $invoices->links() }}</div>
                    @endif
                </div>
            @endif

            {{-- ══════════════════════════════════ TAB: FACTURAS DE COMPRA ══════════════════════════════════ --}}
            @if($activeTab === 'compras')
                <div class="mb-5 flex flex-wrap gap-3">
                    <input wire:model.live.debounce.300ms="pi_search" type="text" placeholder="Buscar proveedor o nro. factura..."
                        class="w-72 rounded-xl border-cream-200 text-sm shadow-sm focus:ring-forest-500 focus:border-forest-500" />
                    <select wire:model.live="pi_filterStatus" class="rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                        <option value="">Todos los estados</option>
                        @foreach($piStatuses as $s)
                            <option value="{{ $s->value }}">{{ $s->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-forest-950 border-b border-forest-800">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Nro. proveedor</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Fecha</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide hidden md:table-cell">Vence</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Proveedor</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Subtotal</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">IVA</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Total</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Estado</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($purchaseInvoices as $pi)
                                <tr wire:key="pi-{{ $pi->id }}" class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-3 font-mono text-xs font-bold text-slate-700">{{ $pi->supplier_invoice_number }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $pi->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 hidden md:table-cell">
                                        @if($pi->due_date)
                                            @php $piVencida = $pi->status->value === 'pendiente' && $pi->balance() > 0 && $pi->due_date->isPast(); @endphp
                                            <span class="{{ $piVencida ? 'text-red-600 font-semibold' : 'text-slate-500' }}">
                                                {{ $pi->due_date->format('d/m/Y') }}
                                            </span>
                                            @if($piVencida)
                                                <span class="ml-1 px-1.5 py-0.5 bg-red-100 text-red-700 text-xs rounded font-medium">Vencida</span>
                                            @endif
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-slate-700">{{ $pi->third?->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm text-slate-700">$ {{ number_format($pi->subtotal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm text-slate-600">$ {{ number_format($pi->tax_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm font-semibold text-slate-800">$ {{ number_format($pi->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $pi->status->color() }}">
                                            {{ $pi->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if(!session('audit_mode') && !session('reference_mode'))
                                            <div class="flex items-center justify-end gap-3">
                                                @if($pi->status->value === 'borrador')
                                                    <button wire:click="openPiEdit({{ $pi->id }})" class="text-xs text-forest-600 hover:text-forest-800 font-medium">Editar</button>
                                                    {{-- Modal inline de retenciones al confirmar --}}
                                                    <button
                                                        x-data="{
                                                            open: false,
                                                            retefte: false, reteftePct: 3.5,
                                                            reteiva: false, reteica: false,
                                                            confirm() {
                                                                $wire.set('pi_aplica_retefte', this.retefte);
                                                                $wire.set('pi_retefte_pct', this.reteftePct);
                                                                $wire.set('pi_aplica_reteiva', this.reteiva);
                                                                $wire.set('pi_aplica_reteica', this.reteica);
                                                                $wire.confirmPurchaseInvoice({{ $pi->id }});
                                                                this.open = false;
                                                            }
                                                        }"
                                                        x-on:click="open = true"
                                                        class="text-xs text-gold-600 hover:text-gold-800 font-medium">Confirmar</button>
                                                    {{-- Retenciones popup --}}
                                                    <template x-teleport="body">
                                                        <div x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                                                            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 space-y-4" @click.stop>
                                                                <h4 class="font-semibold text-slate-800">Confirmar factura de compra</h4>
                                                                <p class="text-xs text-slate-500">Selecciona las retenciones que aplican al proveedor:</p>
                                                                <div class="space-y-3">
                                                                    <label class="flex items-center gap-3 cursor-pointer">
                                                                        <input type="checkbox" x-model="retefte" class="rounded text-forest-600" />
                                                                        <span class="text-sm text-slate-700">Retención en la fuente</span>
                                                                    </label>
                                                                    <div x-show="retefte" class="ml-6">
                                                                        <label class="block text-xs text-slate-500 mb-1">Tarifa (%)</label>
                                                                        <input type="number" x-model="reteftePct" step="0.1" min="0" max="100"
                                                                            class="w-28 rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                                                    </div>
                                                                    <label class="flex items-center gap-3 cursor-pointer">
                                                                        <input type="checkbox" x-model="reteiva" class="rounded text-forest-600" />
                                                                        <span class="text-sm text-slate-700">Reteiva (15% del IVA)</span>
                                                                    </label>
                                                                    <label class="flex items-center gap-3 {{ $esResponsableIva ? 'cursor-pointer' : 'cursor-not-allowed opacity-50' }}">
                                                                        <input type="checkbox" x-model="reteica" @if(!$esResponsableIva) disabled @endif class="rounded text-forest-600 disabled:cursor-not-allowed" />
                                                                        <span class="text-sm text-slate-700">Reteica (0.4‰ del subtotal)</span>
                                                                    </label>
                                                                </div>
                                                                <div class="bg-blue-50 rounded-xl p-3 text-xs text-blue-700">
                                                                    Se generará el asiento contable automáticamente al confirmar.
                                                                </div>
                                                                <div class="flex justify-end gap-3 pt-2">
                                                                    <button @click="open = false" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancelar</button>
                                                                    <button @click="confirm()" class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                                                        Confirmar y contabilizar
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <button
                                                        x-on:click="confirmAction('¿Eliminar este borrador?', () => $wire.deletePurchaseInvoice({{ $pi->id }}), {danger: true, confirmText: 'Sí, eliminar'})"
                                                        class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                                @elseif($pi->status->value === 'pendiente')
                                                    <button
                                                        x-on:click="confirmAction('¿Anular esta factura? Se generará un asiento de reverso.', () => $wire.annulPurchaseInvoice({{ $pi->id }}), {danger: true, confirmText: 'Sí, anular'})"
                                                        class="text-xs text-red-500 hover:text-red-700 font-medium">Anular</button>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center text-slate-400">
                                        No hay facturas de compra directas.
                                        <button wire:click="openPiCreate" class="ml-2 text-forest-600 hover:underline">Registrar la primera</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($purchaseInvoices->hasPages())
                        <div class="px-6 py-4 border-t border-cream-100">{{ $purchaseInvoices->links() }}</div>
                    @endif
                </div>
            @endif

            {{-- ══════════════════════════════════ TAB: RECIBOS DE CAJA ══════════════════════════════════ --}}
            @if($activeTab === 'recibos')
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-forest-950 border-b border-forest-800">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Referencia</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Fecha</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Cliente</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Monto</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Estado</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($receipts as $rc)
                                <tr wire:key="rc-{{ $rc->id }}" class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-mono text-xs font-bold text-slate-700">RC-{{ str_pad($rc->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $rc->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 text-slate-700">{{ $rc->third?->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm font-semibold text-slate-800">$ {{ number_format($rc->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-gold-50 text-gold-700">{{ $rc->status->label() }}</span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        <a href="{{ route(request()->is('aprendizaje/*') ? 'sandbox.recibo.pdf' : (session('audit_mode') ? 'teacher.auditoria.recibo.pdf' : (session('demo_mode') ? 'teacher.demo.recibo.pdf' : 'student.recibo.pdf')), ['id' => $rc->id]) }}"
                                           target="_blank"
                                           class="text-xs text-forest-600 hover:text-forest-800 font-semibold px-2 py-1 rounded-lg hover:bg-forest-50 transition">
                                            PDF
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                        No hay recibos de caja. Usa "Cobrar" en una factura emitida o en F. Electrónicas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ══════════════════════════════════ TAB: F. ELECTRÓNICAS ══════════════════════════════════ --}}
            @if($activeTab === 'fe')
                {{-- Panel resumen IVA --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    {{-- IVA ventas --}}
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-5 py-4">
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">IVA cobrado (ventas)</p>
                        <p class="text-xl font-bold text-slate-800 font-mono">$ {{ number_format($totalIvaVentas, 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-400 mt-1">Facturas emitidas + F. Electrónica</p>
                    </div>

                    {{-- IVA compras --}}
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-5 py-4">
                        <p class="text-xs font-medium text-slate-500 uppercase tracking-wide mb-1">IVA pagado (compras)</p>
                        <p class="text-xl font-bold text-slate-800 font-mono">$ {{ number_format($totalIvaCompras, 0, ',', '.') }}</p>
                        <p class="text-xs text-slate-400 mt-1">IVA descontable de facturas de compra</p>
                    </div>

                    {{-- Saldo IVA --}}
                    @if($diferenciaIva > 0)
                        <div class="bg-red-50 rounded-2xl border border-red-200 shadow-card px-5 py-4">
                            <p class="text-xs font-medium text-red-600 uppercase tracking-wide mb-1">Saldo a pagar a la DIAN</p>
                            <p class="text-xl font-bold text-red-700 font-mono">$ {{ number_format($diferenciaIva, 0, ',', '.') }}</p>
                            <p class="text-xs text-red-400 mt-1">IVA ventas &minus; IVA compras</p>
                            @if(!session('audit_mode') && !session('reference_mode'))
                                <button wire:click="openPagoIvaModal"
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

                <div class="mb-4">
                    <input wire:model.live.debounce.300ms="fe_search" type="text" placeholder="Buscar por número o adquirente…"
                        class="w-full sm:w-80 pl-3 rounded-xl border-cream-200 text-sm shadow-sm focus:ring-forest-500 focus:border-forest-500" />
                </div>
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-forest-950 border-b border-forest-800">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">N° Factura</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Fecha</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Adquirente</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Total</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Saldo</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Estado</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @forelse($feFacturas as $fe)
                                @php $saldo = $fe->balance(); @endphp
                                <tr wire:key="fe-{{ $fe->id }}" class="hover:bg-cream-50">
                                    <td class="px-6 py-3 font-mono text-xs font-bold text-slate-700">{{ $fe->numero_completo ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $fe->fecha_emision?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-700">{{ $fe->nombre_adquirente ?? $fe->cliente?->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm font-semibold text-slate-800">$ {{ number_format($fe->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm font-semibold {{ $saldo > 0 ? 'text-blue-700' : 'text-slate-400' }}">
                                        $ {{ number_format($saldo, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded-lg text-xs font-medium {{ $fe->estado->badgeClasses() }}">
                                            {{ $fe->estado->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        @if($fe->esCobrable() && !session('audit_mode') && !session('reference_mode'))
                                            <button wire:click="openReceiptFe({{ $fe->id }})"
                                                class="text-xs text-forest-600 hover:text-forest-800 font-semibold px-2 py-1 rounded-lg hover:bg-forest-50 transition">
                                                Cobrar
                                            </button>
                                        @elseif($saldo <= 0)
                                            <span class="text-xs text-slate-400">Pagada</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                        No hay facturas electrónicas emitidas. Créalas en el módulo <strong>F. Electrónica</strong>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Modal pago IVA DIAN --}}
                @if($showPagoIvaModal)
                    <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center">
                        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4 p-6" @click.stop>
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="text-base font-bold text-slate-800">Pago IVA a la DIAN</h3>
                                <button wire:click="$set('showPagoIvaModal', false)" class="text-slate-400 hover:text-slate-600">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <div class="bg-red-50 border border-red-200 rounded-xl px-4 py-3 mb-5">
                                <p class="text-xs text-red-600 font-medium mb-0.5">Monto a pagar a la DIAN</p>
                                <p class="text-2xl font-bold text-red-700 font-mono">$ {{ number_format($diferenciaIva, 0, ',', '.') }}</p>
                                <p class="text-xs text-red-400 mt-1">Se generará: DR 2408 IVA por pagar / CR 1110 Bancos</p>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Fecha del pago</label>
                                    <input wire:model="pagoIva_fecha" type="date"
                                        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                    @error('pagoIva_fecha') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Cuenta bancaria</label>
                                    <select wire:model="pagoIva_bankAccountId"
                                        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                        <option value="">Selecciona una cuenta…</option>
                                        @foreach($cuentasBancarias as $cuenta)
                                            <option value="{{ $cuenta->id }}">
                                                {{ ucfirst($cuenta->bank) }} — {{ $cuenta->account_number }}
                                                (Saldo: $ {{ number_format($cuenta->saldo, 0, ',', '.') }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('pagoIva_bankAccountId') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="bg-amber-50 border border-amber-200 rounded-xl px-4 py-3 text-xs text-amber-800">
                                    Se aplicará el GMF (4×1000) sobre el monto pagado. El saldo bancario se reducirá en el total más el impuesto.
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 mt-6">
                                <button wire:click="$set('showPagoIvaModal', false)"
                                    class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancelar</button>
                                <button wire:click="pagarIvaDian" wire:loading.attr="disabled"
                                    class="px-5 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="pagarIvaDian">Confirmar pago</span>
                                    <span wire:loading wire:target="pagarIvaDian">Registrando…</span>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- ══════════════════════════════════ TAB: NOTAS DE CRÉDITO ══════════════════════════════════ --}}
            @if($activeTab === 'notas')
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-forest-950 border-b border-forest-800">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Referencia</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Fecha</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Factura origen</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Cliente</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Razón</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Total NC</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($creditNotes as $cn)
                                <tr wire:key="cn-{{ $cn->id }}" class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-mono text-xs font-bold text-slate-700">{{ $cn->fullReference() }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $cn->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 font-mono text-xs text-slate-600">{{ $cn->invoice->fullReference() }}</td>
                                    <td class="px-6 py-3 text-slate-700">{{ $cn->invoice?->third?->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-600 text-xs">{{ $cn->reason }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm font-semibold text-orange-700">$ {{ number_format($cn->total, 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                        No hay notas de crédito. Ve a <button wire:click="$set('activeTab','facturas')" class="text-forest-600 hover:underline">Facturas</button> y usa "Nota crédito" en una factura emitida.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ══════════════════════════════════ TAB: NOTAS DÉBITO ══════════════════════════════════ --}}
            @if($activeTab === 'notas_debito')
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-forest-950 border-b border-forest-800">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Referencia</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Fecha</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Factura origen</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Cliente</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Razón</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Subtotal</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">IVA</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Total ND</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Estado</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($debitNotes as $dn)
                                <tr wire:key="dn-{{ $dn->id }}" class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-mono text-xs font-bold text-slate-700">{{ $dn->fullReference() }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $dn->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 font-mono text-xs text-slate-600">{{ $dn->invoice->fullReference() }}</td>
                                    <td class="px-6 py-3 text-slate-700">{{ $dn->invoice?->third?->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-600 text-xs">{{ $dn->reason }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm text-slate-700">$ {{ number_format($dn->subtotal, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm text-slate-700">$ {{ number_format($dn->tax_amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm font-semibold text-rose-700">$ {{ number_format($dn->amount, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3">
                                        @php $color = $dn->status->color(); @endphp
                                        <span class="px-2 py-0.5 rounded text-xs font-medium bg-{{ $color }}-50 text-{{ $color }}-700">
                                            {{ $dn->status->label() }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3">
                                        <div class="flex items-center gap-2">
                                            @if($dn->isBorrador())
                                                <button wire:click="confirmDebitNote({{ $dn->id }})"
                                                    wire:confirm="¿Confirmar la nota débito? Se generará el asiento contable."
                                                    class="text-xs px-2 py-1 rounded bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
                                                    Confirmar
                                                </button>
                                            @endif
                                            @if($dn->isEmitida())
                                                <button wire:click="annulDebitNote({{ $dn->id }})"
                                                    wire:confirm="¿Anular esta nota débito? Se generará un asiento de reverso."
                                                    class="text-xs px-2 py-1 rounded bg-red-50 text-red-700 hover:bg-red-100 transition">
                                                    Anular
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-6 py-10 text-center text-slate-400">
                                        No hay notas débito. Ve a <button wire:click="$set('activeTab','facturas')" class="text-forest-600 hover:underline">Facturas</button> y usa "Nota débito" en una factura emitida.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ══════════ CARTERA VENCIDA ══════════ --}}
            @if($activeTab === 'cartera')
                @php
                    $buckets = [
                        'al_dia'   => ['label' => 'Al día',        'color' => 'green',  'items' => $carteraCxC->where('dias_mora', 0)],
                        '1_30'     => ['label' => '1–30 días',     'color' => 'yellow', 'items' => $carteraCxC->whereBetween('dias_mora', [1, 30])],
                        '31_60'    => ['label' => '31–60 días',    'color' => 'orange', 'items' => $carteraCxC->whereBetween('dias_mora', [31, 60])],
                        '61_90'    => ['label' => '61–90 días',    'color' => 'red',    'items' => $carteraCxC->whereBetween('dias_mora', [61, 90])],
                        'mas_90'   => ['label' => '+90 días',      'color' => 'rose',   'items' => $carteraCxC->where('dias_mora', '>', 90)],
                    ];
                    $bucketsCxP = [
                        'al_dia'   => ['label' => 'Al día',        'color' => 'green',  'items' => $carteraCxP->where('dias_mora', 0)],
                        '1_30'     => ['label' => '1–30 días',     'color' => 'yellow', 'items' => $carteraCxP->whereBetween('dias_mora', [1, 30])],
                        '31_60'    => ['label' => '31–60 días',    'color' => 'orange', 'items' => $carteraCxP->whereBetween('dias_mora', [31, 60])],
                        '61_90'    => ['label' => '61–90 días',    'color' => 'red',    'items' => $carteraCxP->whereBetween('dias_mora', [61, 90])],
                        'mas_90'   => ['label' => '+90 días',      'color' => 'rose',   'items' => $carteraCxP->where('dias_mora', '>', 90)],
                    ];
                    $colorMap = ['green'=>'bg-green-100 text-green-800','yellow'=>'bg-yellow-100 text-yellow-800','orange'=>'bg-orange-100 text-orange-800','red'=>'bg-red-100 text-red-800','rose'=>'bg-rose-100 text-rose-800'];
                @endphp

                <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 text-sm text-sky-800 mb-6">
                    <p class="font-semibold mb-1">¿Qué es la cartera vencida?</p>
                    <p>La cartera vencida son las facturas que ya pasaron su fecha de vencimiento y el cliente aún no ha pagado. En contabilidad colombiana, el contador debe provisionar la cartera de difícil cobro (cuenta 1399) cuando supera 90 días. Vigilar este indicador es clave para el flujo de caja.</p>
                </div>

                {{-- Resumen CxC --}}
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Cuentas por cobrar — Cartera de clientes</h3>
                @if($carteraCxC->isEmpty())
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-6 py-10 text-center text-slate-400 mb-6">No hay facturas pendientes de cobro.</div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
                        @foreach($buckets as $bucket)
                            <div class="bg-white rounded-xl border border-cream-200 shadow-sm p-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $colorMap[$bucket['color']] }} mb-1">{{ $bucket['label'] }}</span>
                                <p class="text-sm font-bold font-mono text-slate-800">$ {{ number_format($bucket['items']->sum(fn($i) => $i->balance()), 0, ',', '.') }}</p>
                                <p class="text-xs text-slate-400">{{ $bucket['items']->count() }} factura(s)</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden mb-8">
                        <table class="w-full text-sm">
                            <thead><tr class="bg-forest-950 border-b border-forest-800">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase">Nro.</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase">Cliente</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase hidden sm:table-cell">Vence</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-forest-300 uppercase">Saldo</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase">Mora</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($carteraCxC->sortByDesc('dias_mora') as $inv)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-5 py-2.5 font-mono text-xs font-bold text-slate-700">{{ $inv->fullReference() }}</td>
                                        <td class="px-5 py-2.5 text-slate-700">{{ $inv->third?->name ?? '—' }}</td>
                                        <td class="px-5 py-2.5 hidden sm:table-cell text-slate-500 text-xs">{{ $inv->due_date ? $inv->due_date->format('d/m/Y') : '—' }}</td>
                                        <td class="px-5 py-2.5 text-right font-mono text-sm font-semibold {{ $inv->dias_mora > 0 ? 'text-red-600' : 'text-slate-800' }}">$ {{ number_format($inv->balance(), 0, ',', '.') }}</td>
                                        <td class="px-5 py-2.5">
                                            @if($inv->dias_mora === 0)
                                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded">Al día</span>
                                            @else
                                                <span class="px-2 py-0.5 {{ $inv->dias_mora > 90 ? 'bg-rose-100 text-rose-700' : ($inv->dias_mora > 60 ? 'bg-red-100 text-red-700' : ($inv->dias_mora > 30 ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700')) }} text-xs rounded">{{ $inv->dias_mora }} día(s)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Resumen CxP --}}
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Cuentas por pagar — Obligaciones con proveedores</h3>
                @if($carteraCxP->isEmpty())
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card px-6 py-10 text-center text-slate-400">No hay facturas de compra pendientes de pago.</div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-4">
                        @foreach($bucketsCxP as $bucket)
                            <div class="bg-white rounded-xl border border-cream-200 shadow-sm p-3 text-center">
                                <span class="inline-block px-2 py-0.5 rounded text-xs font-medium {{ $colorMap[$bucket['color']] }} mb-1">{{ $bucket['label'] }}</span>
                                <p class="text-sm font-bold font-mono text-slate-800">$ {{ number_format($bucket['items']->sum(fn($i) => $i->balance()), 0, ',', '.') }}</p>
                                <p class="text-xs text-slate-400">{{ $bucket['items']->count() }} factura(s)</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                        <table class="w-full text-sm">
                            <thead><tr class="bg-forest-950 border-b border-forest-800">
                                <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase">Nro. proveedor</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase">Proveedor</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase hidden sm:table-cell">Vence</th>
                                <th class="text-right px-5 py-3 text-xs font-semibold text-forest-300 uppercase">Saldo</th>
                                <th class="text-left px-5 py-3 text-xs font-semibold text-forest-300 uppercase">Mora</th>
                            </tr></thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($carteraCxP->sortByDesc('dias_mora') as $pi)
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-5 py-2.5 font-mono text-xs font-bold text-slate-700">{{ $pi->supplier_invoice_number }}</td>
                                        <td class="px-5 py-2.5 text-slate-700">{{ $pi->third?->name ?? '—' }}</td>
                                        <td class="px-5 py-2.5 hidden sm:table-cell text-slate-500 text-xs">{{ $pi->due_date ? $pi->due_date->format('d/m/Y') : '—' }}</td>
                                        <td class="px-5 py-2.5 text-right font-mono text-sm font-semibold {{ $pi->dias_mora > 0 ? 'text-red-600' : 'text-slate-800' }}">$ {{ number_format($pi->balance(), 0, ',', '.') }}</td>
                                        <td class="px-5 py-2.5">
                                            @if($pi->dias_mora === 0)
                                                <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs rounded">Al día</span>
                                            @else
                                                <span class="px-2 py-0.5 {{ $pi->dias_mora > 90 ? 'bg-rose-100 text-rose-700' : ($pi->dias_mora > 60 ? 'bg-red-100 text-red-700' : ($pi->dias_mora > 30 ? 'bg-orange-100 text-orange-700' : 'bg-yellow-100 text-yellow-700')) }} text-xs rounded">{{ $pi->dias_mora }} día(s)</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif

        </div>
    </div>

    {{-- ══════════════════════════════════ MODAL: NUEVA NOTA DÉBITO ══════════════════════════════════ --}}
    @if($showDebitNoteForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-4 p-8">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-slate-800">Nueva Nota Débito</h2>
                    <button wire:click="$set('showDebitNoteForm', false)" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                </div>

                {{-- Factura de referencia --}}
                <div class="mb-4 p-3 bg-rose-50 rounded-xl border border-rose-100">
                    <p class="text-xs text-rose-600 font-medium uppercase tracking-wide mb-1">Factura origen</p>
                    <p class="text-sm font-semibold text-rose-800">{{ $dn_invoice_ref }}</p>
                </div>

                <div class="space-y-4">
                    {{-- Fecha --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha</label>
                        <input type="date" wire:model="dn_date"
                            class="w-full border border-cream-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-forest-500">
                        @error('dn_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Razón --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Razón / Descripción</label>
                        <textarea wire:model="dn_reason" rows="2"
                            placeholder="Ej: Ajuste por diferencia en precio pactado, intereses de mora..."
                            class="w-full border border-cream-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-forest-500 resize-none"></textarea>
                        @error('dn_reason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Subtotal + IVA --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Subtotal (sin IVA)</label>
                            <input type="number" wire:model="dn_subtotal" min="0.01" step="0.01"
                                placeholder="0.00"
                                class="w-full border border-cream-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-forest-500">
                            @error('dn_subtotal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tarifa IVA</label>
                            <select wire:model="dn_tax_rate"
                                class="w-full border border-cream-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-forest-500">
                                <option value="0">0% — Excluido / Exento</option>
                                <option value="5">5%</option>
                                <option value="19">19% — General</option>
                            </select>
                        </div>
                    </div>

                    {{-- Preview total --}}
                    @php
                        $previewIva   = round((float) $dn_subtotal * ((int) $dn_tax_rate / 100), 2);
                        $previewTotal = (float) $dn_subtotal + $previewIva;
                    @endphp
                    @if($dn_subtotal > 0)
                        <div class="bg-rose-50 rounded-xl p-3 border border-rose-100 text-sm space-y-1">
                            <div class="flex justify-between text-slate-600">
                                <span>Subtotal</span>
                                <span class="font-mono">$ {{ number_format($dn_subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>IVA ({{ $dn_tax_rate }}%)</span>
                                <span class="font-mono">$ {{ number_format($previewIva, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-rose-800 border-t border-rose-200 pt-1 mt-1">
                                <span>Total nota débito</span>
                                <span class="font-mono">$ {{ number_format($previewTotal, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Nota educativa --}}
                    <div class="bg-amber-50 rounded-xl p-3 border border-amber-100">
                        <p class="text-xs text-amber-700">
                            <strong>Nota contable:</strong> Al confirmar la nota débito se genera el asiento:
                            <br>Débito 1305 Cuentas por cobrar — Crédito 4135 Ingresos + Crédito 2408 IVA por pagar.
                            Esto incrementa la deuda del cliente.
                        </p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button wire:click="$set('showDebitNoteForm', false)"
                        class="px-4 py-2 text-sm rounded-xl border border-cream-200 text-slate-600 hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                    <button wire:click="saveDebitNote"
                        class="px-5 py-2 text-sm rounded-xl bg-rose-600 text-white font-semibold hover:bg-rose-700 transition">
                        Guardar borrador
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
