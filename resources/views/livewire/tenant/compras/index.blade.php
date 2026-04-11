<div>
    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Operaciones</p>
                <h1 class="font-display text-2xl font-bold text-white">Compras</h1>
                <p class="text-forest-300 text-sm mt-1">Facturas de proveedores y pagos</p>
            </div>
            @if(!session('audit_mode') && !session('reference_mode'))
                <div class="flex items-center gap-3">
                    @if($view === 'orders')
                        <button wire:click="openCreateOrder" class="px-4 py-2 bg-gold-500 text-white text-sm font-semibold rounded-xl hover:bg-gold-400 transition">+ Nueva orden</button>
                    @elseif($view === 'invoices')
                        <button wire:click="openCreate" class="px-4 py-2 bg-gold-500 text-white text-sm font-semibold rounded-xl hover:bg-gold-400 transition">+ Nueva factura</button>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">

            {{-- Tabs --}}
            <div class="flex gap-1 mb-6 border-b border-cream-200">
                <button wire:click="$set('view','orders')" class="px-4 py-2 text-sm font-medium transition {{ $view === 'orders' ? 'border-b-2 border-forest-700 text-forest-800' : 'text-slate-500 hover:text-slate-700' }}">Órdenes de compra</button>
                <button wire:click="$set('view','invoices')" class="px-4 py-2 text-sm font-medium transition {{ $view === 'invoices' ? 'border-b-2 border-forest-700 text-forest-800' : 'text-slate-500 hover:text-slate-700' }}">Facturas de compra</button>
                <button wire:click="$set('view','payments')" class="px-4 py-2 text-sm font-medium transition {{ $view === 'payments' ? 'border-b-2 border-forest-700 text-forest-800' : 'text-slate-500 hover:text-slate-700' }}">Pagos a proveedores</button>
            </div>

            {{-- ══════════════════════════════════ MODAL ORDEN DE COMPRA ══════════════════════════════════ --}}
            @if($showOrderForm && !session('audit_mode'))
                <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto" wire:click.self="$set('showOrderForm',false)">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl my-8">
                        <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
                            <h3 class="font-semibold text-slate-800">{{ $editingOrderId ? 'Editar orden de compra' : 'Nueva orden de compra' }}</h3>
                            <button wire:click="$set('showOrderForm',false)" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Proveedor</label>
                                    <select wire:model="order_third_id" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                        <option value="0">— Seleccionar —</option>
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('order_third_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha</label>
                                    <input wire:model="order_date" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha entrega esperada</label>
                                    <input wire:model="order_expected_date" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Productos a pedir</p>
                                    <button wire:click="addOrderLine" type="button" class="text-xs text-forest-600 hover:text-forest-800 font-medium">+ Agregar línea</button>
                                </div>
                                @error('order_lines') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror
                                <div class="border border-cream-200 rounded-xl overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead class="bg-slate-50 border-b border-cream-200">
                                            <tr>
                                                <th class="text-left px-3 py-2 text-xs font-semibold text-slate-500">Producto</th>
                                                <th class="text-left px-3 py-2 text-xs font-semibold text-slate-500">Descripción</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-20">Cant.</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-28">Costo unit.</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-28">Total</th>
                                                <th class="px-3 py-2 w-8"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse($order_lines as $i => $ol)
                                                @php $olTotal = ($ol['unit_cost']??0) * ($ol['qty']??1); @endphp
                                                <tr wire:key="ol-{{ $i }}">
                                                    <td class="px-3 py-2">
                                                        <select wire:model.live="order_lines.{{ $i }}.product_id" class="block w-full rounded border-cream-200 text-xs focus:ring-forest-500 focus:border-forest-500">
                                                            <option value="">— Libre —</option>
                                                            @foreach($products as $p)
                                                                <option value="{{ $p->id }}">{{ $p->code }} {{ $p->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model="order_lines.{{ $i }}.description" type="text" class="block w-full rounded border-cream-200 text-xs focus:ring-forest-500 focus:border-forest-500" />
                                                        @error("order_lines.{$i}.description") <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model.live="order_lines.{{ $i }}.qty" type="number" step="0.01" min="0.01" class="block w-full rounded border-cream-200 text-xs text-right focus:ring-forest-500 focus:border-forest-500" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model.live="order_lines.{{ $i }}.unit_cost" type="number" step="0.01" min="0" class="block w-full rounded border-cream-200 text-xs text-right focus:ring-forest-500 focus:border-forest-500" />
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-mono text-xs text-slate-700">$ {{ number_format($olTotal, 0, ',', '.') }}</td>
                                                    <td class="px-3 py-2 text-center">
                                                        <button wire:click="removeOrderLine({{ $i }})" type="button" class="text-red-400 hover:text-red-600">✕</button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="6" class="px-3 py-6 text-center text-slate-400 text-xs">Sin líneas. <button wire:click="addOrderLine" type="button" class="text-forest-600 hover:underline">Agregar</button></td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if(count($order_lines) > 0)
                                    @php $orderTotal = array_sum(array_map(fn($l) => ($l['unit_cost']??0)*($l['qty']??1), $order_lines)); @endphp
                                    <div class="flex justify-end mt-2">
                                        <div class="text-sm w-64 flex justify-between font-bold text-slate-800 border-t border-cream-200 pt-1">
                                            <span>Total estimado:</span>
                                            <span class="font-mono">$ {{ number_format($orderTotal, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Notas <span class="text-slate-400">(opcional)</span></label>
                                <textarea wire:model="order_notes" rows="2" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500"></textarea>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3 bg-white rounded-b-2xl">
                            <button wire:click="$set('showOrderForm',false)" type="button" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancelar</button>
                            <button wire:click="saveOrder" type="button" class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                <span wire:loading.remove wire:target="saveOrder">Guardar orden</span>
                                <span wire:loading wire:target="saveOrder">Guardando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Modal factura de compra --}}
            @if($showForm && !session('audit_mode'))
                <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto" wire:click.self="$set('showForm',false)">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-4xl my-8">
                        <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
                            <h3 class="font-semibold text-slate-800">{{ $editingId ? 'Editar factura de compra' : 'Nueva factura de compra' }}</h3>
                            <button wire:click="$set('showForm',false)" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Proveedor</label>
                                    <select wire:model="third_id" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                        <option value="0">— Seleccionar —</option>
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('third_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha</label>
                                    <input wire:model="date" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nro. factura proveedor</label>
                                    <input wire:model="supplier_invoice_number" type="text" placeholder="ej: FV-001" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                </div>
                            </div>

                            {{-- Líneas --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Líneas</p>
                                    <button wire:click="addLine" type="button" class="text-xs text-forest-600 hover:text-forest-800 font-medium">+ Agregar línea</button>
                                </div>
                                <div class="border border-cream-200 rounded-xl overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead class="bg-slate-50 border-b border-cream-200">
                                            <tr>
                                                <th class="text-left px-3 py-2 text-xs font-semibold text-slate-500">Producto</th>
                                                <th class="text-left px-3 py-2 text-xs font-semibold text-slate-500">Descripción</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-20">Cant.</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-28">Costo unit.</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-16">IVA%</th>
                                                <th class="text-right px-3 py-2 text-xs font-semibold text-slate-500 w-28">Total</th>
                                                <th class="px-3 py-2 w-8"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @forelse($lines as $i => $line)
                                                @php
                                                    $sub   = ($line['unit_cost'] ?? 0) * ($line['qty'] ?? 1);
                                                    $tax   = $sub * (($line['tax_rate'] ?? 19) / 100);
                                                    $total = $sub + $tax;
                                                @endphp
                                                <tr wire:key="cline-{{ $i }}">
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
                                                        <input wire:model.live="lines.{{ $i }}.unit_cost" type="number" step="0.01" min="0" class="block w-full rounded border-cream-200 text-xs text-right focus:ring-forest-500 focus:border-forest-500" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <select wire:model.live="lines.{{ $i }}.tax_rate" class="block w-full rounded border-cream-200 text-xs focus:ring-forest-500 focus:border-forest-500">
                                                            <option value="0">0%</option>
                                                            <option value="5">5%</option>
                                                            <option value="19">19%</option>
                                                        </select>
                                                    </td>
                                                    <td class="px-3 py-2 text-right font-mono text-xs text-slate-700">$ {{ number_format($total, 0, ',', '.') }}</td>
                                                    <td class="px-3 py-2 text-center">
                                                        <button wire:click="removeLine({{ $i }})" type="button" class="text-red-400 hover:text-red-600">✕</button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr><td colspan="7" class="px-3 py-6 text-center text-slate-400 text-xs">Sin líneas. <button wire:click="addLine" type="button" class="text-forest-600 hover:underline">Agregar</button></td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @if(count($lines) > 0)
                                    @php
                                        $ts = array_sum(array_map(fn($l) => ($l['unit_cost']??0)*($l['qty']??1), $lines));
                                        $tt = array_sum(array_map(fn($l) => ($l['unit_cost']??0)*($l['qty']??1)*(($l['tax_rate']??19)/100), $lines));
                                    @endphp
                                    <div class="flex justify-end mt-2">
                                        <div class="text-sm space-y-1 w-64">
                                            <div class="flex justify-between text-slate-600"><span>Subtotal:</span><span class="font-mono">$ {{ number_format($ts, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between text-slate-600"><span>IVA:</span><span class="font-mono">$ {{ number_format($tt, 0, ',', '.') }}</span></div>
                                            <div class="flex justify-between font-bold text-slate-800 border-t border-cream-200 pt-1"><span>Total:</span><span class="font-mono">$ {{ number_format($ts+$tt, 0, ',', '.') }}</span></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3 bg-white rounded-b-2xl">
                            <button wire:click="$set('showForm',false)" type="button" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancelar</button>
                            <button wire:click="save" type="button" class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                <span wire:loading.remove wire:target="save">Guardar borrador</span>
                                <span wire:loading wire:target="save">Guardando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Modal pago --}}
            @if($showPaymentForm && !session('audit_mode'))
                <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" wire:click.self="$set('showPaymentForm',false)">
                    <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
                        <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                            <h3 class="font-semibold text-slate-800">Registrar pago a proveedor</h3>
                            <button wire:click="$set('showPaymentForm',false)" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de pago</label>
                                <input wire:model="payment_date" type="date" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Facturas pendientes</p>
                                @forelse($payment_items as $pi => $item)
                                    <div class="flex items-center gap-3 py-2 border-b border-cream-100" wire:key="pi-{{ $pi }}">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-slate-700">{{ $item['invoice_ref'] }}</p>
                                            <p class="text-xs text-slate-400">Saldo: $ {{ number_format($item['invoice_balance'], 0, ',', '.') }}</p>
                                        </div>
                                        <input wire:model.live="payment_items.{{ $pi }}.amount_applied" type="number" step="0.01" min="0" class="w-32 rounded-xl border-cream-200 text-sm text-right focus:ring-forest-500 focus:border-forest-500" />
                                    </div>
                                @empty
                                    <p class="text-sm text-slate-400">No hay facturas pendientes para este proveedor.</p>
                                @endforelse
                                @php $payTotal = array_sum(array_column($payment_items, 'amount_applied')); @endphp
                                @if(count($payment_items) > 0)
                                    <div class="flex justify-between font-bold text-slate-800 pt-2">
                                        <span>Total a pagar:</span>
                                        <span class="font-mono">$ {{ number_format($payTotal, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Forma de pago: banco o caja --}}
                            @php
                                $cuentasPago = \App\Models\Tenant\BankAccount::where('activa', true)
                                    ->orderByDesc('es_principal')->get();
                            @endphp
                            @if($cuentasPago->isNotEmpty())
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Pagar desde</p>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" wire:click="$set('payment_bank_account_id', null)"
                                        class="flex items-center gap-2 px-3 py-2 rounded-xl border text-sm transition text-left
                                            {{ is_null($payment_bank_account_id) ? 'border-forest-500 bg-forest-50 text-forest-700 font-semibold' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                                        <span class="w-2 h-2 rounded-full bg-slate-400 shrink-0"></span>
                                        <div>
                                            <p class="text-xs font-semibold">Caja (1105)</p>
                                            <p class="text-xs opacity-60">Sin GMF</p>
                                        </div>
                                    </button>
                                    @foreach($cuentasPago as $cta)
                                        @php
                                            $dot = match($cta->bank) { 'bancolombia' => 'bg-blue-500', 'davivienda' => 'bg-red-500', 'banco_bogota' => 'bg-green-600', default => 'bg-slate-400' };
                                            $gmfEst = $payTotal > 0 ? round($payTotal * 0.004) : 0;
                                        @endphp
                                        <button type="button" wire:click="$set('payment_bank_account_id', {{ $cta->id }})"
                                            class="flex items-center gap-2 px-3 py-2 rounded-xl border text-sm transition text-left
                                                {{ $payment_bank_account_id === $cta->id ? 'border-gold-500 bg-gold-50 text-gold-700 font-semibold' : 'border-slate-200 text-slate-600 hover:border-slate-300' }}">
                                            <span class="w-2 h-2 rounded-full {{ $dot }} shrink-0"></span>
                                            <div class="min-w-0">
                                                <p class="text-xs font-semibold truncate">{{ $cta->nombreBanco() }} ***{{ $cta->ultimosDigitos() }}</p>
                                                <p class="text-xs opacity-60">${{ number_format($cta->saldo, 0, ',', '.') }}
                                                    @if($gmfEst > 0) · GMF +${{ number_format($gmfEst, 0, ',', '.') }}@endif
                                                </p>
                                            </div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                        <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                            <button wire:click="$set('showPaymentForm',false)" class="px-4 py-2 text-sm text-slate-600">Cancelar</button>
                            <button wire:click="applyPayment" class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                <span wire:loading.remove wire:target="applyPayment">Registrar pago</span>
                                <span wire:loading wire:target="applyPayment">Procesando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- ══════════════════════════════════ VISTA: ÓRDENES ══════════════════════════════════ --}}
            @if($view === 'orders')
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-forest-950 border-b border-forest-800">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Nro.</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Fecha</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Fecha entrega</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Proveedor</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Total</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Estado</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($orders as $ord)
                                <tr wire:key="ord-{{ $ord->id }}" class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-3 font-mono text-xs font-bold text-slate-700">OC-{{ str_pad($ord->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $ord->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $ord->expected_date?->format('d/m/Y') ?? '—' }}</td>
                                    <td class="px-6 py-3 font-medium text-slate-700">{{ $ord->third?->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm text-slate-800">$ {{ number_format($ord->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $ord->status->color() }}">{{ $ord->status->label() }}</span>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if(!session('audit_mode') && !session('reference_mode'))
                                            <div class="flex items-center justify-end gap-3">
                                                @if($ord->status === \App\Enums\PurchaseOrderStatus::Pendiente)
                                                    <button wire:click="openEditOrder({{ $ord->id }})" class="text-xs text-forest-600 hover:text-forest-800 font-medium">Editar</button>
                                                    <button x-on:click="confirmAction('¿Confirmar recepción de mercancía? Se creará una factura de compra en borrador.', () => $wire.receiveOrder({{ $ord->id }}), {confirmText: 'Sí, recibir'})" class="text-xs text-gold-600 hover:text-gold-800 font-medium">Recibir mercancía</button>
                                                    <button x-on:click="confirmAction('¿Cancelar esta orden?', () => $wire.cancelOrder({{ $ord->id }}), {danger: true, confirmText: 'Sí, cancelar'})" class="text-xs text-red-500 hover:text-red-700 font-medium">Cancelar</button>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                        No hay órdenes de compra.
                                        @if(!session('audit_mode') && !session('reference_mode'))
                                            <button wire:click="openCreateOrder" class="ml-2 text-forest-600 hover:underline">Crear la primera</button>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($orders->hasPages())
                        <div class="px-6 py-4 border-t border-cream-100">{{ $orders->links() }}</div>
                    @endif
                </div>
            @endif

            {{-- Vista facturas --}}
            @if($view === 'invoices')
                <div class="mb-4">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar proveedor..."
                        class="w-64 rounded-xl border-cream-200 text-sm shadow-sm focus:ring-forest-500 focus:border-forest-500" />
                </div>
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-forest-950 border-b border-forest-800">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Nro. proveedor</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Fecha</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Proveedor</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Total bruto</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Retenciones</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Neto proveedor</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Estado</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($invoices as $inv)
                                <tr wire:key="pinv-{{ $inv->id }}" class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-3 font-mono text-xs text-slate-600">{{ $inv->supplier_invoice_number ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $inv->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 font-medium text-slate-700">{{ $inv->third?->name ?? '—' }}</td>
                                    @php $totalBruto = $inv->subtotal + $inv->tax_amount; @endphp
                                    <td class="px-6 py-3 text-right font-mono text-sm text-slate-500">$ {{ number_format($totalBruto, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm">
                                        @if($inv->tieneRetenciones())
                                            <span class="text-red-600 font-semibold">- $ {{ number_format($inv->total_retenciones, 0, ',', '.') }}</span>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-right font-mono text-sm text-slate-800 font-semibold">$ {{ number_format($inv->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $inv->status->color() }}">{{ $inv->status->label() }}</span>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if(!session('audit_mode') && !session('reference_mode'))
                                        <div class="flex items-center justify-end gap-3">
                                            @if($inv->status->value === 'borrador')
                                                <button wire:click="openEdit({{ $inv->id }})" class="text-xs text-forest-600 hover:text-forest-800 font-medium">Editar</button>
                                                <button wire:click="openConfirm({{ $inv->id }})" class="text-xs text-gold-600 hover:text-gold-800 font-medium">Confirmar</button>
                                                <button x-on:click="confirmAction('¿Eliminar este borrador?', () => $wire.delete({{ $inv->id }}), {danger: true, confirmText: 'Sí, eliminar'})" class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                            @elseif($inv->isPendiente() && $inv->balance() > 0)
                                                <button wire:click="openPayment({{ $inv->third_id }})" class="text-xs text-forest-600 hover:text-forest-800 font-medium">Registrar pago</button>
                                            @endif
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="px-6 py-10 text-center text-slate-400">No hay facturas de compra. <button wire:click="openCreate" class="ml-2 text-forest-600 hover:underline">Crear la primera</button></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($invoices->hasPages())
                        <div class="px-6 py-4 border-t border-cream-100">{{ $invoices->links() }}</div>
                    @endif
                </div>
            @endif

            {{-- Vista pagos --}}
            @if($view === 'payments')
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-forest-950 border-b border-forest-800">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Referencia</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Fecha</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Proveedor</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Monto</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-forest-300 uppercase tracking-wide">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($payments as $pay)
                                <tr wire:key="pay-{{ $pay->id }}" class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-mono text-xs text-slate-600">PAG-{{ str_pad($pay->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $pay->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 font-medium text-slate-700">{{ $pay->third?->name ?? '—' }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm font-semibold text-slate-800">$ {{ number_format($pay->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $pay->status->color() }}">{{ $pay->status->label() }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-6 py-10 text-center text-slate-400">No hay pagos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>

    {{-- ══════════════ MODAL CONFIRMAR FACTURA CON RETENCIONES ══════════════ --}}
    @if($showConfirmModal && !session('audit_mode'))
        <div class="fixed inset-0 bg-slate-900/60 z-50 flex items-center justify-center p-4"
             wire:click.self="$set('showConfirmModal', false)">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">

                <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-800">Confirmar factura de compra</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Configura las retenciones antes de generar el asiento contable.</p>
                    </div>
                    <button wire:click="$set('showConfirmModal', false)" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                <div class="px-6 py-5 space-y-4">

                    {{-- Retención en la fuente --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Retención en la fuente
                        </label>
                        <select wire:model.live="retencion_concepto"
                                wire:change="calcularRetenciones"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                            <option value="">— No aplica —</option>
                            @foreach($conceptosRetencion as $c)
                                <option value="{{ $c->value }}">{{ $c->label() }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-slate-400 mt-1">
                            Solo aplica si el subtotal supera la base mínima del concepto.
                        </p>
                    </div>

                    {{-- Reteiva --}}
                    <div class="flex items-start gap-3">
                        <input type="checkbox" id="aplicar_reteiva"
                               wire:model.live="aplicar_reteiva"
                               wire:change="calcularRetenciones"
                               class="mt-0.5 rounded border-slate-300 text-forest-600 focus:ring-forest-500">
                        <label for="aplicar_reteiva" class="text-sm text-slate-700">
                            Aplicar <strong>Reteiva</strong> (15% del IVA)
                            <span class="block text-xs text-slate-400 font-normal">Aplica cuando el proveedor es del régimen simplificado.</span>
                        </label>
                    </div>

                    {{-- Reteica --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Reteica — porcentaje <span class="text-slate-400 font-normal">(0 = no aplica)</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <input type="number" step="0.01" min="0" max="10"
                                   wire:model.live="reteica_porcentaje"
                                   wire:change="calcularRetenciones"
                                   class="block w-32 rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 text-right"
                                   placeholder="0.00">
                            <span class="text-sm text-slate-500">%</span>
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Varía según el municipio y la actividad económica.</p>
                    </div>

                    {{-- Resumen de retenciones calculadas --}}
                    @if(!empty($retencionesSummary))
                        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-sm space-y-1">
                            <p class="font-semibold text-amber-800 mb-2">Resumen de retenciones</p>
                            @if($retencionesSummary['retefte_valor'] > 0)
                                <div class="flex justify-between text-slate-700">
                                    <span>RteFte ({{ $retencionesSummary['retefte_porcentaje'] }}% sobre $ {{ number_format($retencionesSummary['retefte_base'], 0, ',', '.') }}):</span>
                                    <span class="font-mono text-red-600">- $ {{ number_format($retencionesSummary['retefte_valor'], 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if($retencionesSummary['reteiva_valor'] > 0)
                                <div class="flex justify-between text-slate-700">
                                    <span>Reteiva (15% del IVA):</span>
                                    <span class="font-mono text-red-600">- $ {{ number_format($retencionesSummary['reteiva_valor'], 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if($retencionesSummary['reteica_valor'] > 0)
                                <div class="flex justify-between text-slate-700">
                                    <span>Reteica:</span>
                                    <span class="font-mono text-red-600">- $ {{ number_format($retencionesSummary['reteica_valor'], 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between font-semibold text-slate-800 border-t border-amber-200 pt-1 mt-1">
                                <span>Total retenciones:</span>
                                <span class="font-mono text-red-700">- $ {{ number_format($retencionesSummary['total_retenciones'], 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between font-bold text-slate-900 text-base">
                                <span>Neto a pagar al proveedor:</span>
                                <span class="font-mono">$ {{ number_format($retencionesSummary['total_a_pagar'], 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endif

                    {{-- Nota educativa --}}
                    <div class="p-3 bg-blue-50 border border-blue-100 rounded-xl text-xs text-blue-700">
                        <strong>Contexto colombiano:</strong> Las retenciones son pagadas directamente a la DIAN por la empresa compradora.
                        Se acreditan en las cuentas 2365 (RteFte), 2367 (Reteiva) y 2368 (Reteica) del pasivo.
                    </div>

                </div>

                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3 bg-white rounded-b-2xl">
                    <button wire:click="$set('showConfirmModal', false)"
                            type="button"
                            class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">
                        Cancelar
                    </button>
                    <button wire:click="confirmarConRetenciones"
                            type="button"
                            class="px-4 py-2 bg-gold-700 text-white text-sm font-semibold rounded-xl hover:bg-gold-600 transition">
                        <span wire:loading.remove wire:target="confirmarConRetenciones">Confirmar y generar asiento</span>
                        <span wire:loading wire:target="confirmarConRetenciones">Procesando...</span>
                    </button>
                </div>

            </div>
        </div>
    @endif

</div>
