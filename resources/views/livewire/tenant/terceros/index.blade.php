<div>
    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800">Terceros</h2>
        <p class="text-sm text-slate-500 mt-0.5">Clientes y proveedores de la empresa</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Barra de acciones --}}
            <div class="flex items-center justify-between mb-5">
                <div class="flex flex-wrap gap-3">
                    <input wire:model.live.debounce.300ms="search" type="text" placeholder="Buscar por nombre o documento..."
                        class="rounded-lg border-slate-200 text-sm shadow-sm focus:ring-brand-500 focus:border-brand-500 w-72" />
                    <select wire:model.live="filterType" class="rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                        <option value="">Todos los tipos</option>
                        <option value="cliente">Clientes</option>
                        <option value="proveedor">Proveedores</option>
                        <option value="ambos">Ambos</option>
                    </select>
                </div>
                @if(!session('audit_mode'))
                <button wire:click="openCreate" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                    + Nuevo tercero
                </button>
                @endif
            </div>

            {{-- Modal --}}
            @if($showForm && !session('audit_mode'))
                <div class="fixed inset-0 bg-slate-900/50 z-40 flex items-center justify-center p-4" wire:click.self="cancelForm">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-lg">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="font-semibold text-slate-800">{{ $editingId ? 'Editar tercero' : 'Nuevo tercero' }}</h3>
                            <button wire:click="cancelForm" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tipo documento</label>
                                    <select wire:model="document_type" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                        <option value="nit">NIT</option>
                                        <option value="cc">Cédula (CC)</option>
                                        <option value="ce">Cédula extranjería</option>
                                        <option value="pasaporte">Pasaporte</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Número documento</label>
                                    <input wire:model="document" type="text" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                    @error('document') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Razón social / Nombre</label>
                                <input wire:model="name" type="text" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                                    <select wire:model="type" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                        <option value="cliente">Cliente</option>
                                        <option value="proveedor">Proveedor</option>
                                        <option value="ambos">Cliente y Proveedor</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Régimen</label>
                                    <select wire:model="regimen" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                        <option value="simplificado">Simplificado</option>
                                        <option value="comun">Común</option>
                                    </select>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                                    <input wire:model="phone" type="text" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Correo</label>
                                    <input wire:model="email" type="email" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                    @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Dirección</label>
                                <input wire:model="address" type="text" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
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
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tercero</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Documento</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Régimen</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Contacto</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($thirds as $third)
                            <tr wire:key="third-{{ $third->id }}" class="hover:bg-slate-50 transition">
                                <td class="px-6 py-3 font-medium text-slate-700">{{ $third->name }}</td>
                                <td class="px-6 py-3 text-slate-500 font-mono text-xs uppercase">{{ $third->document_type }} {{ $third->document }}</td>
                                <td class="px-6 py-3">
                                    @php
                                        $colors = ['cliente' => 'bg-blue-100 text-blue-700', 'proveedor' => 'bg-purple-100 text-purple-700', 'ambos' => 'bg-amber-100 text-amber-700'];
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $colors[$third->type->value] ?? 'bg-slate-100 text-slate-600' }}">
                                        {{ $third->type->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-3 text-xs text-slate-500 capitalize">{{ $third->regimen }}</td>
                                <td class="px-6 py-3 text-xs text-slate-500">{{ $third->phone }} {{ $third->email }}</td>
                                <td class="px-6 py-3 text-right">
                                    @if(!session('audit_mode'))
                                    <div class="flex items-center justify-end gap-3">
                                        <button wire:click="openEdit({{ $third->id }})" class="text-xs text-brand-600 hover:text-brand-800 font-medium transition">Editar</button>
                                        <button x-on:click="confirmAction('¿Eliminar este tercero?', () => $wire.delete({{ $third->id }}), {danger: true, confirmText: 'Sí, eliminar'})" class="text-xs text-red-500 hover:text-red-700 font-medium transition">Eliminar</button>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-slate-400">
                                    No hay terceros registrados.
                                    <button wire:click="openCreate" class="ml-2 text-brand-600 hover:underline">Crear el primero</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($thirds->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">{{ $thirds->links() }}</div>
                @endif
            </div>

        </div>
    </div>
</div>
