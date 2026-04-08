<div>
    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Operaciones</p>
                <h1 class="font-display text-2xl font-bold text-white">Facturas de venta</h1>
                <p class="text-forest-300 text-sm mt-1">Ciclo de facturación y cobro</p>
            </div>
            @if(!session('audit_mode') && !session('reference_mode') && $activeTab === 'facturas')
                <div>
                    <button wire:click="openCreate" class="px-4 py-2 bg-gold-500 text-white text-sm font-semibold rounded-xl hover:bg-gold-400 transition">
                        + Nueva factura
                    </button>
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
                <button wire:click="$set('activeTab','recibos')" class="px-4 py-2 text-sm font-medium transition {{ $activeTab === 'recibos' ? 'border-b-2 border-forest-700 text-forest-800' : 'text-slate-500 hover:text-slate-700' }}">
                    Recibos de caja
                </button>
                <button wire:click="$set('activeTab','notas')" class="px-4 py-2 text-sm font-medium transition {{ $activeTab === 'notas' ? 'border-b-2 border-forest-700 text-forest-800' : 'text-slate-500 hover:text-slate-700' }}">
                    Notas de crédito
                </button>
                <button wire:click="$set('activeTab','notas_debito')" class="px-4 py-2 text-sm font-medium transition {{ $activeTab === 'notas_debito' ? 'border-b-2 border-forest-700 text-forest-800' : 'text-slate-500 hover:text-slate-700' }}">
                    Notas débito
                </button>
            </div>

            {{-- ══════════════════════════════════ MODAL FACTURA ══════════════════════════════════ --}}
            @if($showForm && !session('audit_mode'))
                <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto" wire:click.self="cancelForm">
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
                <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" wire:click.self="$set('showReceiptForm',false)">
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
                                <label class="block text-sm font-medium text-slate-700 mb-1">Notas <span class="text-slate-400">(opcional)</span></label>
                                <input wire:model="receipt_notes" type="text" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            </div>
                            <p class="text-xs text-slate-500">
                                Se generará el asiento: Débito 1105 Caja / Crédito 1305 Cuentas por cobrar
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
                <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto" wire:click.self="$set('showCreditNoteForm',false)">
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
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                        No hay recibos de caja. Ve a <button wire:click="$set('activeTab','facturas')" class="text-forest-600 hover:underline">Facturas</button> y usa "Cobrar" en una factura emitida.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
