<div>
    {{-- ── Hero ───────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-5xl mx-auto">
            <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Mi empresa</p>
            <h1 class="font-display text-2xl font-bold text-white">Empresas de referencia</h1>
            <p class="text-forest-300 text-sm mt-1">Empresas de demostración compartidas por tu docente. Puedes explorarlas en modo solo lectura.</p>
        </div>
    </div>

    <div class="py-8 px-6 lg:px-10">
    <div class="max-w-5xl mx-auto">

        @if($demos->isEmpty())
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-14 text-center">
                <div class="w-14 h-14 bg-forest-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-forest-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-slate-700 mb-1">Sin empresas de referencia disponibles</h3>
                <p class="text-xs text-slate-400 max-w-sm mx-auto">
                    Tu docente aún no ha publicado ninguna empresa de demostración. Vuelve más tarde.
                </p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($demos as $demo)
                    @php
                        $sectorLabels = [
                            'comercio'     => 'Comercio',
                            'servicios'    => 'Servicios',
                            'manufactura'  => 'Manufactura',
                            'construccion' => 'Construcción',
                            'agropecuario' => 'Agropecuario',
                            'otro'         => 'Otro',
                        ];
                        $sectorLabel = $sectorLabels[$demo->sector] ?? ucfirst((string) $demo->sector);
                    @endphp
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card hover:border-forest-300 hover:shadow-card-md transition-all flex flex-col">
                        <div class="p-6 flex-1">
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-11 h-11 bg-forest-50 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-forest-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25" />
                                    </svg>
                                </div>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-violet-100 text-violet-700 text-xs font-semibold rounded-full">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.574-3.007-9.964-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    Solo lectura
                                </span>
                            </div>
                            <h3 class="text-base font-bold text-slate-800 leading-snug mb-1">{{ $demo->company_name }}</h3>
                            <p class="text-xs text-slate-400 mb-3">NIT {{ $demo->nit_empresa }}</p>
                            <span class="inline-block px-2.5 py-1 bg-gold-50 text-gold-700 text-xs font-medium rounded-lg border border-gold-100">
                                {{ $sectorLabel }}
                            </span>
                        </div>
                        <div class="border-t border-cream-100 px-4 py-3">
                            <a href="{{ route('student.referencias.enter', $demo->id) }}"
                                class="flex items-center justify-center gap-2 w-full py-2 bg-forest-800 text-white text-xs font-semibold rounded-xl hover:bg-forest-700 transition">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.574-3.007-9.964-7.178Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                Explorar empresa
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

    </div>
    </div>
</div>
