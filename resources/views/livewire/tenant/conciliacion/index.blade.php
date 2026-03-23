<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Conciliación bancaria</h2>
                <p class="text-sm text-slate-500 mt-0.5">Cruza tus libros contables con el extracto bancario</p>
            </div>
            @if(!session('audit_mode'))
                <button wire:click="openNewForm"
                    class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Nueva conciliación
                </button>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Nota educativa --}}
            <div class="bg-sky-50 border border-sky-200 rounded-xl p-4 text-sm text-sky-800">
                <p class="font-semibold mb-1">¿Qué es la conciliación bancaria?</p>
                <p>Es el proceso de comparar el saldo de la cuenta <strong>Bancos (1110)</strong> en tus libros contables contra el <strong>extracto bancario</strong>. Las diferencias se explican por: depósitos en tránsito (registrados en libros pero no aún en el banco), cheques en circulación (pagados en libros pero no cobrados por el beneficiario), y partidas bancarias (cargos o abonos del banco no registrados aún en libros).</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                {{-- Panel izquierdo: lista de conciliaciones --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50">
                            <h3 class="text-sm font-semibold text-slate-700">Conciliaciones</h3>
                        </div>
                        @if($reconciliations->isEmpty())
                            <div class="px-4 py-8 text-center text-sm text-slate-400">
                                No hay conciliaciones aún.
                            </div>
                        @else
                            <ul class="divide-y divide-slate-100">
                                @foreach($reconciliations as $rec)
                                    <li>
                                        <button wire:click="selectReconciliation({{ $rec->id }})"
                                            class="w-full text-left px-4 py-3 hover:bg-slate-50 transition {{ $activeId === $rec->id ? 'bg-brand-50 border-l-4 border-brand-600' : '' }}">
                                            <div class="flex items-center justify-between">
                                                <span class="text-sm font-medium text-slate-800">{{ $rec->account->code }} — {{ $rec->account->name }}</span>
                                                <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                                    {{ $rec->status === 'finalizada' ? 'bg-green-100 text-green-700' : 'bg-amber-100 text-amber-700' }}">
                                                    {{ $rec->status === 'finalizada' ? 'Finalizada' : 'Borrador' }}
                                                </span>
                                            </div>
                                            <div class="text-xs text-slate-500 mt-0.5">
                                                {{ $rec->period_start->format('d/m/Y') }} — {{ $rec->period_end->format('d/m/Y') }}
                                            </div>
                                            <div class="text-xs text-slate-600 mt-1 font-medium">
                                                Extracto: ${{ number_format($rec->statement_balance, 0, ',', '.') }}
                                            </div>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>

                {{-- Panel derecho: conciliación activa --}}
                <div class="lg:col-span-2">
                    @if(!$activeReconciliation)
                        <div class="bg-white rounded-xl border border-dashed border-slate-300 p-12 text-center">
                            <svg class="w-12 h-12 mx-auto text-slate-300 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                            </svg>
                            <p class="text-sm text-slate-500">Selecciona una conciliación o crea una nueva</p>
                        </div>
                    @else
                        @php
                            $rec = $activeReconciliation;
                            $rec->load('items');
                            $bookItems  = $rec->items->where('source', 'libro')->sortBy('date');
                            $bankItems  = $rec->items->where('source', 'banco')->sortBy('date');
                            $isLocked   = $rec->isFinalizada();
                        @endphp

                        <div class="space-y-4">

                            {{-- Encabezado --}}
                            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <h3 class="text-base font-bold text-slate-800">{{ $rec->account->code }} — {{ $rec->account->name }}</h3>
                                        <p class="text-sm text-slate-500">Período: {{ $rec->period_start->format('d/m/Y') }} al {{ $rec->period_end->format('d/m/Y') }}</p>
                                        @if($rec->notes)
                                            <p class="text-xs text-slate-400 mt-0.5">{{ $rec->notes }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <a href="{{ route(session('audit_mode') ? 'teacher.auditoria.conciliacion.pdf' : 'student.conciliacion.pdf', array_merge(session('audit_mode') ? ['tenantId' => session('audit_tenant_id')] : [], ['id' => $rec->id])) }}"
                                            target="_blank"
                                            class="px-3 py-1.5 text-sm font-medium rounded-lg border border-slate-300 text-slate-600 bg-white hover:bg-slate-50 transition flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                                            PDF
                                        </a>
                                        @if(!$isLocked && !session('audit_mode'))
                                            <button wire:click="finalize"
                                                wire:confirm="¿Finalizar y bloquear esta conciliación? Esta acción no se puede deshacer."
                                                class="px-3 py-1.5 text-sm font-medium rounded-lg border transition
                                                    {{ $rec->isBalanced() ? 'bg-green-600 text-white hover:bg-green-700 border-green-600' : 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed' }}">
                                                {{ $rec->isBalanced() ? 'Finalizar' : 'Sin cuadrar' }}
                                            </button>
                                        @elseif($isLocked)
                                            <span class="px-3 py-1.5 text-sm font-semibold rounded-lg bg-green-100 text-green-700">
                                                ✓ Finalizada
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Resumen de cuadre --}}
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                                <div class="bg-white rounded-xl border border-slate-200 p-3 text-center">
                                    <p class="text-xs text-slate-500 mb-1">Saldo extracto</p>
                                    <p class="text-sm font-bold text-slate-800">${{ number_format($rec->statement_balance, 0, ',', '.') }}</p>
                                </div>
                                <div class="bg-white rounded-xl border border-slate-200 p-3 text-center">
                                    <p class="text-xs text-slate-500 mb-1">Saldo libros</p>
                                    <p class="text-sm font-bold text-slate-800">${{ number_format($rec->bookBalance(), 0, ',', '.') }}</p>
                                </div>
                                <div class="bg-white rounded-xl border border-slate-200 p-3 text-center">
                                    <p class="text-xs text-slate-500 mb-1">Extracto ajustado</p>
                                    <p class="text-sm font-bold text-slate-800">${{ number_format($rec->adjustedStatementBalance(), 0, ',', '.') }}</p>
                                </div>
                                <div class="rounded-xl border p-3 text-center
                                    {{ $rec->isBalanced() ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200' }}">
                                    <p class="text-xs {{ $rec->isBalanced() ? 'text-green-600' : 'text-red-500' }} mb-1">Diferencia</p>
                                    <p class="text-sm font-bold {{ $rec->isBalanced() ? 'text-green-700' : 'text-red-700' }}">
                                        ${{ number_format(abs($rec->difference()), 0, ',', '.') }}
                                        {{ $rec->isBalanced() ? '✓' : '' }}
                                    </p>
                                </div>
                            </div>

                            {{-- Fórmula educativa --}}
                            <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 text-xs text-amber-800 font-mono">
                                <span class="font-semibold">Extracto ajustado</span> = Saldo extracto (${{ number_format($rec->statement_balance, 0, ',', '.') }})
                                + Depósitos en tránsito (${{ number_format($rec->depositsInTransit(), 0, ',', '.') }})
                                − Cheques en circulación (${{ number_format($rec->outstandingChecks(), 0, ',', '.') }})
                                +/− Ajustes banco (${{ number_format(abs($rec->bankAdjustments()), 0, ',', '.') }})
                                = <strong>${{ number_format($rec->adjustedStatementBalance(), 0, ',', '.') }}</strong>
                            </div>

                            {{-- Movimientos del libro --}}
                            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-700">Movimientos en libros</h4>
                                        <p class="text-xs text-slate-400">Marca los que aparecen en el extracto bancario (ya cruzados)</p>
                                    </div>
                                    <span class="text-xs text-slate-500">
                                        {{ $bookItems->where('reconciled', true)->count() }} / {{ $bookItems->count() }} cruzados
                                    </span>
                                </div>
                                @if($bookItems->isEmpty())
                                    <div class="px-4 py-6 text-center text-sm text-slate-400">
                                        No hay movimientos en libros para este período y cuenta.
                                    </div>
                                @else
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead>
                                                <tr class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                                    <th class="px-4 py-2 text-left w-10">✓</th>
                                                    <th class="px-4 py-2 text-left">Fecha</th>
                                                    <th class="px-4 py-2 text-left">Descripción</th>
                                                    <th class="px-4 py-2 text-right">Débito</th>
                                                    <th class="px-4 py-2 text-right">Crédito</th>
                                                    <th class="px-4 py-2 text-center">Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach($bookItems as $item)
                                                    <tr class="{{ $item->reconciled ? 'bg-green-50' : 'hover:bg-slate-50' }} transition">
                                                        <td class="px-4 py-2">
                                                            @if(!$isLocked && !session('audit_mode'))
                                                                <input type="checkbox"
                                                                    wire:click="toggleReconciled({{ $item->id }})"
                                                                    {{ $item->reconciled ? 'checked' : '' }}
                                                                    class="rounded border-slate-300 text-brand-600 cursor-pointer">
                                                            @else
                                                                <span class="{{ $item->reconciled ? 'text-green-600' : 'text-slate-300' }}">
                                                                    {{ $item->reconciled ? '✓' : '○' }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-2 text-slate-600 whitespace-nowrap">{{ $item->date->format('d/m/Y') }}</td>
                                                        <td class="px-4 py-2 text-slate-700 max-w-xs truncate">{{ $item->description }}</td>
                                                        <td class="px-4 py-2 text-right font-mono {{ $item->debit > 0 ? 'text-green-700 font-medium' : 'text-slate-300' }}">
                                                            {{ $item->debit > 0 ? '$'.number_format($item->debit, 0, ',', '.') : '—' }}
                                                        </td>
                                                        <td class="px-4 py-2 text-right font-mono {{ $item->credit > 0 ? 'text-red-600 font-medium' : 'text-slate-300' }}">
                                                            {{ $item->credit > 0 ? '$'.number_format($item->credit, 0, ',', '.') : '—' }}
                                                        </td>
                                                        <td class="px-4 py-2 text-center">
                                                            @if($item->reconciled)
                                                                <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700 font-medium">Cruzado</span>
                                                            @elseif($item->debit > 0)
                                                                <span class="text-xs px-2 py-0.5 rounded-full bg-blue-100 text-blue-700">En tránsito</span>
                                                            @else
                                                                <span class="text-xs px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">En circulación</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Partidas bancarias (del extracto, no en libros) --}}
                            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                                    <div>
                                        <h4 class="text-sm font-semibold text-slate-700">Partidas del extracto no en libros</h4>
                                        <p class="text-xs text-slate-400">Cargos bancarios, intereses, notas débito/crédito del banco</p>
                                    </div>
                                    @if(!$isLocked && !session('audit_mode'))
                                        <button wire:click="openBankItemForm"
                                            class="text-xs px-3 py-1.5 bg-slate-800 text-white rounded-lg hover:bg-slate-700 transition">
                                            + Agregar
                                        </button>
                                    @endif
                                </div>

                                @if($bankItems->isEmpty())
                                    <div class="px-4 py-4 text-center text-sm text-slate-400">
                                        Sin partidas bancarias adicionales.
                                    </div>
                                @else
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full text-sm">
                                            <thead>
                                                <tr class="bg-slate-50 text-xs font-semibold text-slate-500 uppercase tracking-wide">
                                                    <th class="px-4 py-2 text-left">Fecha</th>
                                                    <th class="px-4 py-2 text-left">Descripción</th>
                                                    <th class="px-4 py-2 text-right">Débito</th>
                                                    <th class="px-4 py-2 text-right">Crédito</th>
                                                    @if(!$isLocked && !session('audit_mode'))
                                                        <th class="px-4 py-2"></th>
                                                    @endif
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach($bankItems as $item)
                                                    <tr class="hover:bg-slate-50">
                                                        <td class="px-4 py-2 text-slate-600 whitespace-nowrap">{{ $item->date->format('d/m/Y') }}</td>
                                                        <td class="px-4 py-2 text-slate-700">{{ $item->description }}</td>
                                                        <td class="px-4 py-2 text-right font-mono {{ $item->debit > 0 ? 'text-green-700 font-medium' : 'text-slate-300' }}">
                                                            {{ $item->debit > 0 ? '$'.number_format($item->debit, 0, ',', '.') : '—' }}
                                                        </td>
                                                        <td class="px-4 py-2 text-right font-mono {{ $item->credit > 0 ? 'text-red-600 font-medium' : 'text-slate-300' }}">
                                                            {{ $item->credit > 0 ? '$'.number_format($item->credit, 0, ',', '.') : '—' }}
                                                        </td>
                                                        @if(!$isLocked && !session('audit_mode'))
                                                            <td class="px-4 py-2 text-right">
                                                                <button wire:click="removeBankItem({{ $item->id }})"
                                                                    wire:confirm="¿Eliminar esta partida?"
                                                                    class="text-xs text-red-500 hover:text-red-700 transition">
                                                                    Eliminar
                                                                </button>
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Formulario partida bancaria --}}
                            @if($showBankItemForm && !session('audit_mode'))
                                <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 space-y-4">
                                    <h4 class="text-sm font-semibold text-slate-700">Agregar partida del extracto</h4>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1">Fecha</label>
                                            <input type="date" wire:model="bi_date"
                                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1">Descripción</label>
                                            <input type="text" wire:model="bi_description" placeholder="Ej: Cuota manejo, Interés cuenta..."
                                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1">Débito (abono del banco a mi cuenta)</label>
                                            <input type="number" wire:model="bi_debit" step="0.01" min="0" placeholder="0"
                                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-slate-600 mb-1">Crédito (cargo del banco a mi cuenta)</label>
                                            <input type="number" wire:model="bi_credit" step="0.01" min="0" placeholder="0"
                                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                                        </div>
                                    </div>
                                    <div class="flex gap-3">
                                        <button wire:click="addBankItem"
                                            class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                                            Agregar
                                        </button>
                                        <button wire:click="$set('showBankItemForm', false)"
                                            class="px-4 py-2 text-sm text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                                            Cancelar
                                        </button>
                                    </div>
                                </div>
                            @endif

                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    {{-- Modal nueva conciliación --}}
    @if($showNewForm)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-800">Nueva conciliación</h3>
                    <button wire:click="$set('showNewForm', false)" class="text-slate-400 hover:text-slate-600 transition">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Cuenta bancaria</label>
                        <select wire:model="rc_account_id"
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 bg-white">
                            <option value="0">— Selecciona una cuenta —</option>
                            @foreach($bankAccounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->code }} — {{ $acc->name }}</option>
                            @endforeach
                        </select>
                        @error('rc_account_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Desde</label>
                            <input type="date" wire:model="rc_period_from"
                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            @error('rc_period_from') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Hasta</label>
                            <input type="date" wire:model="rc_period_to"
                                class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                            @error('rc_period_to') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Saldo según extracto bancario</label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">$</span>
                            <input type="number" wire:model="rc_statement" step="0.01" placeholder="0"
                                class="w-full text-sm border border-slate-300 rounded-lg pl-7 pr-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        </div>
                        <p class="text-xs text-slate-400 mt-1">Ingresa el saldo final que aparece en el extracto del banco para este período.</p>
                        @error('rc_statement') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Notas (opcional)</label>
                        <input type="text" wire:model="rc_notes" placeholder="Ej: Extracto Bancolombia agosto 2025"
                            class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                    <button wire:click="$set('showNewForm', false)"
                        class="px-4 py-2 text-sm text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200 transition">
                        Cancelar
                    </button>
                    <button wire:click="createReconciliation" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="createReconciliation">Crear y cargar movimientos</span>
                        <span wire:loading wire:target="createReconciliation">Cargando...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
