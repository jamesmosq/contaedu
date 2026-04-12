<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Panel de administración</h2>
                <p class="text-sm text-slate-500 mt-0.5">Visión global de la plataforma ContaEdu</p>
            </div>
            <span class="px-3 py-1 bg-forest-100 text-forest-800 text-xs font-semibold rounded-full uppercase tracking-wide">Superadmin</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ── Hero banner ─────────────────────────────────────────── --}}
            <div class="bg-gradient-to-r from-forest-950 to-forest-800 rounded-2xl p-6 md:p-8 text-white relative overflow-hidden">
                <div class="absolute inset-0 pointer-events-none">
                    <div class="absolute -top-16 -right-16 w-64 h-64 bg-gold-500 rounded-full opacity-5 blur-3xl"></div>
                    <div class="absolute -bottom-12 -left-12 w-48 h-48 bg-forest-600 rounded-full opacity-20 blur-3xl"></div>
                </div>
                <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <p class="text-forest-300 text-sm font-medium mb-1">Bienvenido,</p>
                        <h3 class="text-2xl font-bold">{{ auth('web')->user()?->name }}</h3>
                        <p class="text-forest-300 text-sm mt-1">Gestiona instituciones, docentes y monitorea la actividad de todos los estudiantes.</p>
                    </div>
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="text-center bg-white/10 rounded-xl px-5 py-3">
                            <p class="text-2xl font-bold text-gold-400">{{ $stats['estudiantes'] }}</p>
                            <p class="text-xs text-forest-300 mt-0.5">Estudiantes</p>
                        </div>
                        <div class="text-center bg-white/10 rounded-xl px-5 py-3">
                            <p class="text-2xl font-bold text-gold-400">{{ $stats['instituciones'] }}</p>
                            <p class="text-xs text-forest-300 mt-0.5">Instituciones</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── KPI cards ────────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $kpis = [
                        ['label' => 'Instituciones', 'value' => $stats['instituciones'], 'sub' => 'registradas', 'color' => 'forest', 'icon' => 'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z'],
                        ['label' => 'Docentes',       'value' => $stats['docentes'],       'sub' => 'activos',     'color' => 'gold',   'icon' => 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5'],
                        ['label' => 'Grupos',         'value' => $stats['grupos'],         'sub' => 'activos',     'color' => 'forest', 'icon' => 'M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z'],
                        ['label' => 'Estudiantes',    'value' => $stats['activos'],        'sub' => 'con empresa activa', 'color' => 'gold', 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM1.5 20.25a6.75 6.75 0 0 1 13.5 0v.25H1.5v-.25Z M22.5 20.25v-.25a6.75 6.75 0 0 0-6.75-6.75h-.75'],
                    ];
                @endphp
                @foreach($kpis as $kpi)
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm p-5 flex items-start gap-4">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                            {{ $kpi['color'] === 'gold' ? 'bg-gold-50' : 'bg-forest-50' }}">
                            <svg class="w-5 h-5 {{ $kpi['color'] === 'gold' ? 'text-gold-600' : 'text-forest-700' }}"
                                fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold {{ $kpi['color'] === 'gold' ? 'text-gold-600' : 'text-forest-800' }}">{{ $kpi['value'] }}</p>
                            <p class="text-sm font-semibold text-slate-700">{{ $kpi['label'] }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">{{ $kpi['sub'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- ── Tabs ─────────────────────────────────────────────────── --}}
            <div class="flex border-b border-cream-300 gap-1 -mb-2">
                @foreach(['resumen' => 'Resumen', 'instituciones' => 'Instituciones', 'coordinadores' => 'Coordinadores', 'docentes' => 'Docentes', 'estudiantes' => 'Estudiantes'] as $key => $label)
                    <button wire:click="$set('tab','{{ $key }}')"
                        class="px-4 py-2.5 text-sm font-medium transition border-b-2
                            {{ $tab === $key
                                ? 'border-forest-700 text-forest-800'
                                : 'border-transparent text-slate-500 hover:text-slate-700' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- ── TAB RESUMEN ─────────────────────────────────────────── --}}
            @if($tab === 'resumen')
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Instituciones con métricas --}}
                    <div class="lg:col-span-2 bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-cream-100 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-slate-700">Instituciones y actividad</h3>
                            <button wire:click="openCreateInst; $set('tab','instituciones')"
                                class="text-xs px-3 py-1.5 bg-forest-800 text-white rounded-lg hover:bg-forest-700 transition">
                                + Nueva
                            </button>
                        </div>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-cream-50 border-b border-cream-100">
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Institución</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Grupos</th>
                                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estudiantes</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-cream-100">
                                @forelse($institutions as $inst)
                                    <tr class="hover:bg-cream-50 transition">
                                        <td class="px-5 py-3">
                                            <p class="font-medium text-slate-800">{{ $inst->name }}</p>
                                            @if($inst->city)
                                                <p class="text-xs text-slate-400">{{ $inst->city }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-forest-50 text-forest-700 text-xs font-bold">
                                                {{ $inst->groups_count }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gold-50 text-gold-700 text-xs font-bold">
                                                {{ $inst->students_count }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-5 py-8 text-center text-sm text-slate-400">
                                            No hay instituciones. <button wire:click="openCreateInst" class="text-forest-600 hover:underline">Crea la primera</button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Actividad reciente --}}
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
                        <div class="px-5 py-4 border-b border-cream-100">
                            <h3 class="text-sm font-semibold text-slate-700">Empresas recientes</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Últimas empresas creadas</p>
                        </div>
                        <ul class="divide-y divide-cream-100">
                            @forelse($recentTenants as $tenant)
                                <li class="px-5 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-forest-800 flex items-center justify-center shrink-0">
                                            <span class="text-xs font-bold text-gold-400">{{ strtoupper(substr($tenant->company_name, 0, 1)) }}</span>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-medium text-slate-800 truncate">{{ $tenant->company_name }}</p>
                                            <p class="text-xs text-slate-400 truncate">{{ $tenant->student_name }}</p>
                                        </div>
                                        <span class="shrink-0 text-xs {{ $tenant->active ? 'text-green-600' : 'text-slate-400' }}">
                                            {{ $tenant->active ? '●' : '○' }}
                                        </span>
                                    </div>
                                </li>
                            @empty
                                <li class="px-5 py-8 text-center text-sm text-slate-400">Sin empresas aún.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            @endif

            {{-- ── TAB INSTITUCIONES ───────────────────────────────────── --}}
            @if($tab === 'instituciones')
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-700">Instituciones registradas</h3>
                        <button wire:click="openCreateInst"
                            class="px-3 py-1.5 bg-forest-800 text-white text-xs font-semibold rounded-lg hover:bg-forest-700 transition">
                            + Nueva institución
                        </button>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-cream-50 border-b border-cream-100">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">NIT</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Ciudad</th>
                                <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Grupos</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @forelse($institutions as $inst)
                                <tr wire:key="inst-{{ $inst->id }}" class="hover:bg-cream-50 transition">
                                    <td class="px-6 py-4 font-medium text-slate-800">{{ $inst->name }}</td>
                                    <td class="px-6 py-4 text-slate-500 font-mono text-xs">{{ $inst->nit ?? '—' }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $inst->city ?? '—' }}</td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-forest-50 text-forest-700 text-xs font-bold">
                                            {{ $inst->groups_count }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-3">
                                            <button wire:click="openEditInst({{ $inst->id }})"
                                                class="text-xs text-forest-600 hover:text-forest-800 font-medium">Editar</button>
                                            <button x-on:click="confirmAction('¿Eliminar esta institución?', () => $wire.deleteInst({{ $inst->id }}), {danger: true, confirmText: 'Sí, eliminar'})"
                                                class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                        No hay instituciones.
                                        <button wire:click="openCreateInst" class="ml-2 text-forest-600 hover:underline">Crear la primera</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ── TAB COORDINADORES ──────────────────────────────────── --}}
            @if($tab === 'coordinadores')
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700">Coordinadores por institución</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Cada institución puede tener un coordinador que gestiona sus docentes y estudiantes.</p>
                        </div>
                        <button wire:click="openCreateCoordinator"
                            class="px-3 py-1.5 bg-forest-800 text-white text-xs font-semibold rounded-lg hover:bg-forest-700 transition">
                            + Nuevo coordinador
                        </button>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-cream-50 border-b border-cream-100">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Coordinador</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Email</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Institución asignada</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @forelse($coordinators as $coord)
                                <tr wire:key="coord-{{ $coord->id }}" class="hover:bg-cream-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-gold-100 flex items-center justify-center shrink-0">
                                                <span class="text-xs font-bold text-gold-700">{{ strtoupper(substr($coord->name, 0, 1)) }}</span>
                                            </div>
                                            <span class="font-medium text-slate-800">{{ $coord->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">{{ $coord->email }}</td>
                                    <td class="px-6 py-4">
                                        @if($coord->coordinatedInstitution)
                                            <div class="flex items-center gap-2">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 shrink-0"></span>
                                                <span class="text-slate-700 text-xs font-medium">{{ $coord->coordinatedInstitution->name }}</span>
                                            </div>
                                        @else
                                            <span class="text-xs text-slate-400 italic">Sin institución asignada</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-3">
                                            <button wire:click="openEditCoordinator({{ $coord->id }})"
                                                class="text-xs text-forest-600 hover:text-forest-800 font-medium">Editar</button>
                                            <button x-on:click="confirmAction('¿Eliminar este coordinador? Se liberará su institución asignada.', () => $wire.deleteCoordinator({{ $coord->id }}), {danger: true, confirmText: 'Sí, eliminar'})"
                                                class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                        No hay coordinadores.
                                        <button wire:click="openCreateCoordinator" class="ml-2 text-forest-600 hover:underline">Crear el primero</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Instituciones sin coordinador --}}
                @php $sinCoord = $institutions->whereNull('coordinator_id'); @endphp
                @if($sinCoord->isNotEmpty())
                    <div class="bg-amber-50 border border-amber-200 rounded-2xl px-6 py-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                            </svg>
                            <div>
                                <p class="text-sm font-semibold text-amber-800">Instituciones sin coordinador</p>
                                <p class="text-xs text-amber-700 mt-0.5">
                                    {{ $sinCoord->pluck('name')->implode(', ') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            @endif

            {{-- ── TAB DOCENTES ────────────────────────────────────────── --}}
            @if($tab === 'docentes')
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-700">Docentes registrados</h3>
                        <button wire:click="openCreateTeacher"
                            class="px-3 py-1.5 bg-forest-800 text-white text-xs font-semibold rounded-lg hover:bg-forest-700 transition">
                            + Nuevo docente
                        </button>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-cream-50 border-b border-cream-100">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Docente</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Email</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Institución / Grupo</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @forelse($teachers as $teacher)
                                <tr wire:key="teacher-{{ $teacher->id }}" class="hover:bg-cream-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 rounded-full bg-forest-100 flex items-center justify-center shrink-0">
                                                <span class="text-xs font-bold text-forest-700">{{ strtoupper(substr($teacher->name, 0, 1)) }}</span>
                                            </div>
                                            <span class="font-medium text-slate-800">{{ $teacher->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-500">{{ $teacher->email }}</td>
                                    <td class="px-6 py-4 text-slate-500 text-xs space-y-0.5">
                                        @foreach($teacher->teacherGroups as $g)
                                            <div>{{ $g->institution->name }} — {{ $g->name }}</div>
                                        @endforeach
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-3">
                                            <button wire:click="openEditTeacher({{ $teacher->id }})"
                                                class="text-xs text-forest-600 hover:text-forest-800 font-medium">Editar</button>
                                            <button x-on:click="confirmAction('¿Eliminar este docente?', () => $wire.deleteTeacher({{ $teacher->id }}), {danger: true, confirmText: 'Sí, eliminar'})"
                                                class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                        No hay docentes.
                                        <button wire:click="openCreateTeacher" class="ml-2 text-forest-600 hover:underline">Crear el primero</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- ── TAB ESTUDIANTES ─────────────────────────────────────── --}}
            @if($tab === 'estudiantes')
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-cream-100">
                        <h3 class="text-sm font-semibold text-slate-700">Todas las empresas estudiantiles</h3>
                        <p class="text-xs text-slate-400 mt-0.5">{{ $tenants->count() }} empresa(s) registradas en la plataforma</p>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-cream-50 border-b border-cream-100">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estudiante</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Institución / Grupo</th>
                                <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Creada</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-cream-100">
                            @forelse($tenants as $tenant)
                                <tr wire:key="tenant-{{ $tenant->id }}" class="hover:bg-cream-50 transition">
                                    <td class="px-6 py-3">
                                        <p class="font-medium text-slate-800">{{ $tenant->student_name }}</p>
                                        <p class="text-xs font-mono text-slate-400">{{ $tenant->id }}</p>
                                    </td>
                                    <td class="px-6 py-3">
                                        <p class="font-medium text-slate-700">{{ $tenant->company_name }}</p>
                                        @if($tenant->nit_empresa)
                                            <p class="text-xs text-slate-400">NIT {{ $tenant->nit_empresa }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-xs text-slate-500">
                                        @if($tenant->group)
                                            <p>{{ $tenant->group->institution->name ?? '—' }}</p>
                                            <p class="text-slate-400">{{ $tenant->group->name }}</p>
                                        @else
                                            <span class="text-slate-300">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-center">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                            {{ $tenant->active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $tenant->active ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                                            {{ $tenant->active ? 'Activa' : 'Inactiva' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-xs text-slate-400">
                                        {{ $tenant->created_at?->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-3 text-right">
                                        @if($tenant->isStudent())
                                            <button wire:click="openTransfer('{{ $tenant->id }}')"
                                                class="text-xs text-forest-700 font-medium hover:text-forest-900 hover:underline transition">
                                                Transferir
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                        No hay empresas estudiantiles registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>

    {{-- ── Modal transferencia de estudiante ────────────────────────── --}}
    @if($showTransferModal)
        @php $transferTenant = $tenants->firstWhere('id', $transferTenantId); @endphp
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-card-lg w-full max-w-lg">
                <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-800">Transferir estudiante</h3>
                        @if($transferTenant)
                            <p class="text-xs text-slate-500 mt-0.5">
                                {{ $transferTenant->student_name }} — {{ $transferTenant->company_name }}
                            </p>
                        @endif
                    </div>
                    <button wire:click="$set('showTransferModal',false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>

                <div class="px-6 py-5 space-y-5">
                    {{-- Grupo destino --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Grupo destino <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="transferGroupId"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500 bg-white">
                            <option value="0">— Seleccionar grupo —</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}"
                                    @if($transferTenant && $transferTenant->group_id === $group->id) disabled @endif>
                                    {{ $group->teacher->name ?? '?' }} — {{ $group->name }}
                                    ({{ $group->institution->name ?? '?' }}, {{ $group->period }})
                                    @if($transferTenant && $transferTenant->group_id === $group->id) [actual] @endif
                                </option>
                            @endforeach
                        </select>
                        @error('transferGroupId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    {{-- Modo de transferencia --}}
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">¿Qué pasa con los datos contables?</label>
                        <div class="space-y-2.5">
                            <label class="flex items-start gap-3 p-3 rounded-xl border border-cream-200 cursor-pointer hover:bg-cream-50 transition
                                {{ $transferMode === 'keep' ? 'border-forest-400 bg-forest-50' : '' }}">
                                <input wire:model="transferMode" type="radio" value="keep" class="mt-0.5 text-forest-600 focus:ring-forest-500" />
                                <div>
                                    <p class="text-sm font-medium text-slate-800">Conservar todo</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Solo cambia de grupo. Facturas, asientos y saldos permanecen intactos.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-3 rounded-xl border border-cream-200 cursor-pointer hover:bg-cream-50 transition
                                {{ $transferMode === 'reset' ? 'border-yellow-400 bg-yellow-50' : '' }}">
                                <input wire:model="transferMode" type="radio" value="reset" class="mt-0.5 text-forest-600 focus:ring-forest-500" />
                                <div>
                                    <p class="text-sm font-medium text-slate-800">Reiniciar transacciones</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Borra facturas, compras y asientos contables. Conserva PUC, configuración, terceros y productos.</p>
                                </div>
                            </label>
                            <label class="flex items-start gap-3 p-3 rounded-xl border border-cream-200 cursor-pointer hover:bg-cream-50 transition
                                {{ $transferMode === 'fresh' ? 'border-red-400 bg-red-50' : '' }}">
                                <input wire:model="transferMode" type="radio" value="fresh" class="mt-0.5 text-forest-600 focus:ring-forest-500" />
                                <div>
                                    <p class="text-sm font-medium text-slate-800">Empresa nueva (desde cero)</p>
                                    <p class="text-xs text-slate-500 mt-0.5">Recrea el schema completo. Solo queda el PUC sembrado. Se pierden todos los datos.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Aviso de notas --}}
                    <div class="flex items-start gap-2 p-3 bg-blue-50 rounded-xl border border-blue-100 text-xs text-blue-700">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                        </svg>
                        <span>Las notas del período actual quedarán archivadas con el período <strong>{{ $transferTenant?->group?->period ?? now()->format('Y').'-'.ceil(now()->month / 6) }}</strong>. El nuevo docente verá la rúbrica en blanco.</span>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showTransferModal',false)"
                        class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">
                        Cancelar
                    </button>
                    <button wire:click="confirmTransfer" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-semibold rounded-xl transition disabled:opacity-50
                            {{ $transferMode === 'fresh' ? 'bg-red-600 hover:bg-red-700 text-white' : ($transferMode === 'reset' ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-forest-800 hover:bg-forest-700 text-white') }}">
                        <span wire:loading.remove wire:target="confirmTransfer">
                            {{ $transferMode === 'fresh' ? 'Transferir y recrear empresa' : ($transferMode === 'reset' ? 'Transferir y reiniciar' : 'Transferir') }}
                        </span>
                        <span wire:loading wire:target="confirmTransfer">Procesando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Modal institución ───────────────────────────────────────────── --}}
    @if($showInstForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-card-lg w-full max-w-md">
                <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">{{ $instEditingId ? 'Editar institución' : 'Nueva institución' }}</h3>
                    <button wire:click="$set('showInstForm',false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input wire:model="instName" type="text" placeholder="Nombre de la institución"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('instName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">NIT</label>
                        <input wire:model="instNit" type="text" placeholder="Ej: 800123456-1"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ciudad</label>
                        <input wire:model="instCity" type="text" placeholder="Ej: Bogotá"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500" />
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showInstForm',false)"
                        class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">Cancelar</button>
                    <button wire:click="saveInst" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveInst">Guardar</span>
                        <span wire:loading wire:target="saveInst">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Modal coordinador ─────────────────────────────────────────── --}}
    @if($showCoordinatorForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-card-lg w-full max-w-md">
                <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-800">{{ $coordinatorEditingId ? 'Editar coordinador' : 'Nuevo coordinador' }}</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Accede al panel de su institución y gestiona docentes y estudiantes.</p>
                    </div>
                    <button wire:click="$set('showCoordinatorForm',false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                        <input wire:model="coordinatorName" type="text" placeholder="Nombre del coordinador"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('coordinatorName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Correo electrónico <span class="text-red-500">*</span></label>
                        <input wire:model="coordinatorEmail" type="email" placeholder="correo@institucion.edu.co"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('coordinatorEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Institución <span class="text-red-500">*</span></label>
                        <select wire:model="coordinatorInstitutionId"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500 bg-white">
                            <option value="0">— Seleccionar —</option>
                            @foreach($institutions as $inst)
                                <option value="{{ $inst->id }}">
                                    {{ $inst->name }}
                                    @if($inst->coordinator_id && $inst->coordinator_id !== $coordinatorEditingId)
                                        (ya tiene coordinador)
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('coordinatorInstitutionId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Contraseña {{ $coordinatorEditingId ? '(dejar en blanco para no cambiar)' : '*' }}
                        </label>
                        <input wire:model="coordinatorPassword" type="password" placeholder="Mínimo 6 caracteres"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('coordinatorPassword') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showCoordinatorForm',false)"
                        class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">Cancelar</button>
                    <button wire:click="saveCoordinator" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveCoordinator">Guardar</span>
                        <span wire:loading wire:target="saveCoordinator">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Modal docente ───────────────────────────────────────────────── --}}
    @if($showTeacherForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-card-lg w-full max-w-md">
                <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">{{ $teacherEditingId ? 'Editar docente' : 'Nuevo docente' }}</h3>
                    <button wire:click="$set('showTeacherForm',false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                        <input wire:model="teacherName" type="text" placeholder="Nombre del docente"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('teacherName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Correo electrónico <span class="text-red-500">*</span></label>
                        <input wire:model="teacherEmail" type="email" placeholder="correo@institucion.edu.co"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('teacherEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @if(!$teacherEditingId)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Institución <span class="text-red-500">*</span></label>
                            <select wire:model="teacherInstitution"
                                class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500 bg-white">
                                <option value="0">— Seleccionar —</option>
                                @foreach($institutions as $inst)
                                    <option value="{{ $inst->id }}">{{ $inst->name }}</option>
                                @endforeach
                            </select>
                            @error('teacherInstitution') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">
                            Contraseña {{ $teacherEditingId ? '(dejar en blanco para no cambiar)' : '*' }}
                        </label>
                        <input wire:model="teacherPassword" type="password" placeholder="Mínimo 6 caracteres"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('teacherPassword') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showTeacherForm',false)"
                        class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">Cancelar</button>
                    <button wire:click="saveTeacher" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveTeacher">Guardar</span>
                        <span wire:loading wire:target="saveTeacher">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
