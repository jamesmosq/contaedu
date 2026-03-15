<x-app-layout>
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

            {{-- Métricas --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 bg-brand-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-brand-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                            </svg>
                        </div>
                        <p class="text-sm text-slate-500 font-medium">Instituciones</p>
                    </div>
                    <p class="text-3xl font-bold text-slate-800">{{ $institutionsCount }}</p>
                </div>

                <div class="bg-white rounded-xl border border-slate-200 p-5">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-9 h-9 bg-accent-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-accent-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-1.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                            </svg>
                        </div>
                        <p class="text-sm text-slate-500 font-medium">Docentes</p>
                    </div>
                    <p class="text-3xl font-bold text-slate-800">{{ $teachersCount }}</p>
                </div>
            </div>

            {{-- Acciones rápidas --}}
            <div class="bg-white rounded-xl border border-slate-200 p-6">
                <h3 class="text-sm font-semibold text-slate-700 mb-4 uppercase tracking-wide">Acciones rápidas</h3>
                <div class="flex gap-3">
                    <button class="px-4 py-2 bg-brand-800 text-white text-sm font-medium rounded-lg hover:bg-brand-700 transition">
                        + Nueva institución
                    </button>
                    <button class="px-4 py-2 bg-white border border-slate-200 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-50 transition">
                        + Nuevo docente
                    </button>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
