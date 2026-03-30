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
                    @foreach(['resumen' => 'Resumen', 'docentes' => 'Docentes', 'estudiantes' => 'Estudiantes'] as $key => $label)
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
                                <button wire:click="openCreateTeacher"
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
                                                <button wire:click="openEditTeacher({{ $teacher->id }})"
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

                {{-- ── Tab Estudiantes ──────────────────────────────────── --}}
                @if($tab === 'estudiantes')
                    <div>
                        <div class="px-6 py-4 border-b border-cream-100">
                            <p class="text-sm text-slate-500">{{ $tenants->count() }} empresa(s) estudiantiles en esta institución</p>
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
    </div>

    {{-- ── Modal docente ──────────────────────────────────────────────── --}}
    @if($showTeacherForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" wire:click.self="$set('showTeacherForm',false)">
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

    {{-- ── Modal transferencia ────────────────────────────────────────── --}}
    @if($showTransferModal)
        @php $transferTenant = $tenants->firstWhere('id', $transferTenantId); @endphp
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" wire:click.self="$set('showTransferModal',false)">
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
