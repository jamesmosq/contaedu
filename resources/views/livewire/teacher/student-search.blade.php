<div>
    {{-- Buscador --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="text-sm font-semibold text-slate-700 mb-3">Buscar estudiante por cédula</h3>

        <div class="flex gap-2">
            <div class="flex-1">
                <input wire:model="cedula"
                       wire:keydown.enter="search"
                       type="text"
                       inputmode="numeric"
                       pattern="[0-9\-]+"
                       placeholder="Cédula del estudiante..."
                       class="w-full rounded-lg border-slate-300 text-sm focus:ring-forest-500 focus:border-forest-500" />
                @error('cedula')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button wire:click="search"
                    wire:loading.attr="disabled"
                    class="px-4 py-2 bg-forest-700 text-white text-sm font-medium rounded-lg hover:bg-forest-800 transition disabled:opacity-50">
                <span wire:loading.remove wire:target="search">Buscar</span>
                <span wire:loading wire:target="search">Buscando...</span>
            </button>
        </div>
    </div>

    {{-- Modal: detalle del estudiante encontrado --}}
    @if($showModal && $this->foundTenant)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg" @click.outside="$wire.closeModal()">

                {{-- Cabecera --}}
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-slate-800">Transferir estudiante</h2>
                    <button wire:click="closeModal" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-4">

                    {{-- Información del estudiante --}}
                    <div class="bg-slate-50 rounded-xl p-4 space-y-1 text-sm">
                        <div class="flex justify-between">
                            <span class="text-slate-500">Estudiante</span>
                            <span class="font-medium text-slate-800">{{ $this->foundTenant->student_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Empresa</span>
                            <span class="font-medium text-slate-800">{{ $this->foundTenant->company_name }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-slate-500">Institución actual</span>
                            <span class="font-medium text-slate-800">
                                {{ $this->foundTenant->group?->institution?->name ?? '—' }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Estado</span>
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $this->foundTenant->activityStatus()->badgeClasses() }}">
                                {{ $this->foundTenant->activityStatus()->label() }}
                            </span>
                        </div>
                        @if($this->foundTenant->last_activity_at)
                            <div class="flex justify-between">
                                <span class="text-slate-500">Última actividad</span>
                                <span class="text-slate-700">{{ $this->foundTenant->last_activity_at->format('d/m/Y') }}</span>
                            </div>
                        @endif
                    </div>

                    {{-- Aviso según estado --}}
                    @if($this->foundTenant->isFree())
                        <div class="flex gap-2 bg-green-50 border border-green-200 rounded-lg p-3 text-sm text-green-800">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Este estudiante está libre. Puedes incorporarlo directamente sin necesidad de aprobación.</span>
                        </div>
                    @else
                        <div class="flex gap-2 bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-sm text-yellow-800">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                            <span>Este estudiante está activo en otra institución. Se enviará una solicitud formal al superadministrador para su aprobación.</span>
                        </div>
                    @endif

                    {{-- Grupo destino --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Grupo destino *</label>
                        <select wire:model="targetGroupId"
                                class="w-full rounded-lg border-slate-300 text-sm focus:ring-forest-500 focus:border-forest-500">
                            <option value="">— Selecciona un grupo —</option>
                            @foreach($this->myGroups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }} ({{ $group->period }})</option>
                            @endforeach
                        </select>
                        @error('targetGroupId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Modo de transferencia --}}
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-slate-700">Modo de transferencia *</label>
                        @foreach($this->transferModes as $mode)
                            <label class="flex items-start gap-3 p-3 rounded-lg border cursor-pointer transition
                                {{ $transferMode === $mode->value ? 'border-forest-500 bg-forest-50' : 'border-slate-200 hover:border-slate-300' }}">
                                <input type="radio" wire:model="transferMode" value="{{ $mode->value }}" class="mt-0.5 text-forest-600">
                                <div>
                                    <span class="block text-sm font-medium text-slate-700">{{ $mode->label() }}</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">{{ $mode->description() }}</span>
                                    @if($mode->warningLevel() === 'high')
                                        <span class="inline-block mt-1 text-xs font-medium text-red-600">⚠ Acción irreversible</span>
                                    @endif
                                </div>
                            </label>
                        @endforeach
                        @error('transferMode') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Notas (justificación) --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Notas / justificación
                            @if(! $this->foundTenant->isFree()) <span class="text-slate-400 text-xs">(recomendado para solicitudes formales)</span> @endif
                        </label>
                        <textarea wire:model="notes"
                                  rows="2"
                                  maxlength="500"
                                  placeholder="Motivo de la transferencia..."
                                  class="w-full rounded-lg border-slate-300 text-sm focus:ring-forest-500 focus:border-forest-500"></textarea>
                    </div>
                </div>

                {{-- Confirmación de modo destructivo --}}
                @if($showConfirm && $transferMode === 'fresh')
                    <div class="mx-6 mb-4 p-3 bg-red-50 border border-red-300 rounded-lg text-sm text-red-800 font-medium">
                        ¿Confirmas que deseas recrear la empresa desde cero? Todos los datos anteriores se perderán permanentemente.
                    </div>
                @endif

                {{-- Acciones --}}
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                    <button wire:click="closeModal"
                            class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 transition">
                        Cancelar
                    </button>

                    @if(! $showConfirm)
                        <button wire:click="confirmAction"
                                class="px-4 py-2 bg-forest-700 text-white text-sm font-medium rounded-lg hover:bg-forest-800 transition">
                            {{ $this->foundTenant->isFree() ? 'Incorporar estudiante' : 'Enviar solicitud' }}
                        </button>
                    @else
                        <button wire:click="execute"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 bg-forest-700 text-white text-sm font-medium rounded-lg hover:bg-forest-800 transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="execute">Confirmar</span>
                            <span wire:loading wire:target="execute">Procesando...</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
