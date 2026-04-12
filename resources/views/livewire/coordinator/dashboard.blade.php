<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $institution->name }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">Panel de coordinación institucional</p>
            </div>
            <span class="px-3 py-1 bg-gold-100 text-gold-800 text-xs font-semibold rounded-full uppercase tracking-wide">Coordinador</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ── KPIs ─────────────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @php
                    $kpis = [
                        ['label' => 'Docentes',     'value' => $stats['docentes'],    'color' => 'forest'],
                        ['label' => 'Grupos',       'value' => $stats['grupos'],      'color' => 'gold'],
                        ['label' => 'Estudiantes',  'value' => $stats['estudiantes'], 'color' => 'forest'],
                        ['label' => 'Activos',      'value' => $stats['activos'],     'color' => 'gold'],
                    ];
                @endphp
                @foreach($kpis as $kpi)
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm p-5">
                        <p class="text-2xl font-bold {{ $kpi['color'] === 'gold' ? 'text-gold-600' : 'text-forest-800' }}">
                            {{ $kpi['value'] }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1">{{ $kpi['label'] }}</p>
                    </div>
                @endforeach
            </div>

            {{-- ── Tabs ─────────────────────────────────────────────────── --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card-sm overflow-hidden">
                <div class="flex border-b border-cream-100 overflow-x-auto">
                    @foreach(['resumen' => 'Resumen', 'docentes' => 'Docentes', 'grupos' => 'Grupos', 'estudiantes' => 'Estudiantes'] as $key => $label)
                        <button wire:click="$set('tab', '{{ $key }}')"
                            class="px-5 py-3.5 text-sm font-medium whitespace-nowrap transition-colors
                                   {{ $tab === $key
                                       ? 'border-b-2 border-forest-700 text-forest-800'
                                       : 'text-slate-500 hover:text-slate-700 hover:bg-cream-50' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                {{-- ── Tab Resumen ──────────────────────────────────────── --}}
                @if($tab === 'resumen')
                    <div class="p-6 space-y-6">

                        {{-- Grupos --}}
                        <div>
                            <h3 class="text-sm font-semibold text-slate-700 mb-3">Grupos activos</h3>
                            @forelse($groups as $group)
                                <div class="flex items-center justify-between py-3 border-b border-cream-50 last:border-0">
                                    <div>
                                        <p class="text-sm font-medium text-slate-800">{{ $group->name }}</p>
                                        <p class="text-xs text-slate-400">
                                            {{ $group->teacher->name ?? '—' }} · Período {{ $group->period }}
                                        </p>
                                    </div>
                                    <span class="text-xs font-semibold text-forest-700 bg-forest-50 px-2.5 py-1 rounded-full">
                                        {{ $group->tenants_count }} estudiantes
                                    </span>
                                </div>
                            @empty
                                <p class="text-sm text-slate-400">No hay grupos registrados aún.</p>
                            @endforelse
                        </div>
                    </div>
                @endif

                {{-- ── Tab Docentes ─────────────────────────────────────── --}}
                @if($tab === 'docentes')
                    <div>
                        <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                            <p class="text-sm text-slate-500">{{ $teachers->count() }} docente(s) en esta institución</p>
                            @if(! session('audit_mode'))
                                <button wire:click="openCreateTeacher" @click.stop
                                    class="px-4 py-2 bg-forest-800 text-white text-xs font-semibold rounded-xl hover:bg-forest-700 transition">
                                    + Nuevo docente
                                </button>
                            @endif
                        </div>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-cream-50 border-b border-cream-100">
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Docente</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Correo</th>
                                    <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Grupos</th>
                                    @if(! session('audit_mode'))
                                        <th class="px-6 py-3"></th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-cream-100">
                                @forelse($teachers as $teacher)
                                    <tr wire:key="teacher-{{ $teacher->id }}" class="hover:bg-cream-50 transition">
                                        <td class="px-6 py-3 font-medium text-slate-800">{{ $teacher->name }}</td>
                                        <td class="px-6 py-3 text-slate-500 text-xs">{{ $teacher->email }}</td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="text-xs font-semibold text-forest-700 bg-forest-50 px-2.5 py-1 rounded-full">
                                                {{ $teacher->groups_count }}
                                            </span>
                                        </td>
                                        @if(! session('audit_mode'))
                                            <td class="px-6 py-3 text-right">
                                                <button wire:click="openEditTeacher({{ $teacher->id }})" @click.stop
                                                    class="text-xs text-slate-500 hover:text-forest-700 mr-3 transition">Editar</button>
                                                <button wire:click="deleteTeacher({{ $teacher->id }})"
                                                    wire:confirm="¿Seguro que deseas eliminar este docente? Se perderán sus grupos."
                                                    class="text-xs text-red-400 hover:text-red-600 transition">Eliminar</button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                            No hay docentes registrados en esta institución.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- ── Tab Grupos ───────────────────────────────────────── --}}
                @if($tab === 'grupos')
                    <div>
                        <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                            <p class="text-sm text-slate-500">{{ $groups->count() }} grupo(s) en esta institución</p>
                            <button wire:click="openGroupForm()" @click.stop
                                class="px-4 py-2 bg-forest-800 text-white text-xs font-semibold rounded-xl hover:bg-forest-700 transition">
                                + Nuevo grupo
                            </button>
                        </div>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-cream-50 border-b border-cream-100">
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Grupo</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Docente</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Período</th>
                                    <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estudiantes</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-cream-100">
                                @forelse($groups as $group)
                                    <tr wire:key="group-{{ $group->id }}" class="hover:bg-cream-50 transition">
                                        <td class="px-6 py-3 font-medium text-slate-800">{{ $group->name }}</td>
                                        <td class="px-6 py-3 text-slate-500 text-xs">{{ $group->teacher->name ?? '—' }}</td>
                                        <td class="px-6 py-3 text-slate-500 text-xs">{{ $group->period }}</td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="text-xs font-semibold text-forest-700 bg-forest-50 px-2.5 py-1 rounded-full">
                                                {{ $group->tenants_count }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-right space-x-3">
                                            <button wire:click="openCreate('single', {{ $group->id }})" @click.stop
                                                class="text-xs text-forest-700 font-medium hover:text-forest-900 transition">+ Estudiante</button>
                                            <button wire:click="openGroupForm({{ $group->id }})" @click.stop
                                                class="text-xs text-slate-500 hover:text-forest-700 transition">Editar</button>
                                            <button
                                                x-on:click="confirmAction('¿Eliminar el grupo «{{ $group->name }}»? Solo es posible si no tiene estudiantes.', () => $wire.deleteGroup({{ $group->id }}), { danger: true, confirmText: 'Sí, eliminar' })"
                                                class="text-xs text-red-400 hover:text-red-600 transition">Eliminar</button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                            No hay grupos. Crea el primero con el botón de arriba.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- ── Tab Estudiantes ──────────────────────────────────── --}}
                @if($tab === 'estudiantes')
                    <div>
                        <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                            <p class="text-sm text-slate-500">{{ $tenants->count() }} empresa(s) estudiantiles en esta institución</p>
                            <div class="flex gap-2">
                                <button wire:click="openCreate('single')" @click.stop
                                    class="px-4 py-2 bg-forest-800 text-white text-xs font-semibold rounded-xl hover:bg-forest-700 transition">
                                    + Estudiante
                                </button>
                                <button wire:click="openCreate('bulk')" @click.stop
                                    class="px-4 py-2 border border-forest-700 text-forest-700 text-xs font-medium rounded-xl hover:bg-forest-50 transition">
                                    Carga masiva
                                </button>
                            </div>
                        </div>
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-cream-50 border-b border-cream-100">
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estudiante</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                                    <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Grupo / Docente</th>
                                    <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-cream-100">
                                @forelse($tenants as $tenant)
                                    <tr wire:key="student-{{ $tenant->id }}" class="hover:bg-cream-50 transition">
                                        <td class="px-6 py-3">
                                            <p class="font-medium text-slate-800">{{ $tenant->student_name }}</p>
                                            <p class="text-xs font-mono text-slate-400">{{ $tenant->id }}</p>
                                        </td>
                                        <td class="px-6 py-3">
                                            <p class="font-medium text-slate-700">{{ $tenant->company_name }}</p>
                                            <p class="text-xs text-slate-400">NIT {{ $tenant->nit_empresa }}</p>
                                        </td>
                                        <td class="px-6 py-3 text-xs text-slate-500">
                                            <p>{{ $tenant->group->name ?? '—' }}</p>
                                        </td>
                                        <td class="px-6 py-3 text-center">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium
                                                {{ $tenant->active ? 'bg-green-50 text-green-700' : 'bg-slate-100 text-slate-500' }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $tenant->active ? 'bg-green-500' : 'bg-slate-400' }}"></span>
                                                {{ $tenant->active ? 'Activa' : 'Inactiva' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-3 text-right space-x-3">
                                            @if(! session('audit_mode'))
                                                <a href="{{ route('coordinator.auditar.start', $tenant->id) }}"
                                                    class="text-xs text-slate-500 hover:text-forest-700 transition">
                                                    Auditar
                                                </a>
                                                <button wire:click="openTransfer('{{ $tenant->id }}')" @click.stop
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
    </div>

    {{-- ── Modal docente ──────────────────────────────────────────────── --}}
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

    {{-- ── Modal grupo ─────────────────────────────────────────────────── --}}
    @if($showGroupForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
                <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">{{ $groupEditingId ? 'Editar grupo' : 'Nuevo grupo' }}</h3>
                    <button wire:click="$set('showGroupForm',false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del grupo <span class="text-red-500">*</span></label>
                        <input wire:model="groupName" type="text" placeholder="Ej: Contabilidad 2026-1"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('groupName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Período <span class="text-red-500">*</span></label>
                        <input wire:model="groupPeriod" type="text" placeholder="Ej: 2026-1"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        @error('groupPeriod') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Docente <span class="text-red-500">*</span></label>
                        <select wire:model="groupTeacherId"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500 bg-white">
                            <option value="0">— Seleccionar docente —</option>
                            @foreach($teachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }}</option>
                            @endforeach
                        </select>
                        @error('groupTeacherId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showGroupForm',false)"
                        class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">Cancelar</button>
                    <button wire:click="saveGroup" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveGroup">{{ $groupEditingId ? 'Guardar cambios' : 'Crear grupo' }}</span>
                        <span wire:loading wire:target="saveGroup">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Modal crear estudiante ───────────────────────────────────────── --}}
    @if($showCreateForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4"
            >
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
                    <button wire:click="$set('showCreateForm',false)"
                        class="w-7 h-7 flex items-center justify-center rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition">✕</button>
                </div>

                @if($createMode === 'single')
                    <div class="px-6 py-5 space-y-4">
                        @if($groups->count() > 1)
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Grupo <span class="text-red-500">*</span></label>
                                <select wire:model="createForGroupId"
                                    class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 bg-white">
                                    <option value="">— Seleccionar grupo —</option>
                                    @foreach($groups as $g)
                                        <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->teacher->name ?? '?' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Cédula del estudiante <span class="text-red-500">*</span></label>
                            <input wire:model="cedula" type="text" placeholder="Ej: 1023456789"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('cedula') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre del estudiante <span class="text-red-500">*</span></label>
                            <input wire:model="studentName" type="text" placeholder="Nombre completo"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('studentName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Razón social <span class="text-red-500">*</span></label>
                            <input wire:model="companyName" type="text" placeholder="Nombre de la empresa"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('companyName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">NIT empresa <span class="text-red-500">*</span></label>
                            <input wire:model="nitEmpresa" type="text" placeholder="Ej: 900123456-1"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('nitEmpresa') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Contraseña inicial <span class="text-red-500">*</span></label>
                            <input wire:model="password" type="password" placeholder="Mínimo 6 caracteres"
                                class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                        <button wire:click="$set('showCreateForm',false)"
                            class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 rounded-xl hover:bg-slate-50 transition">Cancelar</button>
                        <button wire:click="createCompany" wire:loading.attr="disabled"
                            class="px-4 py-2 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="createCompany">Crear empresa</span>
                            <span wire:loading wire:target="createCompany">Creando…</span>
                        </button>
                    </div>
                @else
                    <div class="px-6 py-5 space-y-5">
                        @if(empty($bulkResults))
                            @if($groups->count() > 1)
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1.5">Grupo destino <span class="text-red-500">*</span></label>
                                    <select wire:model="createForGroupId"
                                        class="block w-full rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 bg-white">
                                        <option value="">— Seleccionar grupo —</option>
                                        @foreach($groups as $g)
                                            <option value="{{ $g->id }}">{{ $g->name }} ({{ $g->teacher->name ?? '?' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="bg-forest-50 border border-forest-100 rounded-xl px-4 py-3 text-xs text-forest-700 space-y-1">
                                <p class="font-semibold">Formato CSV (5 columnas):</p>
                                <p class="font-mono">cedula, nombre_estudiante, nombre_empresa, nit_empresa, password</p>
                                <a href="{{ route('coordinator.plantilla') }}" class="inline-flex items-center gap-1 text-forest-600 hover:underline mt-1">
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
                                                    <th class="text-left px-3 py-2 font-semibold text-slate-500">Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-cream-100">
                                                @foreach($previewSlice as $row)
                                                    <tr class="{{ !empty($row['error']) ? 'bg-red-50' : '' }}">
                                                        <td class="px-3 py-2 font-mono">{{ $row['cedula'] }}</td>
                                                        <td class="px-3 py-2">{{ $row['student_name'] }}</td>
                                                        <td class="px-3 py-2">{{ $row['company_name'] }}</td>
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
                            @php
                                $resultsSlice = array_slice($bulkResults, ($bulkResultsPage - 1) * $bulkPreviewPerPage, $bulkPreviewPerPage);
                                $resultsTotal = (int) ceil(count($bulkResults) / $bulkPreviewPerPage);
                                $created = count(array_filter($bulkResults, fn($r) => $r['status'] === 'ok'));
                                $skipped = count(array_filter($bulkResults, fn($r) => $r['status'] === 'skipped'));
                                $failed  = count(array_filter($bulkResults, fn($r) => $r['status'] === 'error'));
                            @endphp
                            <div class="flex gap-3 text-xs flex-wrap">
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
                                                    @if($row['status'] === 'ok') <span class="text-green-600 font-semibold">Creada</span>
                                                    @elseif($row['status'] === 'skipped') <span class="text-slate-500">Ya existía</span>
                                                    @else <span class="text-red-600">{{ $row['message'] }}</span>
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
                        <button wire:click="$set('showCreateForm',false)"
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

    {{-- ── Modal transferencia ────────────────────────────────────────── --}}
    @if($showTransferModal)
        @php $transferTenant = $tenants->firstWhere('id', $transferTenantId); @endphp
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl shadow-card-lg w-full max-w-lg">
                <div class="px-6 py-4 border-b border-cream-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-slate-800">Transferir estudiante</h3>
                        @if($transferTenant)
                            <p class="text-xs text-slate-500 mt-0.5">{{ $transferTenant->student_name }} — {{ $transferTenant->company_name }}</p>
                        @endif
                    </div>
                    <button wire:click="$set('showTransferModal',false)" class="text-slate-400 hover:text-slate-600 text-xl leading-none">&times;</button>
                </div>
                <div class="px-6 py-5 space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Grupo destino <span class="text-red-500">*</span></label>
                        <select wire:model="transferGroupId"
                            class="block w-full rounded-xl border-cream-300 text-sm focus:ring-forest-500 focus:border-forest-500 bg-white">
                            <option value="0">— Seleccionar grupo —</option>
                            @foreach($groups as $group)
                                <option value="{{ $group->id }}"
                                    @if($transferTenant && $transferTenant->group_id === $group->id) disabled @endif>
                                    {{ $group->name }} — {{ $group->teacher->name ?? '?' }} ({{ $group->period }})
                                    @if($transferTenant && $transferTenant->group_id === $group->id) [actual] @endif
                                </option>
                            @endforeach
                        </select>
                        @error('transferGroupId') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">¿Qué pasa con los datos contables?</label>
                        <div class="space-y-2">
                            @foreach([
                                ['keep',  'Conservar todo',               'Solo cambia de grupo.',                                                    ''],
                                ['reset', 'Reiniciar transacciones',       'Borra facturas y asientos. Conserva PUC, terceros y productos.',           'border-yellow-200'],
                                ['fresh', 'Empresa nueva (desde cero)',    'Schema completamente nuevo. Solo queda el PUC.',                           'border-red-200'],
                            ] as [$val, $title, $desc, $border])
                                <label class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition
                                    {{ $transferMode === $val ? ($val === 'fresh' ? 'border-red-400 bg-red-50' : ($val === 'reset' ? 'border-yellow-400 bg-yellow-50' : 'border-forest-400 bg-forest-50')) : 'border-cream-200 hover:bg-cream-50' }}">
                                    <input wire:model="transferMode" type="radio" value="{{ $val }}" class="mt-0.5 text-forest-600 focus:ring-forest-500" />
                                    <div>
                                        <p class="text-sm font-medium text-slate-800">{{ $title }}</p>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $desc }}</p>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div class="flex items-start gap-2 p-3 bg-blue-50 rounded-xl border border-blue-100 text-xs text-blue-700">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/>
                        </svg>
                        <span>Las notas actuales quedarán archivadas. El nuevo docente verá la rúbrica en blanco.</span>
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-cream-100 flex justify-end gap-3">
                    <button wire:click="$set('showTransferModal',false)"
                        class="px-4 py-2 text-sm text-slate-600 bg-cream-100 rounded-xl hover:bg-cream-200 transition">Cancelar</button>
                    <button wire:click="confirmTransfer" wire:loading.attr="disabled"
                        class="px-4 py-2 text-sm font-semibold rounded-xl transition disabled:opacity-50
                            {{ $transferMode === 'fresh' ? 'bg-red-600 hover:bg-red-700 text-white' : ($transferMode === 'reset' ? 'bg-yellow-500 hover:bg-yellow-600 text-white' : 'bg-forest-800 hover:bg-forest-700 text-white') }}">
                        <span wire:loading.remove wire:target="confirmTransfer">Confirmar transferencia</span>
                        <span wire:loading wire:target="confirmTransfer">Procesando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

</div>
