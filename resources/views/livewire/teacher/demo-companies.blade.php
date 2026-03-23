<div>

    {{-- ── Hero banner ─────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Panel Docente</p>
                <h1 class="font-display text-2xl font-bold text-white">Empresas de demostración</h1>
                <p class="text-forest-300 text-sm mt-1">Crea empresas modelo por sector para enseñar a tus estudiantes</p>
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
                        Crea empresas modelo de diferentes sectores económicos para que tus estudiantes puedan ver ejemplos reales.
                    </p>
                    <button wire:click="openCreate"
                        class="px-5 py-2.5 bg-forest-800 text-white text-sm font-semibold rounded-xl hover:bg-forest-700 transition">
                        Crear primera empresa demo
                    </button>
                </div>
            @else
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                    @foreach($demos as $demo)
                        @php $sectorLabel = $sectors[$demo->sector] ?? ucfirst((string) $demo->sector); @endphp
                        <div class="bg-white rounded-2xl border border-cream-200 shadow-card hover:shadow-card-md transition-all flex flex-col">
                            <div class="p-6 flex-1">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="w-11 h-11 bg-forest-50 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-forest-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                                        </svg>
                                    </div>
                                    <button wire:click="togglePublished('{{ $demo->id }}')"
                                        title="{{ $demo->published ? 'Clic para ocultar a estudiantes' : 'Clic para publicar a estudiantes' }}">
                                        @if($demo->published)
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full hover:bg-green-200 transition">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Publicada
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-slate-100 text-slate-500 text-xs font-medium rounded-full hover:bg-slate-200 transition">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Borrador
                                            </span>
                                        @endif
                                    </button>
                                </div>
                                <h3 class="text-base font-bold text-slate-800 leading-snug mb-1">{{ $demo->company_name }}</h3>
                                <p class="text-xs text-slate-400 mb-3">NIT {{ $demo->nit_empresa }}</p>
                                <span class="inline-block px-2.5 py-1 bg-gold-50 text-gold-700 text-xs font-medium rounded-lg border border-gold-100">
                                    {{ $sectorLabel }}
                                </span>
                                <p class="text-xs text-slate-400 mt-3">Creada {{ $demo->created_at->diffForHumans() }}</p>
                            </div>
                            <div class="border-t border-cream-100 px-4 py-3 flex items-center justify-between">
                                <button
                                    x-on:click="confirmAction('¿Eliminar la empresa «{{ addslashes($demo->company_name) }}»? Se eliminará el schema PostgreSQL y todos sus datos.', () => $wire.delete('{{ $demo->id }}'), { danger: true, confirmText: 'Sí, eliminar' })"
                                    class="text-xs text-red-500 hover:text-red-700 font-medium px-2.5 py-1.5 rounded-lg hover:bg-red-50 transition">
                                    Eliminar
                                </button>
                                <a href="{{ route('teacher.demo.enter', $demo->id) }}"
                                    class="flex items-center gap-1.5 text-xs text-forest-700 hover:text-forest-900 font-semibold px-2.5 py-1.5 rounded-lg hover:bg-forest-50 transition">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                    </svg>
                                    Entrar a la empresa
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
                <p class="text-xs text-slate-400 mt-6 text-center">
                    Las empresas <span class="font-semibold text-green-600">publicadas</span> son visibles para tus estudiantes en "Empresas de referencia".
                    Las en <span class="font-semibold text-slate-500">borrador</span> solo las ves tú.
                </p>
            @endif
        </div>
    </div>

    {{-- ═══════ Modal: Nueva empresa demo ═══════ --}}
    @if($showForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" wire:click.self="$set('showForm', false)">
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
                        Se creará un schema propio en PostgreSQL con el PUC colombiano. La empresa estará en <strong>borrador</strong> hasta que la publiques.
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

</div>
