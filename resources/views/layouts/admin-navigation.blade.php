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
            <p class="text-xs text-forest-400 mt-0.5">Superadministrador</p>
        </div>
    </div>

    {{-- Nav --}}
    @php
        $current = request()->routeIs('admin.*') ? request()->route()->getName() : '';
        $nav = [
            ['route' => 'admin.dashboard',      'label' => 'Dashboard',        'icon' => 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
            ['route' => 'admin.transferencias', 'label' => 'Transferencias',   'icon' => 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5'],
            ['route' => 'admin.seguridad',      'label' => 'Logs de seguridad','icon' => 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z'],
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
                <span class="text-xs font-bold text-gold-400">{{ strtoupper(substr(auth('web')->user()?->name ?? 'A', 0, 1)) }}</span>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-semibold text-white truncate">{{ auth('web')->user()?->name }}</p>
                <p class="text-xs text-forest-500">Superadmin</p>
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
