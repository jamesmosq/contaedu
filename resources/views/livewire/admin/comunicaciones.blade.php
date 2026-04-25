<div>
    <x-slot name="header">
        <div>
            <h1 class="text-xl font-bold text-white">Comunicaciones</h1>
            <p class="text-sm text-forest-400 mt-0.5">Envía anuncios y avisos a coordinadores y docentes.</p>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 py-8 space-y-6">

        {{-- Botón nueva comunicación --}}
        <div class="flex justify-end">
            <button wire:click="openForm"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-forest-700 hover:bg-forest-600 text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nueva comunicación
            </button>
        </div>

        {{-- Formulario --}}
        @if($showForm)
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 space-y-5">
                <h2 class="text-base font-semibold text-slate-800">Nueva comunicación</h2>

                {{-- Tipo y audiencia --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Tipo</label>
                        <select wire:model="type"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-forest-500">
                            @foreach($this->typeOptions as $option)
                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                            @endforeach
                        </select>
                        @error('type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-slate-600 mb-1">Destinatarios</label>
                        <select wire:model="audience"
                                class="w-full rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-forest-500">
                            @foreach($this->audienceOptions as $option)
                                <option value="{{ $option->value }}">{{ $option->label() }}</option>
                            @endforeach
                        </select>
                        @error('audience') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- Título --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Título</label>
                    <input wire:model="title"
                           type="text"
                           maxlength="150"
                           placeholder="Ej: Mantenimiento programado el 30 de abril"
                           class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-forest-500">
                    @error('title') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Cuerpo --}}
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1">Mensaje</label>
                    <textarea wire:model="body"
                              rows="4"
                              maxlength="2000"
                              placeholder="Escribe el contenido del mensaje..."
                              class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-700 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-forest-500 resize-none"></textarea>
                    @error('body') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Acciones --}}
                <div class="flex justify-end gap-3 pt-1">
                    <button wire:click="$set('showForm', false)"
                            class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 transition">
                        Cancelar
                    </button>
                    <button wire:click="send"
                            wire:loading.attr="disabled"
                            wire:target="send"
                            class="inline-flex items-center gap-2 px-5 py-2 bg-forest-700 hover:bg-forest-600 disabled:opacity-60 text-white text-sm font-medium rounded-xl transition">
                        <span wire:loading.remove wire:target="send">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5"/>
                            </svg>
                        </span>
                        <span wire:loading wire:target="send">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                        </span>
                        Enviar comunicación
                    </button>
                </div>
            </div>
        @endif

        {{-- Historial --}}
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-semibold text-slate-700">Historial de comunicaciones</h2>
            </div>

            @forelse($this->communications as $comm)
                @php
                    $colors = [
                        'announcement' => 'bg-blue-100 text-blue-700',
                        'maintenance'  => 'bg-amber-100 text-amber-700',
                        'update'       => 'bg-emerald-100 text-emerald-700',
                        'urgent'       => 'bg-red-100 text-red-700',
                    ];
                    $badgeClass = $colors[$comm->type->value] ?? 'bg-slate-100 text-slate-600';
                @endphp
                <div wire:key="comm-{{ $comm->id }}" class="px-6 py-4 border-b border-slate-50 last:border-0">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap mb-1">
                                <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $badgeClass }}">
                                    {{ $comm->type->label() }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    → {{ $comm->audience->label() }}
                                </span>
                            </div>
                            <p class="text-sm font-semibold text-slate-800">{{ $comm->title }}</p>
                            <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">{{ $comm->body }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-xs text-slate-400">{{ $comm->sent_at->diffForHumans() }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $comm->recipient_count }} destinatario(s)</p>
                        </div>
                    </div>
                </div>
            @empty
                <div class="px-6 py-10 text-center text-sm text-slate-400">
                    Aún no se han enviado comunicaciones.
                </div>
            @endforelse
        </div>

    </div>
</div>
