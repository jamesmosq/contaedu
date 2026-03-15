<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Panel Superadministrador</h2>
                <p class="text-sm text-slate-500 mt-0.5">Gestión de instituciones y docentes</p>
            </div>
            <span class="px-3 py-1 bg-brand-100 text-brand-800 text-xs font-semibold rounded-full uppercase tracking-wide">Superadmin</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-accent-50 border border-accent-200 rounded-xl text-accent-700 text-sm">{{ session('success') }}</div>
            @endif

            {{-- Métricas --}}
            <div class="grid grid-cols-2 gap-5 mb-8">
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Instituciones</p>
                    <p class="text-3xl font-bold text-brand-800">{{ $institutions->count() }}</p>
                </div>
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Docentes</p>
                    <p class="text-3xl font-bold text-accent-700">{{ $teachers->count() }}</p>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="flex border-b border-slate-200 mb-6 gap-1">
                <button wire:click="$set('tab','instituciones')"
                    class="px-4 py-2 text-sm font-medium transition {{ $tab === 'instituciones' ? 'border-b-2 border-brand-700 text-brand-800' : 'text-slate-500 hover:text-slate-700' }}">
                    Instituciones
                </button>
                <button wire:click="$set('tab','docentes')"
                    class="px-4 py-2 text-sm font-medium transition {{ $tab === 'docentes' ? 'border-b-2 border-brand-700 text-brand-800' : 'text-slate-500 hover:text-slate-700' }}">
                    Docentes
                </button>
            </div>

            {{-- TAB INSTITUCIONES --}}
            @if($tab === 'instituciones')
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-700">Instituciones registradas</h3>
                        <button wire:click="openCreateInst" class="px-3 py-1.5 bg-brand-800 text-white text-xs font-medium rounded-lg hover:bg-brand-700 transition">
                            + Nueva institución
                        </button>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">NIT</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Ciudad</th>
                                <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Grupos</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($institutions as $inst)
                                <tr wire:key="inst-{{ $inst->id }}" class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-medium text-slate-800">{{ $inst->name }}</td>
                                    <td class="px-6 py-4 text-slate-500 font-mono text-xs">{{ $inst->nit ?? '—' }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $inst->city ?? '—' }}</td>
                                    <td class="px-6 py-4 text-center text-slate-600">{{ $inst->groups_count }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button wire:click="openEditInst({{ $inst->id }})" class="text-xs text-brand-600 hover:text-brand-800 font-medium">Editar</button>
                                            <button wire:click="deleteInst({{ $inst->id }})" wire:confirm="¿Eliminar esta institución?" class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-10 text-center text-slate-400">
                                        No hay instituciones.
                                        <button wire:click="openCreateInst" class="ml-2 text-brand-600 hover:underline">Crear la primera</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            {{-- TAB DOCENTES --}}
            @if($tab === 'docentes')
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-slate-700">Docentes registrados</h3>
                        <button wire:click="openCreateTeacher" class="px-3 py-1.5 bg-brand-800 text-white text-xs font-medium rounded-lg hover:bg-brand-700 transition">
                            + Nuevo docente
                        </button>
                    </div>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Email</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Institución / Grupo</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($teachers as $teacher)
                                <tr wire:key="teacher-{{ $teacher->id }}" class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4 font-medium text-slate-800">{{ $teacher->name }}</td>
                                    <td class="px-6 py-4 text-slate-500">{{ $teacher->email }}</td>
                                    <td class="px-6 py-4 text-slate-500 text-xs">
                                        @foreach($teacher->teacherGroups as $g)
                                            <span class="block">{{ $g->institution->name }} — {{ $g->name }}</span>
                                        @endforeach
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <button wire:click="openEditTeacher({{ $teacher->id }})" class="text-xs text-brand-600 hover:text-brand-800 font-medium">Editar</button>
                                            <button wire:click="deleteTeacher({{ $teacher->id }})" wire:confirm="¿Eliminar este docente?" class="text-xs text-red-500 hover:text-red-700 font-medium">Eliminar</button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-10 text-center text-slate-400">
                                        No hay docentes.
                                        <button wire:click="openCreateTeacher" class="ml-2 text-brand-600 hover:underline">Crear el primero</button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

        </div>
    </div>

    {{-- Modal institución --}}
    @if($showInstForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" wire:click.self="$set('showInstForm',false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">{{ $instEditingId ? 'Editar institución' : 'Nueva institución' }}</h3>
                    <button wire:click="$set('showInstForm',false)" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input wire:model="instName" type="text" placeholder="Nombre de la institución"
                            class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                        @error('instName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">NIT</label>
                        <input wire:model="instNit" type="text" placeholder="Ej: 800123456-1"
                            class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Ciudad</label>
                        <input wire:model="instCity" type="text" placeholder="Ej: Bogotá"
                            class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                    <button wire:click="$set('showInstForm',false)" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancelar</button>
                    <button wire:click="saveInst" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveInst">Guardar</span>
                        <span wire:loading wire:target="saveInst">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal docente --}}
    @if($showTeacherForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" wire:click.self="$set('showTeacherForm',false)">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="font-semibold text-slate-800">{{ $teacherEditingId ? 'Editar docente' : 'Nuevo docente' }}</h3>
                    <button wire:click="$set('showTeacherForm',false)" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                        <input wire:model="teacherName" type="text" placeholder="Nombre del docente"
                            class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                        @error('teacherName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Correo electrónico <span class="text-red-500">*</span></label>
                        <input wire:model="teacherEmail" type="email" placeholder="correo@institución.edu.co"
                            class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                        @error('teacherEmail') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    @if(!$teacherEditingId)
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Institución <span class="text-red-500">*</span></label>
                            <select wire:model="teacherInstitution"
                                class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500">
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
                            class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                        @error('teacherPassword') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                    <button wire:click="$set('showTeacherForm',false)" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancelar</button>
                    <button wire:click="saveTeacher" wire:loading.attr="disabled"
                        class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition disabled:opacity-50">
                        <span wire:loading.remove wire:target="saveTeacher">Guardar</span>
                        <span wire:loading wire:target="saveTeacher">Guardando…</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
