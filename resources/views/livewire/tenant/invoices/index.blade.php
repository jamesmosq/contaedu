<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Facturación</h2>
                <p class="text-sm text-slate-500 mt-0.5">Facturas de venta</p>
            </div>
            @if(!session('audit_mode'))
            <button wire:click="openCreate" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                + Nueva factura
            </button>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Flash messages --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-accent-50 border border-accent-200 rounded-xl text-accent-700 text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- Filtros --}}
            <div class="mb-5 flex flex-wrap gap-3">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar cliente..."
                    class="w-64 rounded-lg border-slate-200 text-sm shadow-sm focus:ring-brand-500 focus:border-brand-500" />
                <select wire:model.live="filterStatus" class="rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s->value }}">{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Modal factura --}}
            @if($showForm && !session('audit_mode'))
                <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto" wire:click.self="cancelForm">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-4xl my-8">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white rounded-t-xl z-10">
                            <h3 class="font-semibold text-slate-800">{{ $editingId ? 'Editar factura' : 'Nueva factura de venta' }}</h3>
                            <button wire:click="cancelForm" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-5">

                            {{-- Cabecera --}}
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                                <div class="col-span-2">
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Cliente</label>
                                    <select wire:model="third_id" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                        <option value="0">— Seleccionar —</option>
                                        @foreach($thirds as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->document }})</option>
                                        @endforeach
                                    </select>
                                    @error('third_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Fecha</label>
                                    <input wire:model="date" type="date" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Vencimiento</label>
                                    <input wire:model="due_date" type="date" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                </div>
                            </div>

                            {{-- Líneas --}}
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Líneas</p>
                                    <button wire:click="addLine" type="button" class="text-xs text-brand-600 hover:text-brand-800 font-medium transition">+ Agregar línea</button>
                                </div>
                                @error('lines') <p class="text-red-500 text-xs mb-2">{{ $message }}</p> @enderror

                                <div class="border border-slate-200 rounded-lg overflow-hidden">
                                    <table class="w-full text-sm">
                                        <thead class="bg-slate-50 border-b border-slate-200">
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
                                                        <input wire:model.live="lines.{{ $i }}.unit_price" type="number" step="0.01" min="0" class="block w-full rounded border-slate-200 text-xs text-right focus:ring-brand-500 focus:border-brand-500" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <input wire:model.live="lines.{{ $i }}.discount_pct" type="number" step="0.01" min="0" max="100" class="block w-full rounded border-slate-200 text-xs text-right focus:ring-brand-500 focus:border-brand-500" />
                                                    </td>
                                                    <td class="px-3 py-2">
                                                        <select wire:model.live="lines.{{ $i }}.tax_rate" class="block w-full rounded border-slate-200 text-xs focus:ring-brand-500 focus:border-brand-500">
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
                                                        Sin líneas. <button wire:click="addLine" type="button" class="text-brand-600 hover:underline">Agregar la primera</button>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Totales --}}
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
                                            <div class="flex justify-between font-bold text-slate-800 border-t border-slate-200 pt-1"><span>Total:</span><span class="font-mono">$ {{ number_format($grandTotal, 0, ',', '.') }}</span></div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Notas --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Notas <span class="text-slate-400">(opcional)</span></label>
                                <textarea wire:model="notes" rows="2" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500"></textarea>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-white rounded-b-xl">
                            <button wire:click="cancelForm" type="button" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 transition">Cancelar</button>
                            <button wire:click="save" type="button" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                                <span wire:loading.remove wire:target="save">Guardar borrador</span>
                                <span wire:loading wire:target="save">Guardando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tabla --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nro.</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Cliente</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($invoices as $invoice)
                            <tr wire:key="inv-{{ $invoice->id }}" class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3 font-mono text-xs font-bold text-slate-700">{{ $invoice->fullReference() }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ $invoice->date->format('d/m/Y') }}</td>
                                <td class="px-6 py-3 text-slate-700">{{ $invoice->third->name }}</td>
                                <td class="px-6 py-3 text-right font-mono text-sm font-semibold text-slate-800">$ {{ number_format($invoice->total, 0, ',', '.') }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $invoice->status->color() }}">
                                        {{ $invoice->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-3">
                                    @if(!session('audit_mode'))
                                    <div class="flex items-center justify-end gap-3">
                                        @if($invoice->isBorrador())
                                            <button wire:click="openEdit({{ $invoice->id }})" class="text-xs text-brand-600 hover:text-brand-800 font-medium">Editar</button>
                                            <button wire:click="confirm({{ $invoice->id }})" wire:confirm="¿Confirmar esta factura? Se generará el asiento contable." class="text-xs text-accent-600 hover:text-accent-800 font-medium">Confirmar</button>
                                            <button wire:click="delete({{ $invoice->id }})" wire:confirm="¿Eliminar este borrador?" class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                        @elseif($invoice->isEmitida())
                                            <button wire:click="annul({{ $invoice->id }})" wire:confirm="¿Anular esta factura? Se generará un asiento de reverso." class="text-xs text-red-500 hover:text-red-700 font-medium">Anular</button>
                                        @endif
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                    No hay facturas registradas.
                                    <button wire:click="openCreate" class="ml-2 text-brand-600 hover:underline">Crear la primera</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($invoices->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">{{ $invoices->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</div>
