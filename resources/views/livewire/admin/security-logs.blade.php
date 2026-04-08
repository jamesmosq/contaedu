<div>
    {{-- ── Hero ────────────────────────────────────────────────────────────── --}}
    <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto">
            <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Administración</p>
            <h1 class="font-display text-2xl font-bold text-white">Logs de seguridad</h1>
            <p class="text-forest-300 text-sm mt-1">Registro de eventos de autenticación y actividad</p>

            {{-- Estadísticas rápidas --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-3 mt-6">
                <div class="bg-forest-800/60 rounded-xl px-4 py-3">
                    <p class="text-forest-400 text-xs">Total</p>
                    <p class="text-white text-xl font-bold">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="bg-forest-800/60 rounded-xl px-4 py-3">
                    <p class="text-green-400 text-xs">Exitosos</p>
                    <p class="text-white text-xl font-bold">{{ number_format($stats['login_success']) }}</p>
                </div>
                <div class="bg-forest-800/60 rounded-xl px-4 py-3">
                    <p class="text-red-400 text-xs">Fallidos</p>
                    <p class="text-white text-xl font-bold">{{ number_format($stats['login_failed']) }}</p>
                </div>
                <div class="bg-forest-800/60 rounded-xl px-4 py-3">
                    <p class="text-orange-400 text-xs">Bloqueos</p>
                    <p class="text-white text-xl font-bold">{{ number_format($stats['bloqueo']) }}</p>
                </div>
                <div class="bg-forest-800/60 rounded-xl px-4 py-3">
                    <p class="text-yellow-400 text-xs">Sospechoso</p>
                    <p class="text-white text-xl font-bold">{{ number_format($stats['actividad_sospechosa']) }}</p>
                </div>
                <div class="bg-forest-800/60 rounded-xl px-4 py-3">
                    <p class="text-blue-400 text-xs">Resets</p>
                    <p class="text-white text-xl font-bold">{{ number_format($stats['password_reset']) }}</p>
                </div>
                <div class="bg-forest-800/60 rounded-xl px-4 py-3">
                    <p class="text-forest-400 text-xs">Logouts</p>
                    <p class="text-white text-xl font-bold">{{ number_format($stats['logout']) }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="px-6 py-8 lg:px-10">
        <div class="max-w-7xl mx-auto space-y-5">

            {{-- Filtros --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-5">
                <div class="flex flex-wrap gap-3 items-end">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Evento</label>
                        <select wire:model.live="filterEvent" class="rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                            <option value="">Todos los eventos</option>
                            <option value="login_success">Login exitoso</option>
                            <option value="login_failed">Login fallido</option>
                            <option value="bloqueo">Bloqueo</option>
                            <option value="logout">Logout</option>
                            <option value="actividad_sospechosa">Actividad sospechosa</option>
                            <option value="password_reset">Restablecimiento de contraseña</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Tipo usuario</label>
                        <select wire:model.live="filterUserType" class="rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                            <option value="">Todos</option>
                            <option value="Superadmin">Superadmin</option>
                            <option value="Docente">Docente</option>
                            <option value="Coordinador">Coordinador</option>
                            <option value="Estudiante">Estudiante</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Identificador</label>
                        <input wire:model.live.debounce.300ms="filterSearch" type="text" placeholder="Email o cédula..."
                               class="rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500 w-48">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Fecha</label>
                        <input wire:model.live="filterDate" type="date"
                               class="rounded-xl border-cream-200 text-sm focus:ring-forest-500 focus:border-forest-500">
                    </div>
                    @if($filterEvent || $filterUserType || $filterSearch || $filterDate)
                        <button wire:click="clearFilters"
                                class="px-4 py-2 text-sm text-slate-500 hover:text-slate-700 border border-cream-200 rounded-xl hover:bg-cream-50 transition">
                            Limpiar filtros
                        </button>
                    @endif
                </div>
            </div>

            {{-- Tabla --}}
            <div class="bg-white rounded-2xl border border-cream-200 shadow-card overflow-hidden">
                @if($logs->isEmpty())
                    <div class="text-center py-16 text-slate-400">
                        <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                        </svg>
                        <p class="text-sm font-medium">Sin registros</p>
                        <p class="text-xs mt-1">Los eventos de autenticación aparecerán aquí</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-cream-200 bg-cream-50">
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Evento</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Identificador</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">IP</th>
                                    <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-cream-100">
                                @foreach($logs as $log)
                                    <tr class="hover:bg-cream-50/50 transition-colors">
                                        <td class="px-5 py-3">
                                            @php
                                                $badge = match($log->event) {
                                                    'login_success'        => ['bg-green-50 text-green-700',   'bg-green-500',  'Login exitoso'],
                                                    'login_failed'         => ['bg-red-50 text-red-700',       'bg-red-500',    'Login fallido'],
                                                    'bloqueo'              => ['bg-orange-50 text-orange-700', 'bg-orange-500', 'Bloqueo'],
                                                    'actividad_sospechosa' => ['bg-yellow-50 text-yellow-700', 'bg-yellow-500', 'Actividad sospechosa'],
                                                    'password_reset'       => ['bg-blue-50 text-blue-700',     'bg-blue-500',   'Reset contraseña'],
                                                    default                => ['bg-slate-100 text-slate-600',  'bg-slate-400',  'Logout'],
                                                };
                                            @endphp
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge[0] }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $badge[1] }}"></span>
                                                {{ $badge[2] }}
                                            </span>
                                        </td>
                                        <td class="px-5 py-3">
                                            <span class="text-xs font-medium text-slate-700">{{ $log->user_type }}</span>
                                        </td>
                                        <td class="px-5 py-3 font-mono text-xs text-slate-700">{{ $log->identifier }}</td>
                                        <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $log->ip_address ?? '—' }}</td>
                                        <td class="px-5 py-3 text-xs text-slate-500 whitespace-nowrap">
                                            {{ $log->created_at->format('d/m/Y H:i:s') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($logs->hasPages())
                        <div class="px-5 py-4 border-t border-cream-200">
                            {{ $logs->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</div>
