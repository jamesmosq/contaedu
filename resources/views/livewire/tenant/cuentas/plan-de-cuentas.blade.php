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

            {{-- Nota pedagógica --}}
            <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 text-sm text-sky-800 mb-6">
                <p class="font-semibold mb-1">¿Qué es el Plan Único de Cuentas (PUC) colombiano?</p>
                <p>El PUC es el catálogo oficial de cuentas contables definido por la Superintendencia de Sociedades para Colombia. Está organizado en <strong>9 clases</strong>: Activos (1), Pasivos (2), Patrimonio (3), Ingresos (4), Gastos (5), Costos (6), Costos de producción (7), Cuentas de orden (8 y 9). Cada cuenta tiene un código único de hasta 8 dígitos. Puedes agregar subcuentas personalizadas para adaptar el PUC a tu empresa, pero sin modificar los códigos oficiales.</p>
            </div>

            {{-- Buscador + filtro --}}
            <div class="mb-6 flex flex-col sm:flex-row sm:items-center gap-3">
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

                <button wire:click="$toggle('soloPersonalizadas')"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl border text-sm font-medium transition shrink-0
                        {{ $soloPersonalizadas
                            ? 'bg-forest-800 text-white border-forest-800'
                            : 'bg-white text-slate-600 border-cream-200 hover:border-forest-400 hover:text-forest-700' }}">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                    </svg>
                    Mis cuentas
                </button>
            </div>

            {{-- Tabla de cuentas --}}
            @php
                $clasesBadge = [
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
                                $badgeInfo = $clasesBadge[$prefix] ?? ['', 'bg-slate-100 text-slate-600 border border-slate-200'];
                                $indent    = match($account->level) {
                                    1 => 'ml-0',
                                    2 => 'ml-5',
                                    3 => 'ml-10',
                                    default => 'ml-14',
                                };
                                $nameStyle = 'text-sm text-slate-700';
                                $codeStyle = 'font-mono text-sm text-slate-600';
                                $rowBg     = '';
                            @endphp
                            <tr wire:key="account-{{ $account->id }}" class="hover:bg-cream-50 transition {{ $rowBg }}">
                                <td class="px-6 py-2 {{ $codeStyle }}">
                                    {{ $account->code }}
                                </td>
                                <td class="px-6 py-2">
                                    <span class="{{ $indent }} {{ $nameStyle }}">{{ ucwords(strtolower($account->name)) }}</span>
                                    @if($account->is_custom)
                                        <span class="ml-2 px-1.5 py-0.5 bg-gold-50 text-gold-700 border border-gold-200 text-xs font-medium rounded-md">mía</span>
                                    @endif
                                </td>
                                <td class="px-6 py-2 hidden sm:table-cell">
                                    <span class="px-2 py-0.5 rounded-lg text-xs font-medium {{ $badgeInfo[1] }}">
                                        {{ $badgeInfo[0] ?: ucfirst($account->type) }}
                                    </span>
                                </td>
                                <td class="px-6 py-2 text-xs text-slate-400 capitalize hidden md:table-cell">
                                    {{ $account->nature }}
                                </td>
                                <td class="px-6 py-2 text-right">
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

    {{-- ═══ Modal: Nueva subcuenta (guiado) ═══ --}}
    @if($showForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-start justify-center p-4 overflow-y-auto">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg my-8">

                {{-- Header --}}
                <div class="px-6 py-5 border-b border-cream-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-800">Nueva subcuenta auxiliar</h3>
                    <button wire:click="cancelForm" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5">

                    {{-- Breadcrumb de navegación --}}
                    <div class="flex items-center gap-1.5 text-xs text-slate-400 mb-5 flex-wrap">
                        <button wire:click="volverAPaso('clase')" type="button"
                            class="font-medium transition {{ $selectedClase ? 'text-forest-700 hover:text-forest-900' : 'text-forest-700 cursor-default' }}">
                            Clase
                        </button>
                        @if($selectedClase)
                            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            <button wire:click="volverAPaso('grupo')" type="button"
                                class="font-medium transition {{ $selectedGrupo ? 'text-forest-700 hover:text-forest-900' : 'text-forest-700 cursor-default' }}">
                                {{ $selectedClase }} Grupo
                            </button>
                        @endif
                        @if($selectedGrupo)
                            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            <button wire:click="volverAPaso('cuenta')" type="button"
                                class="font-medium transition {{ $selectedCuenta ? 'text-forest-700 hover:text-forest-900' : 'text-forest-700 cursor-default' }}">
                                {{ $selectedGrupo }} Cuenta
                            </button>
                        @endif
                        @if($selectedCuenta)
                            <svg class="w-3 h-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                            </svg>
                            <span class="font-semibold text-forest-800">{{ $selectedCuenta }} Subcuenta</span>
                        @endif
                    </div>

                    {{-- PASO 1: Seleccionar clase --}}
                    @if($pasoActual === 'clase')
                        <p class="text-sm text-slate-500 mb-4">Selecciona la clase del PUC a la que pertenece la nueva cuenta:</p>
                        <div class="grid grid-cols-2 gap-2.5">
                            @foreach($clases as $clase)
                                <button wire:click="seleccionarClase('{{ $clase->code }}')" type="button"
                                    class="text-left p-3.5 border border-cream-200 rounded-xl hover:border-forest-400 hover:bg-forest-50 transition group">
                                    <div class="font-bold text-forest-800 text-base group-hover:text-forest-900">{{ $clase->code }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $clase->name }}</div>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- PASO 2: Seleccionar grupo --}}
                    @if($pasoActual === 'grupo')
                        <p class="text-sm text-slate-500 mb-4">Selecciona el grupo dentro de la Clase {{ $selectedClase }}:</p>
                        <div class="flex flex-col gap-1.5 max-h-72 overflow-y-auto">
                            @foreach($grupos as $grupo)
                                <button wire:click="seleccionarGrupo('{{ $grupo->code }}')" type="button"
                                    class="flex items-center gap-3 text-left px-4 py-2.5 border border-cream-200 rounded-xl hover:border-forest-400 hover:bg-forest-50 transition">
                                    <span class="font-mono font-bold text-forest-800 min-w-[28px]">{{ $grupo->code }}</span>
                                    <span class="text-sm text-slate-700">{{ $grupo->name }}</span>
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- PASO 3: Seleccionar cuenta --}}
                    @if($pasoActual === 'cuenta')
                        <p class="text-sm text-slate-500 mb-4">Selecciona la cuenta (4 dígitos) bajo el grupo {{ $selectedGrupo }}:</p>
                        <div class="flex flex-col gap-1.5 max-h-72 overflow-y-auto">
                            @foreach($cuentas as $cuenta)
                                <button wire:click="seleccionarCuenta('{{ $cuenta->code }}')" type="button"
                                    class="flex items-center gap-3 text-left px-4 py-2.5 border border-cream-200 rounded-xl hover:border-forest-400 hover:bg-forest-50 transition">
                                    <span class="font-mono font-bold text-forest-800 min-w-[40px]">{{ $cuenta->code }}</span>
                                    <span class="text-sm text-slate-700 flex-1">{{ $cuenta->name }}</span>
                                    @if($cuenta->descripcion)
                                        <span class="shrink-0 px-1.5 py-0.5 bg-green-50 text-green-700 text-xs font-medium rounded-md border border-green-100">con guía</span>
                                    @endif
                                </button>
                            @endforeach
                        </div>
                    @endif

                    {{-- PASO 4: Definir subcuenta --}}
                    @if($pasoActual === 'subcuenta')
                        <p class="text-sm text-slate-500 mb-4">Define la nueva subcuenta bajo <strong class="text-slate-700">{{ $selectedCuenta }}</strong>:</p>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Código de la subcuenta</label>
                                <input wire:model="code" type="text"
                                    placeholder="{{ $codigoSugerido ?: 'ej: 110520' }}"
                                    class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 font-mono" />
                                @if($codigoSugerido)
                                    <p class="text-slate-400 text-xs mt-1">Sugerido: {{ $codigoSugerido }} — puedes modificarlo si necesitas otro</p>
                                @endif
                                @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre de la subcuenta</label>
                                <input wire:model="name" type="text" placeholder="ej: Caja sucursal norte"
                                    class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>

                            <div class="flex items-center gap-2 px-4 py-3 bg-forest-50 border border-forest-100 rounded-xl text-sm text-slate-600">
                                <svg class="w-4 h-4 text-forest-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                                </svg>
                                <span><strong>Tipo:</strong> {{ ucfirst($type) }} · <strong>Naturaleza:</strong> {{ ucfirst($nature) }} <span class="text-slate-400">(heredados del padre)</span></span>
                            </div>
                        </div>
                    @endif

                </div>

                {{-- Footer --}}
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="cancelForm" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">Cancelar</button>
                    @if($pasoActual === 'subcuenta')
                        <button wire:click="save" wire:loading.attr="disabled"
                            class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="save">Guardar cuenta</span>
                            <span wire:loading wire:target="save">Guardando…</span>
                        </button>
                    @endif
                </div>

            </div>
        </div>
    @endif

</div>
