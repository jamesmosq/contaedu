<div>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800">Productos</h2>
        <p class="text-sm text-slate-500 mt-0.5">Inventario con cuentas contables vinculadas</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Buscador + acción --}}
            <div class="flex items-center justify-between mb-5">
                <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar producto..."
                    class="w-72 rounded-lg border-slate-200 text-sm shadow-sm focus:ring-brand-500 focus:border-brand-500" />
                @if(!session('audit_mode'))
                <button wire:click="openCreate" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                    + Nuevo producto
                </button>
                @endif
            </div>

            {{-- Modal --}}
            @if($showForm && !session('audit_mode'))
                <div class="fixed inset-0 bg-slate-900/50 z-40 flex items-center justify-center p-4" wire:click.self="cancelForm">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-screen overflow-y-auto">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between sticky top-0 bg-white">
                            <h3 class="font-semibold text-slate-800">{{ $editingId ? 'Editar producto' : 'Nuevo producto' }}</h3>
                            <button wire:click="cancelForm" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Código</label>
                                    <input wire:model="code" type="text" placeholder="ej: PROD001" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Unidad</label>
                                    <select wire:model="unit" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                        @foreach($units as $u)
                                            <option value="{{ $u->value }}">{{ $u->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre</label>
                                <input wire:model="name" type="text" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-slate-400">(opcional)</span></label>
                                <textarea wire:model="description" rows="2" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500"></textarea>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Precio venta</label>
                                    <input wire:model="sale_price" type="number" step="0.01" min="0" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                    @error('sale_price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Precio costo</label>
                                    <input wire:model="cost_price" type="number" step="0.01" min="0" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">IVA</label>
                                    <select wire:model="tax_rate" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                        @foreach($taxRates as $t)
                                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="border-t border-slate-100 pt-4">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3">Cuentas contables</p>
                                <div class="space-y-3">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Inventario (Activo)</label>
                                        <select wire:model="inventory_account_id" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                            <option value="">— Sin cuenta —</option>
                                            @foreach($accounts->where('type', 'activo') as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Ingresos (Venta)</label>
                                        <select wire:model="revenue_account_id" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                            <option value="">— Sin cuenta —</option>
                                            @foreach($accounts->where('type', 'ingreso') as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Costo de ventas</label>
                                        <select wire:model="cogs_account_id" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                            <option value="">— Sin cuenta —</option>
                                            @foreach($accounts->where('type', 'costo') as $acc)
                                                <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 sticky bottom-0 bg-white">
                            <button wire:click="cancelForm" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 transition">Cancelar</button>
                            <button wire:click="save" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                                <span wire:loading.remove wire:target="save">{{ $editingId ? 'Actualizar' : 'Guardar' }}</span>
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
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Código</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Producto</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Unidad</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Precio venta</th>
                            <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Costo</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">IVA</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($products as $product)
                            <tr wire:key="product-{{ $product->id }}" class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3 font-mono text-xs text-slate-500">{{ $product->code }}</td>
                                <td class="px-6 py-3 font-medium text-slate-700">{{ $product->name }}</td>
                                <td class="px-6 py-3 text-xs text-slate-500">{{ $product->unit->label() }}</td>
                                <td class="px-6 py-3 text-right font-mono text-sm text-slate-700">$ {{ number_format($product->sale_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-right font-mono text-sm text-slate-500">$ {{ number_format($product->cost_price, 0, ',', '.') }}</td>
                                <td class="px-6 py-3">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $product->tax_rate->value == 0 ? 'bg-slate-100 text-slate-500' : 'bg-green-100 text-green-700' }}">
                                        {{ $product->tax_rate->value }}%
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    @if(!session('audit_mode'))
                                    <div class="flex items-center justify-end gap-3">
                                        <button wire:click="openEdit({{ $product->id }})" class="text-xs text-brand-600 hover:text-brand-800 font-medium transition">Editar</button>
                                        <button x-on:click="confirmAction('¿Eliminar este producto?', () => $wire.delete({{ $product->id }}), {danger: true, confirmText: 'Sí, eliminar'})" class="text-xs text-red-500 hover:text-red-700 font-medium transition">Eliminar</button>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-10 text-center text-slate-400">
                                    No hay productos registrados.
                                    <button wire:click="openCreate" class="ml-2 text-brand-600 hover:underline">Crear el primero</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($products->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">{{ $products->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</div>
