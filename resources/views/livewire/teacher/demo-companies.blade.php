<div>

    {{-- ── Hero banner ─────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Panel Docente</p>
                <h1 class="font-display text-2xl font-bold text-white">Empresas de demostración</h1>
                <p class="text-forest-300 text-sm mt-1">Crea empresas modelo y asígnalas a los grupos que correspondan</p>
            </div>
            <button wire:click="openCreate"
                class="flex items-center gap-2 px-4 py-2 bg-gold-500 text-forest-950 text-sm font-bold rounded-xl hover:bg-gold-400 transition shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nueva empresa demo
            </button>
        </div>
    </div>

    {{-- ── Lista de demos ───────────────────────────────────────────────────── --}}
    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">

            @if($demos->isEmpty())
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-16 text-center">
                    <div class="w-16 h-16 bg-forest-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
                        <svg class="w-8 h-8 text-forest-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-700 mb-1">Sin empresas de demostración</h3>
                    <p class="text-slate-400 text-sm mb-6 max-w-sm mx-auto">
                        Crea empresas modelo de diferentes sectores económicos para asignarlas a tus grupos.
                    </p>
                    <button wire:click="openCreate"
                        class="px-5 py-2.5 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                        Crear primera empresa demo
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($demos as $demo)
                        @php
                            $sectorLabel = $sectors[$demo->sector] ?? ucfirst((string) $demo->sector);
                            $assignedGroups = $demo->assignedGroups;
                        @endphp
                        <div class="bg-white rounded-2xl border border-cream-200 shadow-card hover:shadow-card-md transition-all flex flex-col">
                            <div class="p-6 flex-1">

                                {{-- Cabecera --}}
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-11 h-11 bg-forest-50 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-forest-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                                        </svg>
                                    </div>

                                    {{-- Badge: grupos asignados --}}
                                    @if($assignedGroups->isEmpty())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-500 text-xs font-medium rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Sin asignar
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                            {{ $assignedGroups->count() }} {{ $assignedGroups->count() === 1 ? 'grupo' : 'grupos' }}
                                        </span>
                                    @endif
                                </div>

                                {{-- Info empresa --}}
                                <h3 class="text-base font-bold text-slate-800 leading-snug mb-1">{{ $demo->company_name }}</h3>
                                <p class="text-xs text-slate-400 mb-3">NIT {{ $demo->nit_empresa }}</p>
                                <span class="inline-block px-2.5 py-1 bg-gold-50 text-gold-700 text-xs font-medium rounded-lg border border-gold-100">
                                    {{ $sectorLabel }}
                                </span>

                                {{-- Chips de grupos asignados --}}
                                @if($assignedGroups->isNotEmpty())
                                    <div class="flex flex-wrap gap-1.5 mt-3">
                                        @foreach($assignedGroups as $grp)
                                            <span class="inline-flex items-center px-2 py-0.5 bg-forest-50 text-forest-700 text-xs font-medium rounded-lg border border-forest-100">
                                                {{ $grp->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Badge de accesos --}}
                                @php
                                    $accessed = $accessCounts[$demo->id] ?? 0;
                                    $totalStudents = $studentCounts[$demo->id] ?? 0;
                                @endphp
                                @if($totalStudents > 0)
                                    <div class="mt-3 flex items-center gap-2">
                                        <button wire:click="openAccessModal('{{ $demo->id }}')"
                                            class="flex items-center gap-1.5 text-xs font-medium rounded-lg px-2.5 py-1.5 transition
                                                {{ $accessed > 0 ? 'bg-blue-50 text-blue-700 hover:bg-blue-100' : 'bg-slate-50 text-slate-500 hover:bg-slate-100' }}">
                                            <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            {{ $accessed }} / {{ $totalStudents }} {{ $totalStudents === 1 ? 'estudiante' : 'estudiantes' }}
                                        </button>
                                    </div>
                                @endif

                                <p class="text-xs text-slate-400 mt-3">Creada {{ $demo->created_at->diffForHumans() }}</p>
                            </div>

                            {{-- Acciones --}}
                            <div class="border-t border-cream-100 px-4 py-3 flex items-center justify-between">
                                <button
                                    x-on:click="confirmAction('¿Eliminar la empresa «{{ addslashes($demo->company_name) }}»? Se eliminará el schema PostgreSQL y todos sus datos.', () => $wire.delete('{{ $demo->id }}'), { danger: true, confirmText: 'Sí, eliminar' })"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium px-2.5 py-1.5 rounded-lg hover:bg-red-50 transition">
                                    Eliminar
                                </button>

                                <div class="flex items-center gap-1">
                                    <button wire:click="openGroupsModal('{{ $demo->id }}')"
                                        class="flex items-center gap-1.5 text-xs text-slate-600 hover:text-forest-800 font-medium px-2.5 py-1.5 rounded-lg hover:bg-forest-50 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                        </svg>
                                        Grupos
                                    </button>
                                    <a href="{{ route('teacher.demo.enter', $demo->id) }}"
                                        class="flex items-center gap-1.5 text-xs text-forest-700 hover:text-forest-900 font-semibold px-2.5 py-1.5 rounded-lg hover:bg-forest-50 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                        Entrar
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="text-xs text-slate-400 mt-6 text-center">
                    Las empresas asignadas a <span class="font-semibold text-green-600">uno o más grupos</span> son visibles para los estudiantes de esos grupos en "Empresas de referencia".
                    Las <span class="font-semibold text-slate-500">sin asignar</span> solo las ves tú.
                </p>
            @endif
        </div>
    </div>

    {{-- ═══════ Modal: Nueva empresa demo ═══════ --}}
    @if($showForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="px-6 py-5 border-b border-cream-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-800">Nueva empresa de demostración</h3>
                    <button wire:click="$set('showForm', false)" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition text-sm">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Razón social</label>
                        <input wire:model="companyName" type="text" placeholder="Ej: Comercializadora Los Andes S.A.S"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('companyName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">NIT empresa</label>
                        <input wire:model="nitEmpresa" type="text" placeholder="Ej: 900123456-1"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('nitEmpresa') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Sector económico</label>
                        <select wire:model="sector" class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                            @foreach($sectors as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('sector') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="bg-gold-50 border border-gold-100 rounded-xl px-4 py-3 text-xs text-gold-700">
                        Se creará un schema propio en PostgreSQL con el PUC colombiano. Luego podrás asignarla a los grupos que correspondan.
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showForm', false)" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">Cancelar</button>
                    <button wire:click="create" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="create">Crear empresa</span>
                        <span wire:loading wire:target="create">Creando schema…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════ Modal: Accesos de estudiantes ═══════ --}}
    @if($showAccessModal)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="px-6 py-5 border-b border-cream-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-800">Accesos a esta empresa</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Estudiantes que han visitado la empresa de referencia</p>
                    </div>
                    <button wire:click="$set('showAccessModal', false)" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition text-sm">✕</button>
                </div>

                <div class="px-6 py-4 max-h-96 overflow-y-auto">
                    @if(empty($accessData))
                        <p class="text-sm text-slate-400 text-center py-6">No hay estudiantes asignados a los grupos de esta empresa.</p>
                    @else
                        <div class="space-y-1.5">
                            @foreach($accessData as $row)
                                <div class="flex items-center justify-between px-3 py-2.5 rounded-xl
                                    {{ $row['accessed_at'] ? 'bg-blue-50 border border-blue-100' : 'bg-slate-50 border border-cream-100' }}">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-7 h-7 rounded-lg flex items-center justify-center shrink-0
                                            {{ $row['accessed_at'] ? 'bg-blue-100' : 'bg-slate-200' }}">
                                            @if($row['accessed_at'])
                                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                                </svg>
                                            @else
                                                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium {{ $row['accessed_at'] ? 'text-slate-800' : 'text-slate-500' }} truncate">
                                                {{ $row['student_name'] }}
                                            </p>
                                            <p class="text-xs {{ $row['accessed_at'] ? 'text-slate-500' : 'text-slate-400' }} truncate">
                                                {{ $row['company_name'] }}
                                                <span class="text-forest-600 font-medium">· {{ $row['group_name'] }}</span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="shrink-0 ml-2 text-right">
                                        @if($row['accessed_at'])
                                            <p class="text-xs text-blue-600 font-medium">{{ $row['accessed_at'] }}</p>
                                        @else
                                            <p class="text-xs text-slate-400">Pendiente</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @php
                            $accessedCount = collect($accessData)->whereNotNull('accessed_at')->count();
                            $totalCount = count($accessData);
                        @endphp
                        <div class="mt-4 px-3 py-2.5 bg-forest-50 rounded-xl flex items-center justify-between">
                            <span class="text-xs text-forest-700 font-medium">Total accesos</span>
                            <span class="text-sm font-bold text-forest-800">{{ $accessedCount }} / {{ $totalCount }}</span>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-4 border-t border-cream-100 flex justify-end">
                    <button wire:click="$set('showAccessModal', false)"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════ Modal: Gestionar grupos ═══════ --}}
    @if($showGroupsModal)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm">
                <div class="px-6 py-5 border-b border-cream-100 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-semibold text-slate-800">Asignar a grupos</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Solo los estudiantes del grupo verán esta empresa</p>
                    </div>
                    <button wire:click="$set('showGroupsModal', false)" class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition text-sm">✕</button>
                </div>

                <div class="px-6 py-4">
                    @if($groups->isEmpty())
                        <p class="text-sm text-slate-400 text-center py-4">No tienes grupos creados.</p>
                    @else
                        <div class="space-y-2">
                            @foreach($groups as $group)
                                <label class="flex items-center gap-3 p-3 rounded-xl border border-cream-200 hover:border-forest-300 hover:bg-forest-50/50 cursor-pointer transition has-[:checked]:border-forest-400 has-[:checked]:bg-forest-50">
                                    <input type="checkbox"
                                        wire:model="selectedGroupIds"
                                        value="{{ $group->id }}"
                                        class="w-4 h-4 rounded text-forest-700 border-slate-300 focus:ring-forest-500">
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-800">{{ $group->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $group->institution->name }} · Período {{ $group->period }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="px-6 py-4 border-t border-cream-100 flex items-center justify-between gap-3">
                    <p class="text-xs text-slate-400">
                        {{ count($selectedGroupIds) === 0 ? 'Sin asignar — no visible para estudiantes' : count($selectedGroupIds).' grupo(s) seleccionado(s)' }}
                    </p>
                    <div class="flex gap-2">
                        <button wire:click="$set('showGroupsModal', false)" class="px-3 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">Cancelar</button>
                        <button wire:click="saveGroups" wire:loading.attr="disabled"
                            class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
