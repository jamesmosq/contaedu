<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-30 w-64 shrink-0 bg-forest-950 flex flex-col
           transition-transform duration-200 ease-in-out
           lg:sticky lg:top-0 lg:h-screen lg:z-auto">

    {{-- Logo --}}
    <div class="flex items-center gap-3 px-5 py-5 border-b border-forest-800">
        <div class="w-8 h-8 bg-gold-500 rounded-lg flex items-center justify-center shadow-gold shrink-0">
            <svg class="w-4 h-4 text-forest-950" fill="currentColor" viewBox="0 0 20 20">
                <path d="M10.394 2.08a1 1 0 0 0-.788 0l-7 3a1 1 0 0 0 0 1.84L5.25 8.051a.999.999 0 0 1 .356-.257l4-1.714a1 1 0 1 1 .788 1.838L7.667 9.088l1.94.831a1 1 0 0 0 .787 0l7-3a1 1 0 0 0 0-1.838l-7-3ZM3.31 9.397L5 10.12v4.102a8.969 8.969 0 0 0-1.05-.174 1 1 0 0 1-.89-.89 11.115 11.115 0 0 1 .25-3.762ZM9.3 16.573A9.026 9.026 0 0 0 7 14.935v-3.957l1.818.78a3 3 0 0 0 2.364 0l5.508-2.361a11.026 11.026 0 0 1 .25 3.762 1 1 0 0 1-.89.89 8.968 8.968 0 0 0-5.35 2.524 1 1 0 0 1-1.4 0ZM6 18a1 1 0 0 0 1-1v-2.065a8.935 8.935 0 0 0-2-.712V17a1 1 0 0 0 1 1Z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <span class="font-display text-lg font-bold text-white leading-none">Conta<span class="text-gold-400">Edu</span></span>
            <p class="text-xs text-forest-400 mt-0.5">Panel Docente</p>
        </div>
    </div>

    {{-- Nav --}}
    @php
        $currentRoute = request()->route()?->getName() ?? '';
        // Normalize demo sub-routes to their parent nav item
        $current = str_starts_with($currentRoute, 'teacher.demo.') && $currentRoute !== 'teacher.demos'
            ? 'teacher.demos'
            : $currentRoute;
        $nav = [
            [
                'route'  => 'teacher.dashboard',
                'label'  => 'Mis grupos',
                'icon'   => 'M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5',
            ],
            [
                'route'  => 'teacher.comparativo',
                'label'  => 'Panel comparativo',
                'icon'   => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
            ],
            [
                'route'  => 'teacher.announcements',
                'label'  => 'Anuncios',
                'icon'   => 'M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46',
            ],
            [
                'route'  => 'teacher.demos',
                'label'  => 'Mis demos',
                'icon'   => 'M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z',
            ],
            [
                'route'  => 'teacher.buscar-estudiante',
                'label'  => 'Buscar estudiante',
                'icon'   => 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5',
            ],
        ];
    @endphp

    <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
        @foreach($nav as $item)
            @php $active = $current === $item['route']; @endphp
            <a href="{{ route($item['route']) }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150
                      {{ $active
                           ? 'bg-forest-800 text-white'
                           : 'text-forest-300 hover:text-white hover:bg-forest-800/60' }}">
                @if($active)
                    <span class="w-1.5 h-1.5 rounded-full bg-gold-400 shrink-0"></span>
                @else
                    <span class="w-1.5 h-1.5 shrink-0"></span>
                @endif
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                </svg>
                {{ $item['label'] }}
            </a>
        @endforeach
    </nav>

    {{-- Footer --}}
    <div class="px-3 py-4 border-t border-forest-800 space-y-1">
        {{-- Campana de notificaciones --}}
        <div class="flex items-center justify-between px-3 py-1">
            <span class="text-xs text-forest-500">Notificaciones</span>
            @livewire('shared.notification-bell')
        </div>

        <div class="flex items-center gap-3 px-3 py-2">
            <div class="w-7 h-7 rounded-full bg-forest-700 flex items-center justify-center shrink-0">
                <span class="text-xs font-bold text-gold-400">{{ strtoupper(substr(auth('web')->user()?->name ?? 'D', 0, 1)) }}</span>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-white truncate">{{ auth('web')->user()?->name }}</p>
                <p class="text-xs text-forest-500">Docente</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm text-forest-400 hover:text-red-400 hover:bg-red-400/10 transition">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0 1 10.5 3h6a2.25 2.25 0 0 1 2.25 2.25v13.5A2.25 2.25 0 0 1 16.5 21h-6a2.25 2.25 0 0 1-2.25-2.25V15m-3 0-3-3m0 0 3-3m-3 3H15"/>
                </svg>
                Cerrar sesión
            </button>
        </form>
    </div>
</aside>
