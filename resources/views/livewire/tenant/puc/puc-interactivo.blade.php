<div>
    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Aprendizaje contable</p>
            <h1 class="font-display text-2xl font-bold text-white">PUC Interactivo</h1>
            <p class="text-forest-300 text-sm mt-1">Plan Único de Cuentas colombiano — navega y consulta cada cuenta</p>
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">

            {{-- Nota pedagógica --}}
            <div class="bg-sky-50 border border-sky-200 rounded-2xl p-4 text-sm text-sky-800 mb-6">
                <p class="font-semibold mb-1">¿Cómo usar el PUC Interactivo?</p>
                <p>Selecciona cualquier cuenta para ver su <strong>descripción técnica</strong>, <strong>dinámica de débito y crédito</strong> (cuándo se aumenta y cuándo se disminuye), y un <strong>ejemplo práctico</strong> de su uso. Entender la dinámica de cada cuenta es fundamental para registrar asientos contables correctamente. Las cuentas de activo y gasto aumentan con débito; las de pasivo, patrimonio e ingreso aumentan con crédito.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-[300px_1fr] gap-6 items-start">

                {{-- ── Panel izquierdo: filtros y lista ───────────────────── --}}
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">

                    {{-- Búsqueda --}}
                    <div class="p-4 border-b border-cream-200">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/>
                            </svg>
                            <input type="text"
                                   wire:model.live.debounce.300ms="search"
                                   placeholder="Buscar código o nombre..."
                                   class="w-full pl-9 pr-3 py-2 rounded-xl border border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500" />
                        </div>
                    </div>

                    {{-- Filtros por clase --}}
                    @if(! $search)
                    <div class="p-3 border-b border-cream-200">
                        <p class="px-1 mb-2 text-[0.65rem] font-semibold tracking-widest uppercase text-slate-400">Filtrar por clase</p>
                        @foreach($clases as $clase)
                        <button wire:click="filtrarClase('{{ $clase->code }}')"
                                class="w-full text-left px-3 py-1.5 mb-0.5 rounded-xl text-sm font-medium transition
                                       {{ $claseActiva === $clase->code
                                           ? 'bg-forest-900 text-white'
                                           : 'text-slate-600 hover:bg-cream-50' }}">
                            <span class="font-mono font-bold text-xs mr-1">{{ $clase->code }}</span>
                            {{ Str::limit($clase->name, 26) }}
                        </button>
                        @endforeach
                    </div>
                    @endif

                    {{-- Lista de cuentas --}}
                    <div class="overflow-y-auto max-h-[520px]">
                        @forelse($accounts as $account)
                        <button wire:click="seleccionar({{ $account->id }})"
                                class="flex items-center gap-2 w-full text-left border-b border-cream-100 transition
                                       {{ $selectedId === $account->id ? 'bg-forest-50 text-forest-900' : 'text-slate-600 hover:bg-cream-50' }}"
                                style="padding: {{ match($account->level) { 1 => '0.55rem 1rem', 2 => '0.45rem 1.25rem', 3 => '0.4rem 1.5rem', default => '0.35rem 1.75rem' } }};">
                            <span class="font-mono text-slate-400 text-[0.72rem] min-w-[48px]">{{ $account->code }}</span>
                            <span class="flex-1 text-{{ $account->level <= 2 ? 'sm font-semibold' : 'xs' }}">{{ $account->name }}</span>
                            @if($account->tieneContenidoAcademico())
                            <svg class="w-1.5 h-1.5 shrink-0 text-forest-500" viewBox="0 0 8 8" fill="currentColor">
                                <circle cx="4" cy="4" r="4"/>
                            </svg>
                            @endif
                        </button>
                        @empty
                        <div class="p-8 text-center text-slate-400 text-sm">No se encontraron cuentas</div>
                        @endforelse
                    </div>
                </div>

                {{-- ── Panel derecho: detalle ──────────────────────────────── --}}
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-6 min-h-[420px]">

                    @if($selectedAccount)

                    {{-- Badges --}}
                    <div class="flex gap-2 flex-wrap mb-4">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-forest-50 text-forest-700 border border-forest-200">
                            {{ ucfirst($selectedAccount->type) }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700 border border-blue-200">
                            Naturaleza: {{ ucfirst($selectedAccount->nature) }}
                        </span>
                        <span class="px-2.5 py-0.5 rounded-full text-xs bg-cream-100 text-slate-500 border border-cream-200">
                            Nivel {{ $selectedAccount->level }}
                        </span>
                    </div>

                    {{-- Código y nombre --}}
                    <div class="font-mono text-2xl font-bold text-forest-900">{{ $selectedAccount->code }}</div>
                    <h2 class="text-lg font-semibold text-slate-800 mt-1 mb-5">{{ $selectedAccount->name }}</h2>

                    @if($selectedAccount->tieneContenidoAcademico())

                    {{-- Descripción --}}
                    <div class="mb-5">
                        <p class="text-[0.65rem] font-semibold uppercase tracking-widest text-slate-400 mb-1.5">Descripción</p>
                        <p class="text-sm text-slate-600 leading-relaxed">{{ $selectedAccount->descripcion }}</p>
                    </div>

                    {{-- Dinámica débito / crédito --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
                            <p class="text-[0.65rem] font-bold uppercase tracking-widest text-amber-700 mb-2">Débito — se debita cuando...</p>
                            <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">{{ $selectedAccount->dinamica_debe }}</p>
                        </div>
                        <div class="rounded-xl border border-green-200 bg-green-50 p-4">
                            <p class="text-[0.65rem] font-bold uppercase tracking-widest text-green-700 mb-2">Crédito — se acredita cuando...</p>
                            <p class="text-xs text-slate-700 leading-relaxed whitespace-pre-line">{{ $selectedAccount->dinamica_haber }}</p>
                        </div>
                    </div>

                    {{-- Ejemplo práctico --}}
                    @if($selectedAccount->ejemplo)
                    <div class="rounded-xl border-l-4 border-forest-700 bg-forest-50 p-4">
                        <div class="flex items-center gap-2 mb-1.5">
                            <svg class="w-3.5 h-3.5 text-forest-700 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18"/>
                            </svg>
                            <p class="text-[0.65rem] font-bold uppercase tracking-widest text-forest-700">Ejemplo práctico</p>
                        </div>
                        <p class="text-sm text-slate-700 leading-relaxed">{{ $selectedAccount->ejemplo }}</p>
                    </div>
                    @endif

                    @else
                    {{-- Sin contenido académico --}}
                    <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <svg class="w-10 h-10 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>
                        </svg>
                        <p class="text-sm">Esta cuenta no tiene contenido académico detallado.</p>
                        @if($selectedAccount->level >= 4)
                        <p class="text-xs mt-1">Consulta la cuenta padre <strong class="text-slate-600">{{ substr($selectedAccount->code, 0, 4) }}</strong> para ver su dinámica.</p>
                        @endif
                    </div>
                    @endif

                    @else
                    {{-- Estado vacío --}}
                    <div class="flex flex-col items-center justify-center min-h-[320px] text-slate-400">
                        <svg class="w-12 h-12 mb-4" fill="none" viewBox="0 0 24 24" stroke-width="1.25" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>
                        </svg>
                        <h3 class="text-base font-semibold text-slate-600 mb-1">Selecciona una cuenta</h3>
                        <p class="text-sm text-center max-w-xs leading-relaxed">
                            Haz clic en cualquier cuenta del PUC para ver su descripción, dinámica contable y ejemplos prácticos.
                        </p>
                        <div class="mt-4 flex items-center gap-1.5 text-xs bg-cream-50 border border-cream-200 px-3 py-2 rounded-xl">
                            Las cuentas con
                            <svg class="w-1.5 h-1.5 text-forest-500" viewBox="0 0 8 8" fill="currentColor"><circle cx="4" cy="4" r="4"/></svg>
                            tienen contenido académico completo
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>
