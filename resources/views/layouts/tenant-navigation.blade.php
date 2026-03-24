@php
    $demoMode      = session('demo_mode');
    $referenceMode = session('reference_mode');
    $auditMode     = session('audit_mode') && ! $referenceMode;

    $demoId    = session('demo_tenant_id');
    $refId     = session('reference_tenant_id');
    $auditId   = session('audit_tenant_id');

    $currentTenant = ($demoMode || $referenceMode || $auditMode)
        ? tenancy()->tenant
        : auth('student')->user();

    if ($demoMode) {
        // Docente dentro de su empresa demo (acceso completo)
        $nav = [
            ['key' => 'dashboard',    'label' => 'Inicio',          'route' => route('teacher.demo.dashboard',    $demoId), 'icon' => 'home'],
            ['key' => 'facturas',     'label' => 'Facturas',         'route' => route('teacher.demo.facturas',     $demoId), 'icon' => 'document-text'],
            ['key' => 'compras',      'label' => 'Compras',          'route' => route('teacher.demo.compras',      $demoId), 'icon' => 'shopping-cart'],
            ['key' => 'terceros',     'label' => 'Terceros',         'route' => route('teacher.demo.terceros',     $demoId), 'icon' => 'users'],
            ['key' => 'productos',    'label' => 'Productos',        'route' => route('teacher.demo.productos',    $demoId), 'icon' => 'cube'],
            ['key' => 'cuentas',      'label' => 'Plan de cuentas',  'route' => route('teacher.demo.cuentas',      $demoId), 'icon' => 'book-open'],
            ['key' => 'reportes',     'label' => 'Reportes',         'route' => route('teacher.demo.reportes',     $demoId), 'icon' => 'chart-bar'],
            ['key' => 'calendario',   'label' => 'Calendario',       'route' => route('teacher.demo.calendario',   $demoId), 'icon' => 'calendar'],
            ['key' => 'activos',      'label' => 'Activos fijos',    'route' => route('teacher.demo.activos-fijos',$demoId), 'icon' => 'building-office'],
            ['key' => 'conciliacion', 'label' => 'Conciliación',     'route' => route('teacher.demo.conciliacion', $demoId), 'icon' => 'banknotes'],
            ['key' => 'config',       'label' => 'Configuración',    'route' => route('teacher.demo.config',       $demoId), 'icon' => 'cog'],
        ];
    } elseif ($referenceMode) {
        // Estudiante viendo empresa de referencia del docente (solo lectura)
        $nav = [
            ['key' => 'dashboard',    'label' => 'Inicio',          'route' => route('student.referencia.dashboard',    $refId), 'icon' => 'home'],
            ['key' => 'facturas',     'label' => 'Facturas',         'route' => route('student.referencia.facturas',     $refId), 'icon' => 'document-text'],
            ['key' => 'compras',      'label' => 'Compras',          'route' => route('student.referencia.compras',      $refId), 'icon' => 'shopping-cart'],
            ['key' => 'terceros',     'label' => 'Terceros',         'route' => route('student.referencia.terceros',     $refId), 'icon' => 'users'],
            ['key' => 'productos',    'label' => 'Productos',        'route' => route('student.referencia.productos',    $refId), 'icon' => 'cube'],
            ['key' => 'cuentas',      'label' => 'Plan de cuentas',  'route' => route('student.referencia.cuentas',      $refId), 'icon' => 'book-open'],
            ['key' => 'reportes',     'label' => 'Reportes',         'route' => route('student.referencia.reportes',     $refId), 'icon' => 'chart-bar'],
            ['key' => 'calendario',   'label' => 'Calendario',       'route' => route('student.referencia.calendario',   $refId), 'icon' => 'calendar'],
            ['key' => 'activos',      'label' => 'Activos fijos',    'route' => route('student.referencia.activos-fijos',$refId), 'icon' => 'building-office'],
            ['key' => 'conciliacion', 'label' => 'Conciliación',     'route' => route('student.referencia.conciliacion', $refId), 'icon' => 'banknotes'],
        ];
    } elseif ($auditMode) {
        // Docente auditando empresa de estudiante (solo lectura)
        $nav = [
            ['key' => 'dashboard',    'label' => 'Inicio',          'route' => route('teacher.auditoria.dashboard',     $auditId), 'icon' => 'home'],
            ['key' => 'facturas',     'label' => 'Facturas',         'route' => route('teacher.auditoria.facturas',      $auditId), 'icon' => 'document-text'],
            ['key' => 'compras',      'label' => 'Compras',          'route' => route('teacher.auditoria.compras',       $auditId), 'icon' => 'shopping-cart'],
            ['key' => 'terceros',     'label' => 'Terceros',         'route' => route('teacher.auditoria.terceros',      $auditId), 'icon' => 'users'],
            ['key' => 'productos',    'label' => 'Productos',        'route' => route('teacher.auditoria.productos',     $auditId), 'icon' => 'cube'],
            ['key' => 'cuentas',      'label' => 'Plan de cuentas',  'route' => route('teacher.auditoria.cuentas',       $auditId), 'icon' => 'book-open'],
            ['key' => 'reportes',     'label' => 'Reportes',         'route' => route('teacher.auditoria.reportes',      $auditId), 'icon' => 'chart-bar'],
            ['key' => 'calendario',   'label' => 'Calendario',       'route' => route('teacher.auditoria.calendario',    $auditId), 'icon' => 'calendar'],
            ['key' => 'activos',      'label' => 'Activos fijos',    'route' => route('teacher.auditoria.activos-fijos', $auditId), 'icon' => 'building-office'],
            ['key' => 'conciliacion', 'label' => 'Conciliación',     'route' => route('teacher.auditoria.conciliacion',  $auditId), 'icon' => 'banknotes'],
            ['key' => 'config',       'label' => 'Configuración',    'route' => route('teacher.auditoria.config',        $auditId), 'icon' => 'cog'],
        ];
    } else {
        // Modo normal: estudiante en su propia empresa
        $nav = [
            ['key' => 'dashboard',    'label' => 'Inicio',             'route' => route('student.dashboard'),        'icon' => 'home'],
            ['key' => 'facturas',     'label' => 'Facturas',            'route' => route('student.facturas'),         'icon' => 'document-text'],
            ['key' => 'compras',      'label' => 'Compras',             'route' => route('student.compras'),          'icon' => 'shopping-cart'],
            ['key' => 'terceros',     'label' => 'Terceros',            'route' => route('student.terceros'),         'icon' => 'users'],
            ['key' => 'productos',    'label' => 'Productos',           'route' => route('student.productos'),        'icon' => 'cube'],
            ['key' => 'cuentas',      'label' => 'Plan de cuentas',     'route' => route('student.cuentas'),          'icon' => 'book-open'],
            ['key' => 'reportes',     'label' => 'Reportes',            'route' => route('student.reportes'),         'icon' => 'chart-bar'],
            ['key' => 'calendario',   'label' => 'Calendario',          'route' => route('student.calendario'),       'icon' => 'calendar'],
            ['key' => 'activos',      'label' => 'Activos fijos',       'route' => route('student.activos-fijos'),    'icon' => 'building-office'],
            ['key' => 'conciliacion', 'label' => 'Conciliación',        'route' => route('student.conciliacion'),     'icon' => 'banknotes'],
            ['key' => 'fe',           'label' => 'F. Electrónica',      'route' => route('student.fe.index'),         'icon' => 'bolt'],
            ['key' => 'config',       'label' => 'Configuración',       'route' => route('student.config'),           'icon' => 'cog'],
            ['key' => 'referencias',  'label' => 'Empresas Maestras',   'route' => route('student.referencias'),      'icon' => 'academic-cap', 'divider' => true],
        ];
    }

    $icons = [
        'home'            => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
        'document-text'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>',
        'shopping-cart'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>',
        'users'           => '<path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>',
        'cube'            => '<path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>',
        'book-open'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 0 0 6 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 0 1 6 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 0 1 6-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0 0 18 18a8.967 8.967 0 0 0-6 2.292m0-14.25v14.25"/>',
        'chart-bar'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z"/>',
        'calendar'        => '<path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>',
        'building-office' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>',
        'banknotes'       => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>',
        'bolt'            => '<path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z"/>',
        'academic-cap'    => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>',
        'cog'             => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>',
    ];
@endphp

{{-- ── Sidebar ──────────────────────────────────────────────────────────── --}}
<aside
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
    class="fixed inset-y-0 left-0 z-30 w-64 shrink-0 bg-forest-950 flex flex-col
           transition-transform duration-200 ease-in-out
           lg:sticky lg:top-0 lg:h-screen lg:z-auto">

    {{-- Logo --}}
    <div class="flex items-center justify-between h-16 px-5 border-b border-forest-800 shrink-0">
        <a href="{{ $nav[0]['route'] }}" class="flex items-center gap-2">
            <div class="w-7 h-7 bg-gold-500 rounded-lg flex items-center justify-center">
                <svg class="w-4 h-4 text-forest-950" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.394 2.08a1 1 0 0 0-.788 0l-7 3a1 1 0 0 0 0 1.84L5.25 8.051a.999.999 0 0 1 .356-.257l4-1.714a1 1 0 1 1 .788 1.838L7.667 9.088l1.94.831a1 1 0 0 0 .787 0l7-3a1 1 0 0 0 0-1.838l-7-3ZM3.31 9.397L5 10.12v4.102a8.969 8.969 0 0 0-1.05-.174 1 1 0 0 1-.89-.89 11.115 11.115 0 0 1 .25-3.762ZM9.3 16.573A9.026 9.026 0 0 0 7 14.935v-3.957l1.818.78a3 3 0 0 0 2.364 0l5.508-2.361a11.026 11.026 0 0 1 .25 3.762 1 1 0 0 1-.89.89 8.968 8.968 0 0 0-5.35 2.524 1 1 0 0 1-1.4 0ZM6 18a1 1 0 0 0 1-1v-2.065a8.935 8.935 0 0 0-2-.712V17a1 1 0 0 0 1 1Z"/>
                </svg>
            </div>
            <span class="font-display text-lg font-bold text-white">Conta<span class="text-gold-400">Edu</span></span>
        </a>
        <button @click="sidebarOpen = false" class="lg:hidden p-1 rounded text-forest-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Info empresa --}}
    @if($currentTenant)
        <div class="px-4 py-3 border-b border-forest-900 shrink-0">
            <p class="text-xs font-semibold text-gold-400 uppercase tracking-wider truncate">{{ $currentTenant->company_name }}</p>
            <p class="text-xs text-forest-400 mt-0.5 truncate">{{ $currentTenant->student_name ?? '' }}</p>
        </div>
    @endif

    {{-- Navegación --}}
    <nav class="flex-1 overflow-y-auto px-3 py-3 space-y-0.5">
        @foreach($nav as $item)
            @if(!empty($item['divider']))
                <div class="border-t border-forest-900 my-2 mx-1"></div>
            @endif
            @php
                $isActive = match($item['key']) {
                    'dashboard'    => request()->routeIs('student.dashboard') || request()->routeIs('teacher.auditoria.dashboard'),
                    'facturas'     => request()->routeIs('student.facturas') || request()->routeIs('teacher.auditoria.facturas'),
                    'compras'      => request()->routeIs('student.compras') || request()->routeIs('teacher.auditoria.compras'),
                    'terceros'     => request()->routeIs('student.terceros') || request()->routeIs('teacher.auditoria.terceros'),
                    'productos'    => request()->routeIs('student.productos') || request()->routeIs('teacher.auditoria.productos'),
                    'cuentas'      => request()->routeIs('student.cuentas') || request()->routeIs('teacher.auditoria.cuentas'),
                    'reportes'     => request()->routeIs('student.reportes') || request()->routeIs('teacher.auditoria.reportes'),
                    'calendario'   => request()->routeIs('student.calendario') || request()->routeIs('teacher.auditoria.calendario'),
                    'activos'      => request()->routeIs('student.activos-fijos') || request()->routeIs('teacher.auditoria.activos-fijos'),
                    'conciliacion' => request()->routeIs('student.conciliacion') || request()->routeIs('teacher.auditoria.conciliacion'),
                    'fe'           => request()->routeIs('student.fe.*'),
                    'config'       => request()->routeIs('student.config') || request()->routeIs('teacher.auditoria.config'),
                    'referencias'  => request()->routeIs('student.referencias') || request()->routeIs('student.referencia.*'),
                    default        => false,
                };
            @endphp
            <a href="{{ $item['route'] }}"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium transition-all duration-150
                   {{ $isActive
                       ? 'bg-forest-800 text-white shadow-inner'
                       : 'text-forest-300 hover:bg-forest-900 hover:text-white' }}">
                <svg class="w-4 h-4 shrink-0 {{ $isActive ? 'text-gold-400' : '' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    {!! $icons[$item['icon']] ?? '' !!}
                </svg>
                <span>{{ $item['label'] }}</span>
                @if($isActive)
                    <span class="ml-auto w-1.5 h-1.5 rounded-full bg-gold-400"></span>
                @endif
            </a>
        @endforeach
    </nav>

    {{-- Pie del sidebar --}}
    <div class="px-4 py-4 border-t border-forest-900 shrink-0">
        @if(!$auditMode)
            <form method="POST" action="{{ route('student.logout') }}">
                @csrf
                <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-forest-400 hover:bg-forest-900 hover:text-white transition">
                    <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
                    </svg>
                    Cerrar sesión
                </button>
            </form>
        @else
            <a href="{{ route('teacher.auditar.stop') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-xl text-sm font-medium text-amber-400 hover:bg-forest-900 hover:text-amber-300 transition">
                <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>
                </svg>
                Salir de auditoría
            </a>
        @endif
    </div>
</aside>
