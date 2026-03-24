<div>

    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Panel Docente</p>
                <h1 class="font-display text-2xl font-bold text-white">Anuncios al grupo</h1>
                <p class="text-forest-300 text-sm mt-1">Publica avisos, fechas y recordatorios para tus estudiantes</p>
            </div>
            @if($selectedGroupId)
                <button wire:click="openCreate"
                    class="flex items-center gap-2 px-4 py-2 bg-gold-500 text-forest-950 text-sm font-bold rounded-xl hover:bg-gold-400 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Nuevo aviso
                </button>
            @endif
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">

            {{-- ── Selector de grupo ──────────────────────────────────────── --}}
            @if(! $selectedGroupId)
                @if($groups->isEmpty())
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-16 text-center">
                        <p class="text-slate-400 text-sm">No tienes grupos creados aún.</p>
                    </div>
                @else
                    <p class="text-sm text-slate-500 mb-4">Selecciona un grupo para gestionar sus anuncios:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($groups as $group)
                            <button wire:click="selectGroup({{ $group->id }})"
                                class="bg-white rounded-2xl border border-cream-200 shadow-card hover:shadow-card-md hover:border-forest-300 transition-all p-6 text-left group">
                                <div class="flex items-center gap-3 mb-3">
                                    <div class="w-10 h-10 bg-forest-50 rounded-xl flex items-center justify-center group-hover:bg-forest-100 transition">
                                        <svg class="w-5 h-5 text-forest-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                                        </svg>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-slate-800 truncate">{{ $group->name }}</p>
                                        <p class="text-xs text-slate-400">Período {{ $group->period }}</p>
                                    </div>
                                </div>
                                @php $count = \App\Models\Central\Announcement::where('group_id', $group->id)->count(); @endphp
                                <p class="text-xs text-slate-500">
                                    {{ $count === 0 ? 'Sin anuncios' : $count.' '.($count === 1 ? 'anuncio' : 'anuncios') }}
                                </p>
                            </button>
                        @endforeach
                    </div>
                @endif

            {{-- ── Lista de anuncios del grupo ─────────────────────────────── --}}
            @else
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <button wire:click="clearGroup"
                            class="flex items-center gap-1.5 text-sm text-slate-500 hover:text-slate-700 font-medium px-3 py-1.5 rounded-xl hover:bg-slate-100 transition">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                            </svg>
                            Grupos
                        </button>
                        <span class="text-slate-300">/</span>
                        <span class="text-sm font-semibold text-slate-700">{{ $selectedGroup?->name }}</span>
                    </div>
                </div>

                @if($announcements->isEmpty())
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-14 text-center">
                        <div class="w-14 h-14 bg-forest-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-forest-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-slate-700 mb-1">Sin anuncios para este grupo</h3>
                        <p class="text-slate-400 text-sm mb-5">Crea el primer aviso para que tus estudiantes lo vean en su dashboard.</p>
                        <button wire:click="openCreate"
                            class="px-5 py-2.5 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                            Crear primer anuncio
                        </button>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($announcements as $ann)
                            <div class="bg-white rounded-2xl border shadow-card transition-all
                                {{ $ann->active ? 'border-cream-200' : 'border-slate-100 opacity-60' }}">
                                <div class="px-5 py-4 flex items-start justify-between gap-4">
                                    <div class="flex items-start gap-3 min-w-0">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 mt-0.5
                                            {{ $ann->active ? 'bg-forest-50' : 'bg-slate-100' }}">
                                            <svg class="w-4.5 h-4.5 {{ $ann->active ? 'text-forest-600' : 'text-slate-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2 flex-wrap mb-0.5">
                                                <p class="text-sm font-semibold text-slate-800">{{ $ann->title }}</p>
                                                @if(! $ann->active)
                                                    <span class="text-xs px-2 py-0.5 bg-slate-100 text-slate-500 rounded-full font-medium">Oculto</span>
                                                @endif
                                                @if($ann->due_date)
                                                    <span class="text-xs px-2 py-0.5 rounded-full font-medium
                                                        {{ $ann->due_date->isPast() ? 'bg-red-50 text-red-600' : 'bg-gold-50 text-gold-700' }}">
                                                        Fecha límite: {{ $ann->due_date->format('d/m/Y') }}
                                                    </span>
                                                @endif
                                            </div>
                                            @if($ann->body)
                                                <p class="text-sm text-slate-500 leading-relaxed">{{ $ann->body }}</p>
                                            @endif
                                            <p class="text-xs text-slate-400 mt-1.5">{{ $ann->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button wire:click="toggleActive({{ $ann->id }})"
                                            title="{{ $ann->active ? 'Ocultar' : 'Publicar' }}"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">
                                            @if($ann->active)
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                                </svg>
                                            @endif
                                        </button>
                                        <button wire:click="openEdit({{ $ann->id }})"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-forest-700 hover:bg-forest-50 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125" />
                                            </svg>
                                        </button>
                                        <button
                                            x-on:click="confirmAction('¿Eliminar el anuncio «{{ addslashes($ann->title) }}»?', () => $wire.delete({{ $ann->id }}), { danger: true, confirmText: 'Sí, eliminar' })"
                                            class="p-1.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ═══ Modal: Nuevo / Editar aviso ═══ --}}
    @if($showForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" wire:click.self="$set('showForm', false)">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg">
                <div class="px-6 py-5 border-b border-cream-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-800">{{ $editingId ? 'Editar aviso' : 'Nuevo aviso' }}</h3>
                    <button wire:click="$set('showForm', false)" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition text-sm">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Grupo</label>
                        <select wire:model="formGroupId" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                            <option value="0">Selecciona un grupo</option>
                            @foreach($groups as $g)
                                <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->period }})</option>
                            @endforeach
                        </select>
                        @error('formGroupId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Título</label>
                        <input wire:model="title" type="text" placeholder="Ej: Entrega de facturas — semana 3"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Mensaje <span class="text-slate-400 font-normal">(opcional)</span></label>
                        <textarea wire:model="body" rows="3" placeholder="Detalle del aviso o instrucciones..."
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Fecha límite <span class="text-slate-400 font-normal">(opcional)</span></label>
                        <input wire:model="dueDate" type="date"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                    </div>
                    <div class="flex items-center gap-3">
                        <input wire:model="formActive" type="checkbox" id="formActive" class="w-4 h-4 rounded text-forest-700 border-slate-300 focus:ring-forest-500">
                        <label for="formActive" class="text-sm text-slate-700">Visible para estudiantes</label>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showForm', false)" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">Cancelar</button>
                    <button wire:click="save" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="save">Guardar</span>
                        <span wire:loading wire:target="save">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
