<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Plan de Cuentas</h2>
                <p class="text-sm text-slate-500 mt-0.5">PUC colombiano — Clases 1 a 6</p>
            </div>
            <button wire:click="openForm()" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                + Agregar cuenta
            </button>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Buscador --}}
            <div class="mb-5">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Buscar por código o nombre..."
                    class="w-full sm:w-80 rounded-lg border-slate-200 text-sm shadow-sm focus:ring-brand-500 focus:border-brand-500"
                />
            </div>

            {{-- Modal agregar cuenta --}}
            @if($showForm)
                <div class="fixed inset-0 bg-slate-900/50 z-40 flex items-center justify-center" wire:click.self="cancelForm">
                    <div class="bg-white rounded-xl shadow-xl w-full max-w-md mx-4 z-50">
                        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                            <h3 class="font-semibold text-slate-800">Nueva cuenta auxiliar</h3>
                            <button wire:click="cancelForm" class="text-slate-400 hover:text-slate-600">✕</button>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Cuenta padre</label>
                                <select wire:model.live="parent_id" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                    <option value="">— Sin padre (clase) —</option>
                                    @foreach($parentAccounts as $pa)
                                        <option value="{{ $pa->id }}">{{ $pa->code }} - {{ $pa->name }}</option>
                                    @endforeach
                                </select>
                                @error('parent_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Código</label>
                                    <input wire:model="code" type="text" placeholder="ej: 110505" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">Naturaleza</label>
                                    <select wire:model="nature" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                        <option value="debito">Débito</option>
                                        <option value="credito">Crédito</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nombre</label>
                                <input wire:model="name" type="text" placeholder="Nombre de la cuenta" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                                <select wire:model="type" class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
                                    <option value="activo">Activo</option>
                                    <option value="pasivo">Pasivo</option>
                                    <option value="patrimonio">Patrimonio</option>
                                    <option value="ingreso">Ingreso</option>
                                    <option value="costo">Costo</option>
                                    <option value="gasto">Gasto</option>
                                    <option value="orden">Orden</option>
                                </select>
                            </div>
                        </div>
                        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                            <button wire:click="cancelForm" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 transition">Cancelar</button>
                            <button wire:click="save" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                                <span wire:loading.remove wire:target="save">Guardar cuenta</span>
                                <span wire:loading wire:target="save">Guardando...</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Tabla de cuentas --}}
            @php
                $clases = [
                    '1' => ['ACTIVO', 'bg-blue-50 text-blue-700'],
                    '2' => ['PASIVO', 'bg-red-50 text-red-700'],
                    '3' => ['PATRIMONIO', 'bg-purple-50 text-purple-700'],
                    '4' => ['INGRESOS', 'bg-green-50 text-green-700'],
                    '5' => ['GASTOS', 'bg-orange-50 text-orange-700'],
                    '6' => ['COSTOS', 'bg-yellow-50 text-yellow-700'],
                ];
            @endphp

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100">
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Código</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                            <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Naturaleza</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($accounts->flatten()->sortBy('code') as $account)
                            @php
                                $prefix = substr($account->code, 0, 1);
                                $badgeInfo = $clases[$prefix] ?? ['', 'bg-slate-100 text-slate-600'];
                                $indent = ($account->level - 1) * 20;
                                $isBold = $account->level <= 2;
                            @endphp
                            <tr wire:key="account-{{ $account->id }}" class="hover:bg-slate-50 transition">
                                <td class="px-6 py-2.5 font-mono text-xs {{ $isBold ? 'font-bold text-slate-700' : 'text-slate-500' }}">
                                    {{ $account->code }}
                                </td>
                                <td class="px-6 py-2.5" style="padding-left: {{ 24 + $indent }}px">
                                    <span class="{{ $isBold ? 'font-semibold text-slate-700' : 'text-slate-600' }}">
                                        {{ $account->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-2.5">
                                    <span class="px-2 py-0.5 rounded text-xs font-medium {{ $badgeInfo[1] }}">
                                        {{ ucfirst($account->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-2.5 text-xs text-slate-500 capitalize">{{ $account->nature }}</td>
                                <td class="px-6 py-2.5 text-right">
                                    @if($account->level < 4)
                                        <button wire:click="openForm({{ $account->id }})" class="text-xs text-brand-600 hover:text-brand-800 font-medium transition">
                                            + Sub
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-8 text-center text-slate-400">No hay cuentas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>
