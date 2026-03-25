<div>

    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Maestros contables</p>
                <h1 class="font-display text-2xl font-bold text-white">Productos</h1>
                <p class="text-forest-300 text-sm mt-1">Inventario con cuentas contables vinculadas</p>
            </div>
            @if(! session('audit_mode') && ! session('reference_mode'))
                <button wire:click="openCreate"
                    class="flex items-center gap-2 px-4 py-2 bg-gold-500 text-forest-950 text-sm font-bold rounded-xl hover:bg-gold-400 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nuevo producto
                </button>
            @endif
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">

            {{-- Buscador --}}
            <div class="mb-6">
                <div class="relative w-full sm:w-80">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                    </svg>
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar producto…"
                        class="w-full pl-9 rounded-xl border-cream-200 text-sm shadow-sm focus:ring-forest-500 focus:border-forest-500" />
                </div>
            </div>

            {{-- Tabla --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-forest-950 border-b border-forest-800">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide">Código</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide">Producto</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide hidden sm:table-cell">Unidad</th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide">Precio venta</th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide hidden md:table-cell">Costo</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide hidden sm:table-cell">IVA</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cream-100">
                        @forelse($products as $product)
                            <tr wire:key="product-{{ $product->id }}" class="hover:bg-cream-50 transition">
                                <td class="px-6 py-3 font-mono text-xs text-slate-500">{{ $product->code }}</td>
                                <td class="px-6 py-3 font-medium text-slate-700">{{ $product->name }}</td>
                                <td class="px-6 py-3 text-xs text-slate-500 hidden sm:table-cell">{{ $product->unit->label() }}</td>
                                <td class="px-6 py-3 text-right font-mono text-sm text-slate-700">$ {{ number_format($product->sale_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right font-mono text-sm text-slate-500 hidden md:table-cell">$ {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 hidden sm:table-cell">
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-medium {{ $product->tax_rate->value == 0 ? 'bg-slate-100 text-slate-500 border border-slate-200' : 'bg-green-50 text-green-700 border border-green-100' }}">
                                        {{ $product->tax_rate->value }}%
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    @if(! session('audit_mode') && ! session('reference_mode'))
                                        <div class="flex items-center justify-end gap-2">
                                            <button wire:click="openEdit({{ $product->id }})"
                                                class="text-xs text-forest-600 hover:text-forest-800 font-semibold px-2 py-1 rounded-lg hover:bg-forest-50 transition">
                                                Editar
                                            </button>
                                            <button x-on:click="confirmAction('¿Eliminar este producto?', () => $wire.delete({{ $product->id }}), { danger: true, confirmText: 'Sí, eliminar' })"
                                                class="text-xs text-red-500 hover:text-red-700 font-semibold px-2 py-1 rounded-lg hover:bg-red-50 transition">
                                                Eliminar
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <p class="text-slate-400 text-sm mb-2">No hay productos registrados.</p>
                                    @if(! session('audit_mode') && ! session('reference_mode'))
                                        <button wire:click="openCreate" class="text-sm text-forest-600 hover:text-forest-800 font-semibold hover:underline">
                                            Crear el primero
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($products->hasPages())
                    <div class="px-6 py-4 border-t border-cream-100">{{ $products->links() }}</div>
                @endif
            </div>

        </div>
    </div>

    {{-- ═══ Modal: Nuevo / Editar producto ═══ --}}
    @if(! session('audit_mode') && ! session('reference_mode'))
        <div x-show="$wire.showForm" x-cloak class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto" wire:click.self="cancelForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl my-8">
                <div class="px-6 py-5 border-b border-cream-100 flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
                    <h3 class="text-base font-semibold text-slate-800">{{ $editingId ? 'Editar producto' : 'Nuevo producto' }}</h3>
                    <button wire:click="cancelForm" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition text-sm">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Código</label>
                            <input wire:model="code" type="text" placeholder="ej: PROD001"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Unidad</label>
                            <select wire:model="unit" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                @foreach($units as $u)
                                    <option value="{{ $u->value }}">{{ $u->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
                        <input wire:model="name" type="text"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción <span class="text-slate-400 font-normal">(opcional)</span></label>
                        <textarea wire:model="description" rows="2"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500"></textarea>
                    </div>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Precio venta</label>
                            <input wire:model="sale_price" type="number" step="0.01" min="0"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('sale_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Precio costo</label>
                            <input wire:model="cost_price" type="number" step="0.01" min="0"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">IVA</label>
                            <select wire:model="tax_rate" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                @foreach($taxRates as $t)
                                    <option value="{{ $t->value }}">{{ $t->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="border-t border-cream-100 pt-4">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Cuentas contables</p>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Inventario (Activo)</label>
                                <select wire:model="inventory_account_id" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                    <option value="">— Sin cuenta —</option>
                                    @foreach($accounts->where('type', 'activo') as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Ingresos (Venta)</label>
                                <select wire:model="revenue_account_id" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                    <option value="">— Sin cuenta —</option>
                                    @foreach($accounts->where('type', 'ingreso') as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Costo de ventas</label>
                                <select wire:model="cogs_account_id" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                    <option value="">— Sin cuenta —</option>
                                    @foreach($accounts->where('type', 'costo') as $acc)
                                        <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3 sticky bottom-0 bg-white rounded-b-2xl">
                    <button wire:click="cancelForm" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">{{ $editingId ? 'Actualizar' : 'Guardar' }}</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
