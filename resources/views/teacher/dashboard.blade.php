<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $teacher->name }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">Panel del docente</p>
            </div>
            <span class="px-3 py-1 bg-accent-100 text-accent-800 text-xs font-semibold rounded-full uppercase tracking-wide">Docente</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($group)
                {{-- Header del grupo --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">{{ $group->name }}</h3>
                            <p class="text-sm text-slate-500">{{ $group->institution->name }} · Período {{ $group->period }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-brand-800">{{ $group->tenants->count() }}</p>
                            <p class="text-xs text-slate-500">estudiantes</p>
                        </div>
                    </div>
                </div>

                {{-- Tabla de estudiantes --}}
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-slate-700">Empresas del grupo</h4>
                        <button class="px-3 py-1.5 bg-brand-800 text-white text-xs font-medium rounded-lg hover:bg-brand-700 transition">
                            + Crear empresa
                        </button>
                    </div>

                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estudiante</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">NIT</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($group->tenants as $tenant)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-8 h-8 bg-brand-100 rounded-full flex items-center justify-center text-brand-700 font-semibold text-xs">
                                                {{ strtoupper(substr($tenant->student_name, 0, 2)) }}
                                            </div>
                                            <span class="font-medium text-slate-700">{{ $tenant->student_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-slate-600">{{ $tenant->company_name }}</td>
                                    <td class="px-6 py-4 text-slate-500 font-mono text-xs">{{ $tenant->nit_empresa }}</td>
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ $tenant->active ? 'bg-accent-100 text-accent-700' : 'bg-red-100 text-red-700' }}">
                                            {{ $tenant->active ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <button class="text-xs text-brand-700 hover:text-brand-900 font-medium transition">Auditar →</button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                    <p class="text-slate-500">Aún no tienes un grupo asignado.</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
