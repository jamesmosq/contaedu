<div>

    {{-- ── Hero ────────────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Panel Docente</p>
            <h1 class="font-display text-2xl font-bold text-white">Enviar dinero a estudiantes</h1>
            <p class="text-forest-300 text-sm mt-1">Deposita capital en las cuentas bancarias de tus estudiantes</p>
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto space-y-8">

            {{-- ── Tabs modo ─────────────────────────────────────────────────── --}}
            <div class="flex gap-1 bg-slate-100 p-1 rounded-xl w-fit">
                <button wire:click="$set('mode', 'grupal')"
                    class="px-5 py-2 text-sm font-semibold rounded-lg transition-all
                        {{ $mode === 'grupal' ? 'bg-white text-forest-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Envío grupal
                </button>
                <button wire:click="$set('mode', 'individual')"
                    class="px-5 py-2 text-sm font-semibold rounded-lg transition-all
                        {{ $mode === 'individual' ? 'bg-white text-forest-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                    Envío individual
                </button>
            </div>

            {{-- ── Paso 1: Selección de destinatario ─────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-6">
                <h2 class="text-sm font-semibold text-slate-700 mb-4">
                    {{ $mode === 'grupal' ? 'Selecciona el grupo' : 'Busca al estudiante' }}
                </h2>

                @if($mode === 'grupal')
                    @if($groups->isEmpty())
                        <p class="text-sm text-slate-400">No tienes grupos creados aún.</p>
                    @else
                        <div class="flex flex-col sm:flex-row gap-3">
                            <select wire:model.live="selectedGroupId"
                                class="flex-1 rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                                <option value="0">Selecciona un grupo…</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group->id }}">{{ $group->name }} — {{ $group->period }}</option>
                                @endforeach
                            </select>
                            <button wire:click="loadGroupPreview" wire:loading.attr="disabled"
                                wire:target="loadGroupPreview"
                                class="flex items-center gap-2 px-5 py-2.5 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50 shrink-0">
                                <span wire:loading.remove wire:target="loadGroupPreview">Ver quiénes recibirán</span>
                                <span wire:loading wire:target="loadGroupPreview" class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                    </svg>
                                    Consultando…
                                </span>
                            </button>
                        </div>
                    @endif

                @else
                    <div class="flex flex-col sm:flex-row gap-3">
                        <div class="flex-1">
                            <input wire:model="searchCedula" type="text"
                                placeholder="Cédula del estudiante (ej: cc1023456789)"
                                wire:keydown.enter="searchIndividual"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        </div>
                        <button wire:click="searchIndividual" wire:loading.attr="disabled"
                            wire:target="searchIndividual"
                            class="flex items-center gap-2 px-5 py-2.5 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50 shrink-0">
                            <span wire:loading.remove wire:target="searchIndividual">Buscar</span>
                            <span wire:loading wire:target="searchIndividual" class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                                </svg>
                                Buscando…
                            </span>
                        </button>
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Solo puedes enviar dinero a estudiantes de tus grupos.</p>
                @endif
            </div>

            {{-- ── Paso 2: Vista previa de destinatarios ──────────────────────── --}}
            @if($showPreview && count($previewList) > 0)
                @php
                    $willReceive = collect($previewList)->where('hasAccount', true)->count();
                    $willSkip    = collect($previewList)->where('hasAccount', false)->count();
                @endphp

                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between gap-4 flex-wrap">
                        <h2 class="text-sm font-semibold text-slate-700">
                            Vista previa — {{ count($previewList) }} {{ count($previewList) === 1 ? 'estudiante' : 'estudiantes' }}
                        </h2>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="flex items-center gap-1.5 text-emerald-700 font-medium">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                {{ $willReceive }} {{ $willReceive === 1 ? 'recibirá' : 'recibirán' }}
                            </span>
                            @if($willSkip > 0)
                                <span class="flex items-center gap-1.5 text-slate-400 font-medium">
                                    <span class="w-2 h-2 rounded-full bg-slate-300"></span>
                                    {{ $willSkip }} sin cuenta
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-6 py-3">Estudiante</th>
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Empresa</th>
                                    <th class="text-center text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Estado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($previewList as $item)
                                    <tr class="{{ $item['hasAccount'] ? '' : 'opacity-50' }}">
                                        <td class="px-6 py-3">
                                            <p class="font-medium text-slate-800">{{ $item['name'] }}</p>
                                            <p class="text-xs text-slate-400">{{ $item['id'] }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600">{{ $item['company'] }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($item['hasAccount'])
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-full">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M16.704 4.153a.75.75 0 01.143 1.052l-8 10.5a.75.75 0 01-1.127.075l-4.5-4.5a.75.75 0 011.06-1.06l3.894 3.893 7.48-9.817a.75.75 0 011.05-.143z" clip-rule="evenodd"/>
                                                    </svg>
                                                    Recibirá
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 bg-slate-100 px-2.5 py-1 rounded-full">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                    Sin cuenta
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- ── Paso 3: Monto y descripción ────────────────────────────── --}}
                @if($willReceive > 0)
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-6">
                        <h2 class="text-sm font-semibold text-slate-700 mb-5">Detalle del envío</h2>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">
                                    Monto a enviar
                                    <span class="text-slate-400 font-normal">(en pesos)</span>
                                </label>
                                <div class="relative">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-slate-400 text-sm pointer-events-none">$</span>
                                    <input wire:model="amount" type="number" min="1000" step="1000"
                                        placeholder="0"
                                        class="block w-full pl-7 rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                </div>
                                @error('amount') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                                @if($amount && is_numeric($amount) && $amount >= 1000)
                                    <p class="mt-1.5 text-xs text-slate-400">
                                        Total a distribuir: <span class="font-semibold text-slate-600">${{ number_format((float)$amount * $willReceive, 0, ',', '.') }}</span>
                                        ({{ $willReceive }} {{ $willReceive === 1 ? 'estudiante' : 'estudiantes' }} × ${{ number_format((float)$amount, 0, ',', '.') }})
                                    </p>
                                @endif
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                                <input wire:model="description" type="text" maxlength="200"
                                    placeholder="Descripción del depósito…"
                                    class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                                @error('description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <button wire:click="openConfirm" wire:loading.attr="disabled"
                                class="flex items-center gap-2 px-6 py-2.5 bg-gold-500 text-forest-950 text-sm font-bold rounded-xl hover:bg-gold-400 transition disabled:opacity-50">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                                </svg>
                                Enviar dinero a {{ $willReceive }} {{ $willReceive === 1 ? 'estudiante' : 'estudiantes' }}
                            </button>
                        </div>
                    </div>
                @endif
            @endif

            {{-- ── Historial ──────────────────────────────────────────────────── --}}
            @if($history->isNotEmpty())
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    <div class="px-6 py-4 border-b border-cream-100">
                        <h2 class="text-sm font-semibold text-slate-700">Últimos envíos</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 border-b border-slate-100">
                                <tr>
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-6 py-3">Fecha</th>
                                    <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Destino</th>
                                    <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Monto</th>
                                    <th class="text-center text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Tipo</th>
                                    <th class="text-center text-xs font-semibold text-slate-500 uppercase tracking-wide px-4 py-3">Resultado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-50">
                                @foreach($history as $transfer)
                                    <tr class="hover:bg-slate-50/50 transition">
                                        <td class="px-6 py-3">
                                            <p class="font-medium text-slate-800">{{ $transfer->created_at->format('d/m/Y') }}</p>
                                            <p class="text-xs text-slate-400">{{ $transfer->created_at->format('H:i') }}</p>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-slate-800">{{ $transfer->target_name }}</p>
                                            <p class="text-xs text-slate-400 truncate max-w-48">{{ $transfer->description }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                            ${{ number_format((float)$transfer->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            @if($transfer->mode === 'grupal')
                                                <span class="inline-flex items-center text-xs font-medium text-forest-700 bg-forest-50 px-2.5 py-1 rounded-full">Grupal</span>
                                            @else
                                                <span class="inline-flex items-center text-xs font-medium text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full">Individual</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <p class="text-xs text-emerald-700 font-medium">{{ $transfer->students_reached }} recibieron</p>
                                            @if($transfer->students_skipped > 0)
                                                <p class="text-xs text-slate-400">{{ $transfer->students_skipped }} omitidos</p>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

        </div>
    </div>

    {{-- ═══ Modal de confirmación ═══════════════════════════════════════════════ --}}
    @if($showConfirm)
        @php
            $willReceive = collect($previewList)->where('hasAccount', true)->count();
        @endphp
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="px-6 py-5 border-b border-cream-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-800">Confirmar envío</h3>
                    <button wire:click="$set('showConfirm', false)"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition text-sm">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div class="bg-gold-50 rounded-xl p-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Monto por estudiante</span>
                            <span class="font-bold text-slate-800">${{ number_format((float)$amount, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-500">Estudiantes que recibirán</span>
                            <span class="font-semibold text-slate-800">{{ $willReceive }}</span>
                        </div>
                        <div class="border-t border-gold-200 pt-2 flex justify-between text-sm">
                            <span class="font-semibold text-slate-700">Total distribuido</span>
                            <span class="font-bold text-forest-800 text-base">${{ number_format((float)$amount * $willReceive, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="text-sm">
                        <span class="text-slate-500">Descripción: </span>
                        <span class="font-medium text-slate-700">{{ $description }}</span>
                    </div>
                    <p class="text-xs text-slate-400">
                        El dinero se registrará como una consignación en la cuenta principal de cada estudiante
                        y generará el asiento contable correspondiente.
                    </p>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showConfirm', false)"
                        class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                    <button wire:click="enviar" wire:loading.attr="disabled"
                        class="px-5 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="enviar">Confirmar envío</span>
                        <span wire:loading wire:target="enviar" class="flex items-center gap-1.5">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            Enviando…
                        </span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
