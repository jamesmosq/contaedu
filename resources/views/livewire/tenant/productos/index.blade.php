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

            {{-- Nota pedagógica --}}
            <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 text-sm text-sky-800 mb-6">
                <p class="font-semibold mb-1">¿Por qué cada producto tiene cuentas contables vinculadas?</p>
                <p>En contabilidad colombiana, cada producto de inventario está asociado a tres cuentas del PUC: <strong>Inventario (1435)</strong> que registra el valor almacenado, <strong>Ingresos por ventas (4135)</strong> que se abona al facturar, y <strong>Costo de ventas (6135)</strong> que se debita cuando sale del inventario. Sin estas cuentas configuradas, el sistema no puede generar los asientos contables correctamente al vender o comprar.</p>
            </div>

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
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide hidden lg:table-cell">Stock</th>
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
                                @php
                                    $stock   = $product->stockActual();
                                    $dec     = $product->stockDecimals();
                                    $bajo    = $product->stockBajo();
                                @endphp
                                <td class="px-6 py-3 text-right hidden lg:table-cell">
                                    @if($product->inventory_account_id)
                                        @if($stock < 0)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs font-semibold" title="Stock negativo: vendiste más de lo disponible">
                                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                                {{ number_format($stock, $dec, ',', '.') }}
                                            </span>
                                        @elseif($bajo)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-amber-50 border border-amber-200 text-amber-700 text-xs font-semibold" title="Stock bajo: mínimo configurado {{ number_format($product->stock_minimo, $dec, ',', '.') }}">
                                                <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/></svg>
                                                {{ number_format($stock, $dec, ',', '.') }}
                                            </span>
                                        @elseif($stock == 0)
                                            <span class="font-mono text-sm text-slate-400">0</span>
                                        @else
                                            <span class="font-mono text-sm text-slate-700">{{ number_format($stock, $dec, ',', '.') }}</span>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-300">—</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 hidden sm:table-cell">
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-medium {{ $product->tax_rate->value == 0 ? 'bg-slate-100 text-slate-500 border border-slate-200' : 'bg-green-50 text-green-700 border border-green-100' }}">
                                        {{ $product->tax_rate->value }}%
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($product->inventory_account_id)
                                            <button wire:click="openKardex({{ $product->id }})"
                                                class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold px-2 py-1 rounded-lg hover:bg-indigo-50 transition">
                                                Kardex
                                            </button>
                                        @endif
                                        @if(! session('audit_mode') && ! session('reference_mode'))
                                            <button wire:click="abastecer({{ $product->id }})"
                                                class="text-xs text-emerald-600 hover:text-emerald-800 font-semibold px-2 py-1 rounded-lg hover:bg-emerald-50 transition">
                                                Abastecer
                                            </button>
                                            <button wire:click="openEdit({{ $product->id }})"
                                                class="text-xs text-forest-600 hover:text-forest-800 font-semibold px-2 py-1 rounded-lg hover:bg-forest-50 transition">
                                                Editar
                                            </button>
                                            <button x-on:click="confirmAction('¿Eliminar este producto?', () => $wire.delete({{ $product->id }}), { danger: true, confirmText: 'Sí, eliminar' })"
                                                class="text-xs text-red-500 hover:text-red-700 font-semibold px-2 py-1 rounded-lg hover:bg-red-50 transition">
                                                Eliminar
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center">
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

    {{-- ═══ Panel: Kardex de inventario ═══ --}}
    @if($showKardex)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto" wire:click.self="closeKardex">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-3xl my-8">
                <div class="px-6 py-5 border-b border-cream-100 flex items-center justify-between sticky top-0 bg-white rounded-t-2xl z-10">
                    <div>
                        <h3 class="text-base font-semibold text-slate-800">Kardex — {{ $kardexProductName }}</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Últimos 20 movimientos · modo {{ modoContable() }}</p>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-xs text-slate-400">Stock actual</p>
                            <p class="text-lg font-bold {{ $kardexStock < 0 ? 'text-red-600' : 'text-forest-800' }}">
                                {{ number_format($kardexStock, 2, ',', '.') }}
                            </p>
                        </div>
                        <button wire:click="closeKardex" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition text-sm">✕</button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    @if($kardexMovements->isEmpty())
                        <div class="px-6 py-12 text-center text-slate-400 text-sm">
                            No hay movimientos registrados para este producto en el modo actual.
                        </div>
                    @else
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-slate-50 border-b border-cream-100">
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden sm:table-cell">Descripción</th>
                                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden lg:table-cell">Proveedor / Cliente</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Qty</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Costo unit.</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Saldo qty</th>
                                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide hidden md:table-cell">Saldo valor</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-cream-100">
                                @foreach($kardexMovements as $mov)
                                    <tr class="hover:bg-cream-50 transition">
                                        <td class="px-4 py-2.5 text-xs text-slate-500 whitespace-nowrap">{{ $mov->fecha->format('d/m/Y') }}</td>
                                        <td class="px-4 py-2.5">
                                            <span class="px-2 py-0.5 rounded-lg text-xs font-medium {{ $mov->tipo === 'entrada' ? 'bg-green-50 text-green-700 border border-green-100' : 'bg-red-50 text-red-700 border border-red-100' }}">
                                                {{ ucfirst($mov->tipo) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-2.5 text-xs text-slate-500 hidden sm:table-cell max-w-xs truncate">{{ $mov->descripcion }}</td>
                                        <td class="px-4 py-2.5 text-xs hidden lg:table-cell">
                                            @if($mov->third)
                                                <span class="font-medium text-slate-700">{{ $mov->third->name }}</span>
                                                <span class="block text-slate-400 text-xs">{{ $mov->third->document_number }}</span>
                                            @else
                                                <span class="text-slate-300">—</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-mono text-sm {{ $mov->tipo === 'entrada' ? 'text-green-700' : 'text-red-600' }}">
                                            {{ $mov->tipo === 'entrada' ? '+' : '-' }}{{ number_format($mov->qty, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-mono text-xs text-slate-500 hidden md:table-cell">$ {{ number_format($mov->costo_unitario, 2, ',', '.') }}</td>
                                        <td class="px-4 py-2.5 text-right font-mono text-sm font-semibold {{ (float)$mov->saldo_qty < 0 ? 'text-red-600' : 'text-slate-700' }}">
                                            {{ number_format($mov->saldo_qty, 2, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-mono text-xs text-slate-500 hidden md:table-cell">$ {{ number_format($mov->saldo_valor, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end">
                    <button wire:click="closeKardex" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">Cerrar</button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══ Modal: Nuevo / Editar producto ═══ --}}
    @if(! session('audit_mode') && ! session('reference_mode'))
        <div x-show="$wire.showForm" x-cloak class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto">
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
                    <div class="grid grid-cols-4 gap-4">
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
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Stock mínimo</label>
                            <input wire:model="stock_minimo" type="number" step="1" min="0" placeholder="0"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            <p class="text-slate-400 text-xs mt-1">Alerta cuando baje de este nivel</p>
                        </div>
                    </div>
                    {{-- Stock inicial (solo en creación) --}}
                    @if(! $editingId)
                    <div class="border-t border-cream-100 pt-4">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Stock inicial</p>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Cantidad en existencia</label>
                                <input wire:model.live="initial_stock" type="number" step="0.01" min="0" placeholder="0"
                                    class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                <p class="text-xs text-slate-400 mt-1">Deja en 0 si el producto aún no tiene existencias</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Costo unitario inicial</label>
                                <input wire:model="initial_cost" type="number" step="0.01" min="0" placeholder="0"
                                    class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                <p class="text-xs text-slate-400 mt-1">Deja en 0 para usar el precio de costo</p>
                            </div>
                        </div>
                        @if((float)$initial_stock > 0)
                            @php $costoUnit = (float)$initial_cost ?: (float)$cost_price; @endphp
                            <p class="text-xs text-forest-700 bg-forest-50 border border-forest-100 rounded-lg px-3 py-2 mt-2">
                                Asiento generado: <strong>DR 1435 ${{ number_format((float)$initial_stock * $costoUnit, 0, ',', '.') }}</strong>
                                / <strong>CR 3115 ${{ number_format((float)$initial_stock * $costoUnit, 0, ',', '.') }}</strong>
                                — {{ number_format((float)$initial_stock, 2, ',', '.') }} uds. × ${{ number_format($costoUnit, 0, ',', '.') }}
                            </p>
                        @endif
                    </div>
                    @endif

                    <div class="border-t border-cream-100 pt-4"
                         x-data="{
                             accounts: @js($accounts->map(fn($a) => ['id' => $a->id, 'code' => $a->code, 'display' => ucwords(strtolower($a->name)).' ('.$a->type.')', 'lname' => strtolower($a->name)])->values()),
                             inv:  { query: '', open: false, results: [] },
                             rev:  { query: '', open: false, results: [] },
                             cost: { query: '', open: false, results: [] },
                             init() {
                                 const find = id => id ? this.accounts.find(a => a.id == id) : null;
                                 const lb   = a  => a  ? a.code + ' — ' + a.display : '';
                                 this.$watch('$wire.showForm', val => {
                                     if (val) {
                                         this.inv.query  = lb(find(this.$wire.inventory_account_id));
                                         this.rev.query  = lb(find(this.$wire.revenue_account_id));
                                         this.cost.query = lb(find(this.$wire.cogs_account_id));
                                     } else {
                                         this.inv.query = this.rev.query = this.cost.query = '';
                                         this.inv.open  = this.rev.open  = this.cost.open  = false;
                                     }
                                 });
                             },
                             filter(f, q) {
                                 const lo = q.toLowerCase().trim();
                                 this[f].results = lo.length ? this.accounts.filter(a => a.code.includes(lo) || a.lname.includes(lo)).slice(0, 14) : [];
                                 this[f].open = this[f].results.length > 0;
                             },
                             pick(f, wf, acc) {
                                 this[f].query = acc.code + ' — ' + acc.display;
                                 this[f].open = false; this[f].results = [];
                                 $wire.set(wf, acc.id);
                             },
                             clear(f, wf) {
                                 this[f].query = ''; this[f].open = false; this[f].results = [];
                                 $wire.set(wf, null);
                             }
                         }">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Cuentas contables</p>
                        <div class="space-y-3">

                            {{-- Inventario --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Inventario (Activo)</label>
                                <div class="relative">
                                    <input type="text" x-model="inv.query"
                                        @input="filter('inv', inv.query)"
                                        @focus="filter('inv', inv.query)"
                                        @click.outside="inv.open = false"
                                        @keydown.escape="inv.open = false"
                                        placeholder="Escribe código o nombre del PUC…"
                                        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 pr-8" />
                                    <button type="button" x-show="inv.query" @click="clear('inv', 'inventory_account_id')"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    </button>
                                    <div x-show="inv.open"
                                        class="absolute z-50 left-0 right-0 mt-1 bg-white border border-cream-200 rounded-xl shadow-lg max-h-52 overflow-y-auto">
                                        <template x-for="acc in inv.results" :key="acc.id">
                                            <button type="button" @mousedown.prevent="pick('inv', 'inventory_account_id', acc)"
                                                class="w-full text-left px-3 py-2 hover:bg-forest-50 border-b border-cream-100 last:border-0 flex items-baseline gap-2">
                                                <span class="font-mono text-xs font-bold text-forest-800 shrink-0" x-text="acc.code"></span>
                                                <span class="text-xs text-slate-600 truncate" x-text="acc.display"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                @error('inventory_account_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                <p class="text-slate-400 text-xs mt-1">Sugerida: 1435 — Mercancías no fabricadas por la empresa</p>
                            </div>

                            {{-- Ingresos --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Ingresos (Venta)</label>
                                <div class="relative">
                                    <input type="text" x-model="rev.query"
                                        @input="filter('rev', rev.query)"
                                        @focus="filter('rev', rev.query)"
                                        @click.outside="rev.open = false"
                                        @keydown.escape="rev.open = false"
                                        placeholder="Escribe código o nombre del PUC…"
                                        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 pr-8" />
                                    <button type="button" x-show="rev.query" @click="clear('rev', 'revenue_account_id')"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    </button>
                                    <div x-show="rev.open"
                                        class="absolute z-50 left-0 right-0 mt-1 bg-white border border-cream-200 rounded-xl shadow-lg max-h-52 overflow-y-auto">
                                        <template x-for="acc in rev.results" :key="acc.id">
                                            <button type="button" @mousedown.prevent="pick('rev', 'revenue_account_id', acc)"
                                                class="w-full text-left px-3 py-2 hover:bg-forest-50 border-b border-cream-100 last:border-0 flex items-baseline gap-2">
                                                <span class="font-mono text-xs font-bold text-forest-800 shrink-0" x-text="acc.code"></span>
                                                <span class="text-xs text-slate-600 truncate" x-text="acc.display"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                @error('revenue_account_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                <p class="text-slate-400 text-xs mt-1">Sugerida: 4135 — Comercio al por mayor y al por menor</p>
                            </div>

                            {{-- Costo de ventas --}}
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Costo de ventas</label>
                                <div class="relative">
                                    <input type="text" x-model="cost.query"
                                        @input="filter('cost', cost.query)"
                                        @focus="filter('cost', cost.query)"
                                        @click.outside="cost.open = false"
                                        @keydown.escape="cost.open = false"
                                        placeholder="Escribe código o nombre del PUC…"
                                        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 pr-8" />
                                    <button type="button" x-show="cost.query" @click="clear('cost', 'cogs_account_id')"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-500">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                                    </button>
                                    <div x-show="cost.open"
                                        class="absolute z-50 left-0 right-0 mt-1 bg-white border border-cream-200 rounded-xl shadow-lg max-h-52 overflow-y-auto">
                                        <template x-for="acc in cost.results" :key="acc.id">
                                            <button type="button" @mousedown.prevent="pick('cost', 'cogs_account_id', acc)"
                                                class="w-full text-left px-3 py-2 hover:bg-forest-50 border-b border-cream-100 last:border-0 flex items-baseline gap-2">
                                                <span class="font-mono text-xs font-bold text-forest-800 shrink-0" x-text="acc.code"></span>
                                                <span class="text-xs text-slate-600 truncate" x-text="acc.display"></span>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                @error('cogs_account_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                <p class="text-slate-400 text-xs mt-1">Sugerida: 6135 — Comercio al por mayor y al por menor</p>
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
