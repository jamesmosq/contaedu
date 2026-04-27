<div>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold text-slate-800">Anuncios</h2>
            <p class="text-sm text-slate-500 mt-0.5">Avisos publicados por los docentes de {{ $institution->name }}</p>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            {{-- Filtro de grupo --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm p-4 flex items-center gap-4">
                <label class="text-sm font-medium text-slate-700 shrink-0">Filtrar por grupo:</label>
                <select wire:model.live="selectedGroupId"
                    class="text-sm border border-cream-300 rounded-xl px-3 py-2 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-forest-500">
                    <option value="0">Todos los grupos</option>
                    @foreach($groups as $group)
                        <option value="{{ $group->id }}">{{ $group->name }}</option>
                    @endforeach
                </select>
                <span class="text-xs text-slate-400">{{ $announcements->count() }} aviso(s)</span>
            </div>

            {{-- Lista de anuncios --}}
            <div class="space-y-3">
                @forelse($announcements as $ann)
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    @if($ann->active)
                                        <span class="inline-block w-2 h-2 rounded-full bg-forest-500 shrink-0"></span>
                                    @else
                                        <span class="inline-block w-2 h-2 rounded-full bg-slate-300 shrink-0"></span>
                                    @endif
                                    <h3 class="font-semibold text-slate-800 text-sm">{{ $ann->title }}</h3>
                                </div>
                                @if($ann->body)
                                    <p class="text-sm text-slate-600 mt-1">{{ $ann->body }}</p>
                                @endif
                                <div class="flex items-center gap-3 mt-3 text-xs text-slate-400">
                                    <span>{{ $ann->teacher?->name ?? '—' }}</span>
                                    <span>·</span>
                                    <span>{{ $ann->group?->name ?? '—' }}</span>
                                    <span>·</span>
                                    <span>{{ $ann->created_at->format('d/m/Y') }}</span>
                                    @if($ann->due_date)
                                        <span>·</span>
                                        <span class="text-amber-600 font-medium">Vence {{ $ann->due_date->format('d/m/Y') }}</span>
                                    @endif
                                </div>
                            </div>
                            <span class="shrink-0 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ann->active ? 'bg-forest-100 text-forest-700' : 'bg-slate-100 text-slate-400' }}">
                                {{ $ann->active ? 'Activo' : 'Inactivo' }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm px-6 py-12 text-center text-slate-400">
                        No hay anuncios publicados en este grupo.
                    </div>
                @endforelse
            </div>

        </div>
    </div>
</div>
