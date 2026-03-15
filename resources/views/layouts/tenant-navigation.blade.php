@php
    $auditMode  = session('audit_mode');
    $auditId    = session('audit_tenant_id');
    $navRoutes  = $auditMode ? [
        'dashboard' => route('teacher.auditoria.dashboard',  $auditId),
        'cuentas'   => route('teacher.auditoria.cuentas',    $auditId),
        'terceros'  => route('teacher.auditoria.terceros',   $auditId),
        'productos' => route('teacher.auditoria.productos',  $auditId),
        'config'    => route('teacher.auditoria.config',     $auditId),
        'facturas'  => route('teacher.auditoria.facturas',   $auditId),
        'compras'   => route('teacher.auditoria.compras',    $auditId),
        'reportes'  => route('teacher.auditoria.reportes',   $auditId),
    ] : [
        'dashboard' => route('student.dashboard'),
        'cuentas'   => route('student.cuentas'),
        'terceros'  => route('student.terceros'),
        'productos' => route('student.productos'),
        'config'    => route('student.config'),
        'facturas'  => route('student.facturas'),
        'compras'   => route('student.compras'),
        'reportes'  => route('student.reportes'),
    ];

    // En modo auditoría tenancy ya está inicializada; usamos tenancy()->tenant
    // para no consultar la tabla tenants dentro del schema del estudiante.
    $currentTenant = $auditMode
        ? tenancy()->tenant
        : auth('student')->user();
@endphp

<nav x-data="{ open: false }" class="bg-white border-b border-slate-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-8">
                {{-- Logo --}}
                <a href="{{ $navRoutes['dashboard'] }}" class="flex items-center">
                    <span class="text-brand-900 font-bold text-xl tracking-tight">Conta</span><span class="text-accent-600 font-bold text-xl tracking-tight">Edu</span>
                </a>

                {{-- Nav Links --}}
                <div class="hidden sm:flex gap-1">
                    <x-nav-link :href="$navRoutes['dashboard']" :active="request()->routeIs('student.dashboard') || request()->routeIs('teacher.auditoria.dashboard')">
                        Inicio
                    </x-nav-link>
                    <x-nav-link :href="$navRoutes['facturas']" :active="request()->routeIs('student.facturas') || request()->routeIs('teacher.auditoria.facturas')">
                        Facturas
                    </x-nav-link>
                    <x-nav-link :href="$navRoutes['compras']" :active="request()->routeIs('student.compras') || request()->routeIs('teacher.auditoria.compras')">
                        Compras
                    </x-nav-link>
                    <x-nav-link :href="$navRoutes['terceros']" :active="request()->routeIs('student.terceros') || request()->routeIs('teacher.auditoria.terceros')">
                        Terceros
                    </x-nav-link>
                    <x-nav-link :href="$navRoutes['productos']" :active="request()->routeIs('student.productos') || request()->routeIs('teacher.auditoria.productos')">
                        Productos
                    </x-nav-link>
                    <x-nav-link :href="$navRoutes['reportes']" :active="request()->routeIs('student.reportes') || request()->routeIs('teacher.auditoria.reportes')">
                        Reportes
                    </x-nav-link>
                </div>
            </div>

            {{-- Info de empresa --}}
            @if($currentTenant)
            <div class="hidden sm:flex sm:items-center gap-4">
                <span class="text-sm text-slate-500">{{ $currentTenant->company_name }}</span>

                @if(!$auditMode)
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-sm font-medium text-slate-700 bg-slate-100 hover:bg-slate-200 transition">
                            <svg class="w-4 h-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <span>{{ $currentTenant->student_name }}</span>
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="px-4 py-2 border-b border-slate-100">
                            <p class="text-xs text-slate-400">Empresa activa</p>
                            <p class="text-sm font-medium text-slate-700">{{ $currentTenant->company_name }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">NIT {{ $currentTenant->nit_empresa }}</p>
                        </div>
                        <form method="POST" action="{{ route('student.logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('student.logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                Cerrar sesión
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
                @endif
            </div>
            @endif

            {{-- Hamburger --}}
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="p-2 rounded-md text-slate-400 hover:text-slate-500 hover:bg-slate-100 transition">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- Responsive Menu --}}
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t border-slate-200">
        <div class="pt-2 pb-3 px-4 space-y-1">
            <x-responsive-nav-link :href="$navRoutes['dashboard']">Inicio</x-responsive-nav-link>
            <x-responsive-nav-link :href="$navRoutes['facturas']">Facturas</x-responsive-nav-link>
            <x-responsive-nav-link :href="$navRoutes['compras']">Compras</x-responsive-nav-link>
            <x-responsive-nav-link :href="$navRoutes['cuentas']">Plan de cuentas</x-responsive-nav-link>
            <x-responsive-nav-link :href="$navRoutes['terceros']">Terceros</x-responsive-nav-link>
            <x-responsive-nav-link :href="$navRoutes['productos']">Productos</x-responsive-nav-link>
            <x-responsive-nav-link :href="$navRoutes['reportes']">Reportes</x-responsive-nav-link>
        </div>
        @if($currentTenant && !$auditMode)
        <div class="pt-4 pb-4 border-t border-slate-200 px-4">
            <p class="text-sm font-medium text-slate-700">{{ $currentTenant->student_name }}</p>
            <p class="text-xs text-slate-500 mb-3">{{ $currentTenant->company_name }}</p>
            <form method="POST" action="{{ route('student.logout') }}">
                @csrf
                <x-responsive-nav-link :href="route('student.logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                    Cerrar sesión
                </x-responsive-nav-link>
            </form>
        </div>
        @endif
    </div>
</nav>
