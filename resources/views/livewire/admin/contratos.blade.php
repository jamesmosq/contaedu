<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Gestión de contratos</h2>
                <p class="text-xs text-slate-500 mt-0.5">Fechas de vigencia, estado y acceso por institución</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto px-6 py-8 space-y-6">

        {{-- Alertas rápidas: próximas a vencer --}}
        @php
            $criticas = $institutions->filter(fn($i) => in_array($i->contractStatus(), ['critico', 'vencido']));
            $proximas = $institutions->filter(fn($i) => $i->contractStatus() === 'proximo');
        @endphp

        @if($criticas->isNotEmpty())
            <div class="bg-red-50 border border-red-200 rounded-2xl px-5 py-4">
                <p class="text-sm font-semibold text-red-700 mb-2">
                    {{ $criticas->count() }} institución(es) con contrato vencido o por vencer en ≤15 días
                </p>
                <ul class="space-y-1">
                    @foreach($criticas as $inst)
                        <li class="text-xs text-red-600 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-red-500 shrink-0"></span>
                            <strong>{{ $inst->name }}</strong>
                            @if($inst->contract_expires_at)
                                — vence {{ $inst->contract_expires_at->format('d/m/Y') }}
                                @if($inst->contractStatus() === 'vencido') <span class="font-semibold">(VENCIDO)</span> @endif
                            @else
                                — sin fecha registrada
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($proximas->isNotEmpty())
            <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-4">
                <p class="text-sm font-semibold text-amber-700 mb-2">
                    {{ $proximas->count() }} institución(es) con contrato por vencer en ≤30 días
                </p>
                <ul class="space-y-1">
                    @foreach($proximas as $inst)
                        <li class="text-xs text-amber-700 flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                            <strong>{{ $inst->name }}</strong> — vence {{ $inst->contract_expires_at->format('d/m/Y') }}
                            ({{ now()->diffInDays($inst->contract_expires_at) }} días)
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Tabla principal --}}
        <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-cream-50 border-b border-cream-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Institución</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Inicio</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Vencimiento</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado contrato</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acceso</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-cream-100">
                    @forelse($institutions as $inst)
                        @php
                            $cs = $inst->contractStatus();
                            $csConfig = match($cs) {
                                'vigente'   => ['bg' => 'bg-green-50',  'text' => 'text-green-700',  'dot' => 'bg-green-500',  'label' => 'Vigente'],
                                'proximo'   => ['bg' => 'bg-amber-50',  'text' => 'text-amber-700',  'dot' => 'bg-amber-400',  'label' => 'Por vencer'],
                                'critico'   => ['bg' => 'bg-red-50',    'text' => 'text-red-700',    'dot' => 'bg-red-500',    'label' => 'Crítico'],
                                'vencido'   => ['bg' => 'bg-slate-100', 'text' => 'text-slate-500',  'dot' => 'bg-slate-400',  'label' => 'Vencido'],
                                default     => ['bg' => 'bg-slate-50',  'text' => 'text-slate-400',  'dot' => 'bg-slate-300',  'label' => 'Sin fecha'],
                            };
                        @endphp
                        <tr wire:key="inst-{{ $inst->id }}" class="hover:bg-cream-50 transition">
                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-800">{{ $inst->name }}</p>
                                <p class="text-xs text-slate-400">
                                    {{ $inst->students_count }} est. · {{ $inst->groups_count }} grupo(s)
                                    @if($inst->coordinator) · {{ $inst->coordinator->name }} @endif
                                </p>
                            </td>
                            <td class="px-4 py-4 text-center text-xs text-slate-500">
                                {{ $inst->contract_starts_at?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-4 text-center">
                                @if($inst->contract_expires_at)
                                    <span class="text-xs font-medium {{ in_array($cs, ['critico', 'vencido']) ? 'text-red-600' : ($cs === 'proximo' ? 'text-amber-600' : 'text-slate-600') }}">
                                        {{ $inst->contract_expires_at->format('d/m/Y') }}
                                        @if(in_array($cs, ['critico', 'proximo']))
                                            <br><span class="font-normal">{{ now()->diffInDays($inst->contract_expires_at) }}d restantes</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-xs text-slate-300">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $csConfig['bg'] }} {{ $csConfig['text'] }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $csConfig['dot'] }}"></span>
                                    {{ $csConfig['label'] }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold
                                    {{ $inst->active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $inst->active ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                                    {{ $inst->active ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button wire:click="openEdit({{ $inst->id }})"
                                        class="text-xs text-forest-600 hover:text-forest-800 font-medium">
                                        Editar contrato
                                    </button>
                                    <button wire:click="toggleActive({{ $inst->id }})"
                                        wire:confirm="{{ $inst->active ? '¿Deshabilitar acceso a esta institución?' : '¿Habilitar acceso a esta institución?' }}"
                                        class="text-xs font-medium {{ $inst->active ? 'text-amber-600 hover:text-amber-800' : 'text-green-600 hover:text-green-800' }}">
                                        {{ $inst->active ? 'Deshabilitar' : 'Habilitar' }}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                No hay instituciones registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="text-xs text-slate-400">
            El sistema deshabilita automáticamente las instituciones al vencer su contrato (00:05 diario).
            Los coordinadores reciben notificación a los 30 y 15 días de anticipación.
        </p>

    </div>

    {{-- Modal editar contrato --}}
    @if($showForm)
        @php $inst = $institutions->firstWhere('id', $editingId); @endphp
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-card-lg w-full max-w-md">
                <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-800">Editar contrato</h3>
                        @if($inst)
                            <p class="text-xs text-slate-400 mt-0.5">{{ $inst->name }}</p>
                        @endif
                    </div>
                    <button wire:click="$set('showForm',false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha de inicio del contrato</label>
                        <input wire:model="contractStartsAt" type="date"
                            class="w-full border border-cream-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-forest-500 focus:border-forest-500">
                        <p class="mt-1 text-xs text-slate-400">El acceso se activa automáticamente en esta fecha.</p>
                        @error('contractStartsAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Fecha de vencimiento del contrato</label>
                        <input wire:model="contractExpiresAt" type="date"
                            class="w-full border border-cream-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-forest-500 focus:border-forest-500">
                        <p class="mt-1 text-xs text-slate-400">El acceso se deshabilita automáticamente al vencer. Se notifica al coordinador a los 30 y 15 días.</p>
                        @error('contractExpiresAt') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showForm',false)"
                        class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">Cancelar</button>
                    <button wire:click="saveContract" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveContract">Guardar</span>
                        <span wire:loading wire:target="saveContract">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
