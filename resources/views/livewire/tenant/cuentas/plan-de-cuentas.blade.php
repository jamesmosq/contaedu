<div>

    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Maestros contables</p>
                <h1 class="font-display text-2xl font-bold text-white">Plan de Cuentas</h1>
                <p class="text-forest-300 text-sm mt-1">PUC colombiano — Clases 1 a 6</p>
            </div>
            @if(! session('audit_mode') && ! session('reference_mode'))
                <button wire:click="openForm()"
                    class="flex items-center gap-2 px-4 py-2 bg-gold-500 text-forest-950 text-sm font-bold rounded-xl hover:bg-gold-400 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Agregar cuenta
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
                    <input
                        wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Buscar por código o nombre…"
                        class="w-full pl-9 rounded-xl border-cream-200 text-sm shadow-sm focus:ring-forest-500 focus:border-forest-500"
                    />
                </div>
            </div>

            {{-- Tabla de cuentas --}}
            @php
                $clases = [
                    '1' => ['Activo',     'bg-blue-50 text-blue-700 border border-blue-100'],
                    '2' => ['Pasivo',     'bg-red-50 text-red-700 border border-red-100'],
                    '3' => ['Patrimonio', 'bg-violet-50 text-violet-700 border border-violet-100'],
                    '4' => ['Ingreso',    'bg-green-50 text-green-700 border border-green-100'],
                    '5' => ['Gasto',      'bg-orange-50 text-orange-700 border border-orange-100'],
                    '6' => ['Costo',      'bg-gold-50 text-gold-700 border border-gold-100'],
                    '9' => ['Orden',      'bg-slate-100 text-slate-600 border border-slate-200'],
                ];
            @endphp

            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-forest-950 border-b border-forest-800">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide">Código</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide">Nombre</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide hidden sm:table-cell">Tipo</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-forest-300 uppercase tracking-wide hidden md:table-cell">Naturaleza</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-cream-100">
                        @forelse($accounts->flatten()->sortBy('code') as $account)
                            @php
                                $prefix    = substr($account->code, 0, 1);
                                $badgeInfo = $clases[$prefix] ?? ['', 'bg-slate-100 text-slate-600 border border-slate-200'];
                                $levelPad  = str_repeat('·· ', $account->level - 1);
                            @endphp
                            <tr wire:key="account-{{ $account->id }}" class="hover:bg-cream-50 transition">
                                <td class="px-6 py-2.5 font-mono text-sm text-slate-600">
                                    {{ $account->code }}
                                </td>
                                <td class="px-6 py-2.5 text-sm text-slate-700">
                                    @if($account->level > 1)
                                        <span class="text-slate-300 select-none mr-1">{{ $levelPad }}</span>
                                    @endif
                                    {{ $account->name }}
                                </td>
                                <td class="px-6 py-2.5 hidden sm:table-cell">
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-medium {{ $badgeInfo[1] }}">
                                        {{ $badgeInfo[0] ?: ucfirst($account->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-2.5 text-xs text-slate-500 capitalize hidden md:table-cell">
                                    {{ $account->nature }}
                                </td>
                                <td class="px-6 py-2.5 text-right">
                                    @if(! session('audit_mode') && ! session('reference_mode') && $account->level < 5)
                                        <button wire:click="openForm({{ $account->id }})"
                                            class="text-xs text-forest-600 hover:text-forest-800 font-semibold px-2 py-1 rounded-lg hover:bg-forest-50 transition">
                                            + Sub
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-slate-400 text-sm">No hay cuentas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    {{-- ═══ Modal: Nueva cuenta ═══ --}}
    @if($showForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" wire:click.self="cancelForm">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="px-6 py-5 border-b border-cream-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-800">Nueva cuenta auxiliar</h3>
                    <button wire:click="cancelForm" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition text-sm">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Cuenta padre</label>
                        <select wire:model.live="parent_id" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                            <option value="">— Sin padre (clase) —</option>
                            @foreach($parentAccounts as $pa)
                                <option value="{{ $pa->id }}">{{ $pa->code }} — {{ $pa->name }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Código</label>
                            <input wire:model="code" type="text" placeholder="ej: 110505"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Naturaleza</label>
                            <select wire:model="nature" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                <option value="debito">Débito</option>
                                <option value="credito">Crédito</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre</label>
                        <input wire:model="name" type="text" placeholder="Nombre de la cuenta"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Tipo</label>
                        <select wire:model="type" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
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
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="cancelForm" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">Guardar cuenta</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
