<div>

    {{-- ══════════════════════════════════════════════════════════
         HERO BANNER — Stats del docente
    ══════════════════════════════════════════════════════════ --}}
    @if(!$selectedGroupId)
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
                <div>
                    <p class="text-forest-400 text-sm font-medium uppercase tracking-widest mb-1">Panel Docente</p>
                    <h1 class="font-display text-2xl lg:text-3xl font-bold text-white">
                        {{ auth()->user()->name }}
                    </h1>
                    <p class="text-forest-300 text-sm mt-1 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                        </svg>
                        {{ $stats['institucion'] }}
                    </p>
                </div>
                <a href="{{ route('teacher.comparativo') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-gold-500/20 border border-gold-500/30 text-gold-300 text-sm font-medium rounded-xl hover:bg-gold-500/30 transition shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                    Ver comparativo
                </a>
            </div>

            {{-- KPI cards --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

                {{-- Grupos --}}
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-gold-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4 text-gold-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $stats['grupos'] }}</p>
                    <p class="text-forest-400 text-xs mt-1">{{ $stats['grupos'] === 1 ? 'Grupo activo' : 'Grupos activos' }}</p>
                </div>

                {{-- Estudiantes --}}
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-gold-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4 text-gold-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $stats['estudiantes'] }}</p>
                    <p class="text-forest-400 text-xs mt-1">Total de estudiantes</p>
                </div>

                {{-- Activos --}}
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-green-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $stats['activos'] }}</p>
                    <p class="text-forest-400 text-xs mt-1">Empresas activas</p>
                </div>

                {{-- Inactivos --}}
                <div class="bg-white/5 border border-white/10 rounded-2xl p-5">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-9 h-9 bg-slate-500/20 rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-3xl font-bold text-white">{{ $stats['estudiantes'] - $stats['activos'] }}</p>
                    <p class="text-forest-400 text-xs mt-1">Empresas inactivas</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════
         CONTENIDO PRINCIPAL
    ══════════════════════════════════════════════════════════ --}}
    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">

            {{-- ══════════════════════════════════════════════════════════
                 VISTA A — Grid de grupos
            ══════════════════════════════════════════════════════════ --}}
            @if(!$selectedGroupId)

                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-slate-800">Mis grupos</h2>
                        <p class="text-sm text-slate-500 mt-0.5">Gestiona tus grupos y las empresas de tus estudiantes</p>
                    </div>
                    <button wire:click="openGroupForm()"
                        class="flex items-center gap-2 px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Nuevo grupo
                    </button>
                </div>

                @if($groups->isEmpty())
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-16 text-center">
                        <div class="w-16 h-16 bg-forest-50 rounded-2xl flex items-center justify-center mx-auto mb-5">
                            <svg class="w-8 h-8 text-forest-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-semibold text-slate-700 mb-1">Sin grupos creados</h3>
                        <p class="text-slate-500 text-sm mb-6">Crea tu primer grupo para empezar a agregar estudiantes.</p>
                        <button wire:click="openGroupForm()"
                            class="px-5 py-2.5 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                            Crear primer grupo
                        </button>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                        @foreach($groups as $group)
                            @php $count = $group->tenants->count(); @endphp
                            <div class="bg-white rounded-2xl border border-cream-200 shadow-card hover:border-forest-300 hover:shadow-card-md transition-all flex flex-col">
                                <button wire:click="selectGroup({{ $group->id }})" class="flex-1 text-left p-6">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="w-11 h-11 bg-forest-50 rounded-xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-forest-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                                            </svg>
                                        </div>
                                        <span class="text-3xl font-bold text-forest-800">{{ $count }}</span>
                                    </div>
                                    <h3 class="text-base font-bold text-slate-800 mb-1">{{ $group->name }}</h3>
                                    <p class="text-sm text-slate-500">{{ $group->institution->name }}</p>
                                    <div class="flex items-center gap-3 mt-3">
                                        <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                                            </svg>
                                            Período {{ $group->period }}
                                        </span>
                                        <span class="inline-flex items-center gap-1 text-xs text-slate-400">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                            </svg>
                                            {{ $count }} {{ $count === 1 ? 'estudiante' : 'estudiantes' }}
                                        </span>
                                    </div>
                                </button>
                                <div class="border-t border-cream-100 px-4 py-3 flex items-center justify-between">
                                    <div class="flex gap-1">
                                        <button wire:click="openGroupForm({{ $group->id }})"
                                            class="text-xs text-slate-500 hover:text-slate-700 font-medium px-2.5 py-1.5 rounded-lg hover:bg-slate-50 transition">
                                            Editar
                                        </button>
                                        <button
                                            x-on:click="confirmAction('¿Eliminar el grupo «{{ $group->name }}»? Solo es posible si no tiene empresas asignadas.', () => $wire.deleteGroup({{ $group->id }}), { danger: true, confirmText: 'Sí, eliminar' })"
                                            class="text-xs text-red-500 hover:text-red-700 font-medium px-2.5 py-1.5 rounded-lg hover:bg-red-50 transition">
                                            Eliminar
                                        </button>
                                    </div>
                                    <button wire:click="selectGroup({{ $group->id }})"
                                        class="text-xs text-forest-700 hover:text-forest-900 font-semibold flex items-center gap-1 px-2.5 py-1.5 rounded-lg hover:bg-forest-50 transition">
                                        Ver estudiantes
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    {{-- Paginación de grupos --}}
                    @if($groupsTotalPages > 1)
                        <div class="flex items-center justify-center gap-3 mt-6">
                            <button wire:click="groupPrevPage" @disabled($groupPage <= 1)
                                class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-slate-600 bg-white border border-cream-200 rounded-xl hover:bg-cream-50 hover:border-forest-300 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                                </svg>
                                Anterior
                            </button>

                            <div class="flex items-center gap-1">
                                @for($p = 1; $p <= $groupsTotalPages; $p++)
                                    <button wire:click="$set('groupPage', {{ $p }})"
                                        class="w-8 h-8 text-xs font-semibold rounded-lg transition
                                               {{ $groupPage === $p
                                                   ? 'bg-forest-800 text-white'
                                                   : 'text-slate-500 hover:bg-cream-100' }}">
                                        {{ $p }}
                                    </button>
                                @endfor
                            </div>

                            <button wire:click="groupNextPage" @disabled($groupPage >= $groupsTotalPages)
                                class="flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-slate-600 bg-white border border-cream-200 rounded-xl hover:bg-cream-50 hover:border-forest-300 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                Siguiente
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                                </svg>
                            </button>
                        </div>
                    @endif
                @endif

            {{-- ══════════════════════════════════════════════════════════
                 VISTA B — Estudiantes del grupo seleccionado
            ══════════════════════════════════════════════════════════ --}}
            @elseif($selectedGroup)

                {{-- Cabecera de la vista de grupo --}}
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div class="flex items-center gap-3">
                        <button wire:click="backToGroups()"
                            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 hover:bg-white border border-transparent hover:border-cream-200 transition">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                            </svg>
                        </button>
                        <div>
                            <h2 class="text-lg font-bold text-slate-800">{{ $selectedGroup->name }}</h2>
                            <p class="text-sm text-slate-500">{{ $selectedGroup->institution->name }} · Período {{ $selectedGroup->period }}</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button wire:click="openCreate('single', {{ $selectedGroup->id }})"
                            class="flex items-center gap-1.5 px-3 py-2 bg-forest-800 text-white text-xs font-semibold rounded-xl hover:bg-forest-700 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            Crear empresa
                        </button>
                        <button wire:click="openCreate('bulk', {{ $selectedGroup->id }})"
                            class="flex items-center gap-1.5 px-3 py-2 border border-forest-700 text-forest-700 text-xs font-medium rounded-xl hover:bg-forest-50 transition">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                            </svg>
                            Carga masiva
                        </button>
                    </div>
                </div>

                {{-- Tabla de estudiantes --}}
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                    @if($selectedGroup->tenants->isEmpty())
                        <div class="px-6 py-16 text-center">
                            <div class="w-14 h-14 bg-forest-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <svg class="w-7 h-7 text-forest-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                            </div>
                            <h3 class="text-sm font-semibold text-slate-700 mb-1">Sin empresas en este grupo</h3>
                            <p class="text-slate-400 text-xs mb-5">Agrega estudiantes para comenzar el ciclo contable.</p>
                            <button wire:click="openCreate('single', {{ $selectedGroup->id }})"
                                class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                                Crear primera empresa
                            </button>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="bg-cream-50 border-b border-cream-200">
                                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estudiante</th>
                                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                                        <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Facturas</th>
                                        <th class="text-right px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total facturado</th>
                                        <th class="text-center px-6 py-3.5 text-xs font-semibold text-slate-500 uppercase tracking-wide">Promedio</th>
                                        <th class="px-6 py-3.5"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-cream-100">
                                    @foreach($students as $s)
                                        <tr class="hover:bg-cream-50/60 transition">
                                            <td class="px-6 py-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-8 h-8 bg-forest-100 rounded-full flex items-center justify-center text-forest-700 font-bold text-xs shrink-0">
                                                        {{ strtoupper(mb_substr($s['tenant']->student_name, 0, 2)) }}
                                                    </div>
                                                    <div>
                                                        <p class="font-medium text-slate-700">{{ $s['tenant']->student_name }}</p>
                                                        <p class="text-xs text-slate-400">CC {{ $s['tenant']->id }}</p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <p class="text-slate-700 font-medium">{{ $s['tenant']->company_name }}</p>
                                                <p class="text-xs text-slate-400">NIT {{ $s['tenant']->nit_empresa }}</p>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="text-slate-700 font-semibold">{{ number_format($s['metrics']['invoices_count']) }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-right">
                                                <span class="text-slate-700 font-semibold">$ {{ number_format($s['metrics']['invoices_total'], 0, ',', '.') }}</span>
                                            </td>
                                            <td class="px-6 py-4 text-center">
                                                @if($s['promedio'] !== null)
                                                    @php
                                                        $color = $s['promedio'] >= 3.5
                                                            ? 'bg-green-100 text-green-700'
                                                            : ($s['promedio'] >= 3.0 ? 'bg-gold-100 text-gold-700' : 'bg-red-100 text-red-700');
                                                    @endphp
                                                    <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $color }}">{{ $s['promedio'] }}</span>
                                                    <p class="text-xs text-slate-400 mt-0.5">{{ $s['graded'] }} mód.</p>
                                                @else
                                                    <span class="text-xs text-slate-400">Sin notas</span>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('teacher.rubrica', $s['tenant']->id) }}"
                                                        class="text-xs text-slate-500 hover:text-slate-700 font-medium px-2.5 py-1.5 rounded-lg hover:bg-slate-50 transition">
                                                        Calificar
                                                    </a>
                                                    <a href="{{ route('teacher.auditar.start', $s['tenant']->id) }}"
                                                        class="text-xs text-forest-700 hover:text-forest-900 font-semibold px-2.5 py-1.5 rounded-lg hover:bg-forest-50 transition flex items-center gap-1">
                                                        Auditar
                                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                                        </svg>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

            @endif

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         Modal: Crear / editar grupo
    ════════════════════════════════════════════════════════════════ --}}
    @if($showGroupForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4"
             wire:click.self="$set('showGroupForm', false)">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="px-6 py-5 border-b border-cream-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-slate-800">
                        {{ $editingGroupId ? 'Editar grupo' : 'Nuevo grupo' }}
                    </h3>
                    <button wire:click="$set('showGroupForm', false)"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition text-sm">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del grupo</label>
                        <input wire:model="groupName" type="text" placeholder="Ej: Contabilidad 2025-1"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('groupName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Período</label>
                        <input wire:model="groupPeriod" type="text" placeholder="Ej: 2025-1"
                            class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('groupPeriod') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showGroupForm', false)"
                        class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">
                        Cancelar
                    </button>
                    <button wire:click="saveGroup" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveGroup">{{ $editingGroupId ? 'Guardar cambios' : 'Crear grupo' }}</span>
                        <span wire:loading wire:target="saveGroup">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════════════
         Modal: Crear empresa (individual o carga masiva)
    ════════════════════════════════════════════════════════════════ --}}
    @if($showCreateForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4"
             wire:click.self="$set('showCreateForm', false)">
            <div class="bg-white rounded-2xl shadow-xl w-full {{ $createMode === 'bulk' ? 'max-w-3xl' : 'max-w-md' }}">

                <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                    <div class="flex gap-1 bg-cream-100 rounded-xl p-1">
                        <button wire:click="switchMode('single')"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ $createMode === 'single' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                            Crear uno
                        </button>
                        <button wire:click="switchMode('bulk')"
                            class="px-3 py-1.5 text-xs font-medium rounded-lg transition {{ $createMode === 'bulk' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                            Carga masiva
                        </button>
                    </div>
                    <button wire:click="$set('showCreateForm', false)"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition text-sm">✕</button>
                </div>

                @if($createMode === 'single')
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Cédula del estudiante</label>
                            <input wire:model="cedula" type="text" placeholder="Ej: 1023456789"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('cedula') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del estudiante</label>
                            <input wire:model="studentName" type="text" placeholder="Nombre completo"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('studentName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Razón social de la empresa</label>
                            <input wire:model="companyName" type="text" placeholder="Nombre de la empresa"
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
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña inicial</label>
                            <input wire:model="password" type="password" placeholder="Mínimo 6 caracteres"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                        <button wire:click="$set('showCreateForm', false)"
                            class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">
                            Cancelar
                        </button>
                        <button wire:click="createCompany" wire:loading.attr="disabled"
                            class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="createCompany">Crear empresa</span>
                            <span wire:loading wire:target="createCompany">Creando…</span>
                        </button>
                    </div>

                @else
                    {{-- Carga masiva --}}
                    <div class="px-6 py-5 space-y-5">
                        @if(empty($bulkResults))
                            {{-- Instrucciones --}}
                            <div class="bg-forest-50 border border-forest-100 rounded-xl px-4 py-3 text-xs text-forest-700 space-y-1">
                                <p class="font-semibold">Formato del archivo CSV (5 columnas, sin espacios):</p>
                                <p class="font-mono">cedula, nombre_estudiante, nombre_empresa, nit_empresa, password</p>
                                <a href="{{ route('teacher.plantilla') }}" class="inline-flex items-center gap-1 text-forest-600 hover:underline mt-1">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                    </svg>
                                    Descargar plantilla
                                </a>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Archivo CSV</label>
                                <input wire:model="bulkFile" type="file" accept=".csv,.txt"
                                    class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-forest-50 file:text-forest-700 hover:file:bg-forest-100" />
                                @error('bulkFile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            @if($bulkError)
                                <div class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-xl px-4 py-3">{{ $bulkError }}</div>
                            @endif

                            @if(!empty($bulkPreview))
                                @php
                                    $previewSlice = array_slice($bulkPreview, ($bulkPreviewPage - 1) * $bulkPreviewPerPage, $bulkPreviewPerPage);
                                    $previewTotal = (int) ceil(count($bulkPreview) / $bulkPreviewPerPage);
                                    $hasErrors    = collect($bulkPreview)->contains(fn($r) => !empty($r['error']));
                                @endphp
                                <div>
                                    <p class="text-xs font-semibold text-slate-700 mb-2">Vista previa — {{ count($bulkPreview) }} filas</p>
                                    <div class="border border-cream-200 rounded-xl overflow-hidden">
                                        <table class="w-full text-xs">
                                            <thead>
                                                <tr class="bg-cream-50 border-b border-cream-200">
                                                    <th class="text-left px-3 py-2 font-semibold text-slate-500">Cédula</th>
                                                    <th class="text-left px-3 py-2 font-semibold text-slate-500">Nombre</th>
                                                    <th class="text-left px-3 py-2 font-semibold text-slate-500">Empresa</th>
                                                    <th class="text-left px-3 py-2 font-semibold text-slate-500">NIT</th>
                                                    <th class="text-left px-3 py-2 font-semibold text-slate-500">Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-cream-100">
                                                @foreach($previewSlice as $row)
                                                    <tr class="{{ !empty($row['error']) ? 'bg-red-50' : '' }}">
                                                        <td class="px-3 py-2 font-mono">{{ $row['cedula'] }}</td>
                                                        <td class="px-3 py-2">{{ $row['student_name'] }}</td>
                                                        <td class="px-3 py-2">{{ $row['company_name'] }}</td>
                                                        <td class="px-3 py-2">{{ $row['nit_empresa'] }}</td>
                                                        <td class="px-3 py-2">
                                                            @if(empty($row['error']))
                                                                <span class="text-green-600 font-semibold">OK</span>
                                                            @else
                                                                <span class="text-red-600">{{ $row['error'] }}</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($previewTotal > 1)
                                        <div class="flex items-center justify-between mt-2 text-xs text-slate-500">
                                            <button wire:click="bulkPreviewPrevPage" @disabled($bulkPreviewPage <= 1) class="px-2 py-1 rounded hover:bg-slate-100 disabled:opacity-40">← Anterior</button>
                                            <span>Página {{ $bulkPreviewPage }} de {{ $previewTotal }}</span>
                                            <button wire:click="bulkPreviewNextPage" @disabled($bulkPreviewPage >= $previewTotal) class="px-2 py-1 rounded hover:bg-slate-100 disabled:opacity-40">Siguiente →</button>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        @else
                            {{-- Resultados --}}
                            @php
                                $resultsSlice = array_slice($bulkResults, ($bulkResultsPage - 1) * $bulkPreviewPerPage, $bulkPreviewPerPage);
                                $resultsTotal = (int) ceil(count($bulkResults) / $bulkPreviewPerPage);
                                $created = count(array_filter($bulkResults, fn($r) => $r['status'] === 'ok'));
                                $skipped = count(array_filter($bulkResults, fn($r) => $r['status'] === 'skipped'));
                                $failed  = count(array_filter($bulkResults, fn($r) => $r['status'] === 'error'));
                            @endphp
                            <div class="flex gap-3 text-xs">
                                @if($created > 0)<span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-full font-semibold">✓ {{ $created }} creadas</span>@endif
                                @if($skipped > 0)<span class="px-2.5 py-1 bg-slate-100 text-slate-600 rounded-full font-semibold">↷ {{ $skipped }} ya existían</span>@endif
                                @if($failed  > 0)<span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-full font-semibold">✗ {{ $failed }} con error</span>@endif
                            </div>
                            <div class="border border-cream-200 rounded-xl overflow-hidden">
                                <table class="w-full text-xs">
                                    <thead>
                                        <tr class="bg-cream-50 border-b border-cream-200">
                                            <th class="text-left px-3 py-2 font-semibold text-slate-500">Cédula</th>
                                            <th class="text-left px-3 py-2 font-semibold text-slate-500">Nombre</th>
                                            <th class="text-left px-3 py-2 font-semibold text-slate-500">Resultado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-cream-100">
                                        @foreach($resultsSlice as $row)
                                            <tr class="{{ $row['status'] === 'error' ? 'bg-red-50' : ($row['status'] === 'skipped' ? 'bg-slate-50' : '') }}">
                                                <td class="px-3 py-2 font-mono">{{ $row['cedula'] }}</td>
                                                <td class="px-3 py-2">{{ $row['student_name'] }}</td>
                                                <td class="px-3 py-2">
                                                    @if($row['status'] === 'ok')
                                                        <span class="text-green-600 font-semibold">Creada</span>
                                                    @elseif($row['status'] === 'skipped')
                                                        <span class="text-slate-500">Ya existía</span>
                                                    @else
                                                        <span class="text-red-600">{{ $row['message'] }}</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($resultsTotal > 1)
                                <div class="flex items-center justify-between text-xs text-slate-500">
                                    <button wire:click="bulkResultsPrevPage" @disabled($bulkResultsPage <= 1) class="px-2 py-1 rounded hover:bg-slate-100 disabled:opacity-40">← Anterior</button>
                                    <span>Página {{ $bulkResultsPage }} de {{ $resultsTotal }}</span>
                                    <button wire:click="bulkResultsNextPage" @disabled($bulkResultsPage >= $resultsTotal) class="px-2 py-1 rounded hover:bg-slate-100 disabled:opacity-40">Siguiente →</button>
                                </div>
                            @endif
                        @endif
                    </div>

                    <div class="px-6 py-4 border-t border-cream-100 flex items-center justify-between">
                        <button wire:click="$set('showCreateForm', false)"
                            class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">
                            {{ empty($bulkResults) ? 'Cancelar' : 'Cerrar' }}
                        </button>
                        @if(empty($bulkResults))
                            @if(empty($bulkPreview))
                                <button wire:click="processBulkFile" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="processBulkFile">Previsualizar</span>
                                    <span wire:loading wire:target="processBulkFile">Procesando…</span>
                                </button>
                            @else
                                <button wire:click="confirmBulkCreate" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="confirmBulkCreate">Crear {{ count(array_filter($bulkPreview, fn($r) => empty($r['error']))) }} empresas</span>
                                    <span wire:loading wire:target="confirmBulkCreate">Creando…</span>
                                </button>
                            @endif
                        @endif
                    </div>
                @endif

            </div>
        </div>
    @endif

</div>
