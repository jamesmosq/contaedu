<div>
    {{-- Filtros --}}
    <div class="flex gap-2 mb-5">
        @foreach(['pending' => 'Pendientes', 'approved' => 'Aprobadas', 'rejected' => 'Rechazadas'] as $value => $label)
            <button wire:click="$set('filter', '{{ $value }}')"
                    class="px-3 py-1.5 rounded-lg text-sm font-medium transition
                    {{ $filter === $value
                        ? 'bg-forest-700 text-white'
                        : 'bg-white border border-slate-200 text-slate-600 hover:border-forest-400' }}">
                {{ $label }}
            </button>
        @endforeach
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Estudiante</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Solicitante</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Destino</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado actividad</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Modo</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                    @if($filter === 'pending')
                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($this->requests as $req)
                    <tr wire:key="req-{{ $req->id }}" class="hover:bg-slate-50 transition">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $req->tenant?->student_name ?? '—' }}</p>
                            <p class="text-xs text-slate-400">{{ $req->tenant_id }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-slate-700">{{ $req->requester?->name ?? '—' }}</p>
                            <p class="text-xs text-slate-400">
                                {{ $req->targetGroup?->institution?->name ?? '—' }}
                            </p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-slate-700">{{ $req->targetGroup?->name ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($req->tenant)
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                    {{ $req->tenant->activityStatus()->badgeClasses() }}">
                                    {{ $req->tenant->activityStatus()->label() }}
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-700">{{ $req->transfer_mode->label() }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                                {{ $req->status->badgeClasses() }}">
                                {{ $req->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">
                            {{ $req->created_at->format('d/m/Y') }}
                        </td>
                        @if($filter === 'pending')
                            <td class="px-4 py-3">
                                <div class="flex gap-1">
                                    <button wire:click="openAction({{ $req->id }}, 'approve')"
                                            class="px-2 py-1 bg-green-600 text-white text-xs rounded-lg hover:bg-green-700 transition">
                                        Aprobar
                                    </button>
                                    <button wire:click="openAction({{ $req->id }}, 'reject')"
                                            class="px-2 py-1 bg-red-600 text-white text-xs rounded-lg hover:bg-red-700 transition">
                                        Rechazar
                                    </button>
                                </div>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-10 text-center text-sm text-slate-400">
                            No hay solicitudes en este estado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $this->requests->links() }}

    {{-- Modal: aprobar / rechazar --}}
    @if($actionId)
        @php $req = \App\Models\Central\TransferRequest::with('tenant')->find($actionId); @endphp
        @if($req)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h2 class="text-base font-semibold text-slate-800">
                            {{ $actionType === 'approve' ? 'Aprobar transferencia' : 'Rechazar solicitud' }}
                        </h2>
                        <p class="text-sm text-slate-500 mt-0.5">{{ $req->tenant?->student_name }}</p>
                    </div>

                    <div class="px-6 py-4 space-y-3">
                        @if($req->notes)
                            <div class="bg-slate-50 rounded-lg p-3 text-sm text-slate-700">
                                <span class="font-medium block text-xs text-slate-400 mb-1">Notas del solicitante</span>
                                {{ $req->notes }}
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">
                                Respuesta / notas
                                @if($actionType === 'reject') <span class="text-red-500">*</span> @endif
                            </label>
                            <textarea wire:model="adminNotes"
                                      rows="3"
                                      maxlength="500"
                                      placeholder="{{ $actionType === 'reject' ? 'Motivo del rechazo (requerido)...' : 'Notas opcionales...' }}"
                                      class="w-full rounded-lg border-slate-300 text-sm focus:ring-forest-500 focus:border-forest-500"></textarea>
                            @error('adminNotes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-2">
                        <button wire:click="cancelAction"
                                class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 transition">
                            Cancelar
                        </button>
                        <button wire:click="executeAction"
                                wire:loading.attr="disabled"
                                class="px-4 py-2 text-sm font-medium text-white rounded-lg transition disabled:opacity-50
                                    {{ $actionType === 'approve' ? 'bg-green-600 hover:bg-green-700' : 'bg-red-600 hover:bg-red-700' }}">
                            <span wire:loading.remove wire:target="executeAction">
                                {{ $actionType === 'approve' ? 'Aprobar y ejecutar' : 'Rechazar' }}
                            </span>
                            <span wire:loading wire:target="executeAction">Procesando...</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
