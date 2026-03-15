<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Compras</h2>
                <p class="text-sm text-slate-500 mt-0.5">Facturas de proveedores y pagos</p>
            </div>
            <div class="flex gap-2">
                @if($view === 'invoices' && !session('audit_mode'))
                    <button wire:click="openCreate" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">+ Nueva factura</button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Flash --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-accent-50 border border-accent-200 rounded-xl text-accent-700 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">{{ session('error') }}</div>
            @endif

            {{-- Tabs --}}
            <div class="flex gap-1 mb-6 border-b border-slate-200">
                <button wire:click="$set('view','invoices')" class="px-4 py-2 text-sm font-medium transition {{ $view === 'invoices' ? 'border-b-2 border-brand-700 text-brand-800' : 'text-slate-500 hover:text-slate-700' }}">Facturas de compra</button>
                <button wire:click="$set('view','payments')" class="px-4 py-2 text-sm font-medium transition {{ $view === 'payments' ? 'border-b-2 border-brand-700 text-brand-800' : 'text-slate-500 hover:text-slate-700' }}">Pagos a proveedores</button>
            </div>

            {{-- Modal factura de compra --}}
            @if($showForm && !session('audit_mode'))
                <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto" wire:click.self="$set('showForm',false)">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl my-8">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white rounded-t-xl z-10">
                            <h3 class="font-semibold text-slate-800">{{ $editingId ? 'Editar factura de compra' : 'Nueva factura de compra' }}</h3>
                            <button wire:click="$set('showForm',false)" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Proveedor</label>
                                    <select wire:model="third_id" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                        <option value="0">— Seleccionar —</option>
                                        @foreach($suppliers as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('third_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha</label>
                                    <input wire:model="date" type="date" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Nro. factura proveedor</label>
                                    <input wire:model="supplier_invoice_number" type="text" placeholder="ej: FV-001" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                </div>
                            </div>

                            {{-- Líneas --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Líneas</p>
                                    <button wire:click="addLine" type="button" class="text-xs text-brand-600 hover:text-brand-800 font-medium">+ Agregar línea</button>
                                </div>
                                <div class="border border-slate-200 rounded-lg overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead class="bg-slate-50 border-b border-slate-200">
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
                                                        <select wire:model.live="lines.{{ $i }}.product_id" class="block w-full rounded border-slate-200 text-xs focus:ring-brand-500 focus:border-brand-500">
                                                            <option value="">— Libre —</option>
                                                            @foreach($products as $p)
                                                                <option value="{{ $p->id }}">{{ $p->code }} {{ $p->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model="lines.{{ $i }}.description" type="text" class="block w-full rounded border-slate-200 text-xs focus:ring-brand-500 focus:border-brand-500" />
                                                        @error("lines.{$i}.description") <p class="text-red-500 text-xs">{{ $message }}</p> @enderror
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model.live="lines.{{ $i }}.qty" type="number" step="0.01" min="0.01" class="block w-full rounded border-slate-200 text-xs text-right focus:ring-brand-500 focus:border-brand-500" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model.live="lines.{{ $i }}.unit_cost" type="number" step="0.01" min="0" class="block w-full rounded border-slate-200 text-xs text-right focus:ring-brand-500 focus:border-brand-500" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <select wire:model.live="lines.{{ $i }}.tax_rate" class="block w-full rounded border-slate-200 text-xs focus:ring-brand-500 focus:border-brand-500">
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
                                                <tr><td colspan="7" class="px-3 py-6 text-center text-slate-400 text-xs">Sin líneas. <button wire:click="addLine" type="button" class="text-brand-600 hover:underline">Agregar</button></td></tr>
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
                                            <div class="flex justify-between font-bold text-slate-800 border-t border-slate-200 pt-1"><span>Total:</span><span class="font-mono">$ {{ number_format($ts+$tt, 0, ',', '.') }}</span></div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-white rounded-b-xl">
                            <button wire:click="$set('showForm',false)" type="button" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancelar</button>
                            <button wire:click="save" type="button" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
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
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="font-semibold text-slate-800">Registrar pago a proveedor</h3>
                            <button wire:click="$set('showPaymentForm',false)" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Fecha de pago</label>
                                <input wire:model="payment_date" type="date" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Facturas pendientes</p>
                                @forelse($payment_items as $pi => $item)
                                    <div class="flex items-center gap-3 py-2 border-b border-slate-100" wire:key="pi-{{ $pi }}">
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-slate-700">{{ $item['invoice_ref'] }}</p>
                                            <p class="text-xs text-slate-400">Saldo: $ {{ number_format($item['invoice_balance'], 0, ',', '.') }}</p>
                                        </div>
                                        <input wire:model.live="payment_items.{{ $pi }}.amount_applied" type="number" step="0.01" min="0" class="w-32 rounded-lg border-slate-200 text-sm text-right focus:ring-brand-500 focus:border-brand-500" />
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
                        </div>
                        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                            <button wire:click="$set('showPaymentForm',false)" class="px-4 py-2 text-sm text-slate-600">Cancelar</button>
                            <button wire:click="applyPayment" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                                <span wire:loading.remove wire:target="applyPayment">Registrar pago</span>
                                <span wire:loading wire:target="applyPayment">Procesando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Vista facturas --}}
            @if($view === 'invoices')
                <div class="mb-4">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar proveedor..."
                        class="w-64 rounded-lg border-slate-200 text-sm shadow-sm focus:ring-brand-500 focus:border-brand-500" />
                </div>
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nro. proveedor</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Proveedor</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Saldo</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($invoices as $inv)
                                <tr wire:key="pinv-{{ $inv->id }}" class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-3 font-mono text-xs text-slate-600">{{ $inv->supplier_invoice_number ?? '—' }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $inv->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 font-medium text-slate-700">{{ $inv->third->name }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm text-slate-800">$ {{ number_format($inv->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-3 text-right font-mono text-sm {{ $inv->balance() > 0 ? 'text-red-600 font-semibold' : 'text-slate-400' }}">$ {{ number_format($inv->balance(), 0, ',', '.') }}</td>
                                    <td class="px-6 py-3">
                                        <span class="px-2 py-0.5 rounded text-xs font-medium {{ $inv->status->color() }}">{{ $inv->status->label() }}</span>
                                    </td>
                                    <td class="px-6 py-3">
                                        @if(!session('audit_mode'))
                                        <div class="flex items-center justify-end gap-3">
                                            @if($inv->status->value === 'borrador')
                                                <button wire:click="openEdit({{ $inv->id }})" class="text-xs text-brand-600 hover:text-brand-800 font-medium">Editar</button>
                                                <button wire:click="confirm({{ $inv->id }})" wire:confirm="¿Confirmar esta factura? Se generará el asiento contable." class="text-xs text-accent-600 hover:text-accent-800 font-medium">Confirmar</button>
                                                <button wire:click="delete({{ $inv->id }})" wire:confirm="¿Eliminar este borrador?" class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                            @elseif($inv->isPendiente() && $inv->balance() > 0)
                                                <button wire:click="openPayment({{ $inv->third_id }})" class="text-xs text-brand-600 hover:text-brand-800 font-medium">Registrar pago</button>
                                            @endif
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-6 py-10 text-center text-slate-400">No hay facturas de compra. <button wire:click="openCreate" class="ml-2 text-brand-600 hover:underline">Crear la primera</button></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    @if($invoices->hasPages())
                        <div class="px-6 py-4 border-t border-slate-100">{{ $invoices->links() }}</div>
                    @endif
                </div>
            @endif

            {{-- Vista pagos --}}
            @if($view === 'payments')
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Referencia</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Proveedor</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Monto</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($payments as $pay)
                                <tr wire:key="pay-{{ $pay->id }}" class="hover:bg-slate-50">
                                    <td class="px-6 py-3 font-mono text-xs text-slate-600">PAG-{{ str_pad($pay->id, 5, '0', STR_PAD_LEFT) }}</td>
                                    <td class="px-6 py-3 text-slate-600">{{ $pay->date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-3 font-medium text-slate-700">{{ $pay->third->name }}</td>
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
</div>
