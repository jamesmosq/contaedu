<x-tenant-layout>
    @php
        $auditMode     = session('audit_mode');
        $demoMode      = session('demo_mode');
        $referenceMode = session('reference_mode');
        $readOnly      = $auditMode || $referenceMode;

        $r = $auditMode ? [
            'config'    => route('teacher.auditoria.config',    session('audit_tenant_id')),
            'cuentas'   => route('teacher.auditoria.cuentas',   session('audit_tenant_id')),
            'terceros'  => route('teacher.auditoria.terceros',  session('audit_tenant_id')),
            'productos' => route('teacher.auditoria.productos', session('audit_tenant_id')),
            'facturas'  => route('teacher.auditoria.facturas',  session('audit_tenant_id')),
            'compras'   => route('teacher.auditoria.compras',   session('audit_tenant_id')),
            'reportes'  => route('teacher.auditoria.reportes',  session('audit_tenant_id')),
        ] : ($demoMode ? [
            'config'    => route('teacher.demo.config',    session('demo_tenant_id')),
            'cuentas'   => route('teacher.demo.cuentas',   session('demo_tenant_id')),
            'terceros'  => route('teacher.demo.terceros',  session('demo_tenant_id')),
            'productos' => route('teacher.demo.productos', session('demo_tenant_id')),
            'facturas'  => route('teacher.demo.facturas',  session('demo_tenant_id')),
            'compras'   => route('teacher.demo.compras',   session('demo_tenant_id')),
            'reportes'  => route('teacher.demo.reportes',  session('demo_tenant_id')),
        ] : ($referenceMode ? [
            'config'    => route('student.referencia.config',    session('reference_tenant_id')),
            'cuentas'   => route('student.referencia.cuentas',   session('reference_tenant_id')),
            'terceros'  => route('student.referencia.terceros',  session('reference_tenant_id')),
            'productos' => route('student.referencia.productos', session('reference_tenant_id')),
            'facturas'  => route('student.referencia.facturas',  session('reference_tenant_id')),
            'compras'   => route('student.referencia.compras',   session('reference_tenant_id')),
            'reportes'  => route('student.referencia.reportes',  session('reference_tenant_id')),
        ] : [
            'config'    => route('student.config'),
            'cuentas'   => route('student.cuentas'),
            'terceros'  => route('student.terceros'),
            'productos' => route('student.productos'),
            'facturas'  => route('student.facturas'),
            'compras'   => route('student.compras'),
            'reportes'  => route('student.reportes'),
        ]));

        $moduleLabels = [
            'maestros'    => 'Maestros contables',
            'facturacion' => 'Facturación y cobro',
            'compras'     => 'Compras y pagos',
            'cierre'      => 'Cierre contable',
        ];
    @endphp

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $student->company_name }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">NIT {{ $student->nit_empresa }}</p>
            </div>
            <span class="px-3 py-1 text-xs font-semibold rounded-full uppercase tracking-wide
                {{ $auditMode ? 'bg-amber-100 text-amber-800' : ($demoMode ? 'bg-indigo-100 text-indigo-800' : ($referenceMode ? 'bg-sky-100 text-sky-800' : 'bg-slate-100 text-slate-600')) }}">
                {{ $auditMode ? 'Auditoría' : ($demoMode ? 'Demo' : ($referenceMode ? 'Referencia' : 'Mi empresa')) }}
            </span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            {{-- ── Hero bienvenida ──────────────────────────────────────────── --}}
            <div class="bg-gradient-to-br from-forest-900 via-forest-800 to-forest-950 rounded-2xl p-6 text-white">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">
                            {{ $auditMode ? 'Modo auditoría' : ($demoMode ? 'Empresa demo' : ($referenceMode ? 'Empresa de referencia' : 'Panel de empresa')) }}
                        </p>
                        <h3 class="text-xl font-bold text-white mb-1">
                            {{ $auditMode ? 'Empresa de '.$student->student_name : ($demoMode ? ($student->company_name ?? $student->student_name) : ($referenceMode ? ($student->company_name ?? $student->student_name) : 'Bienvenido, '.$student->student_name)) }}
                        </h3>
                        <p class="text-forest-300 text-sm">
                            {{ $auditMode ? 'Navegación de solo lectura — modo auditoría activo.' : ($demoMode ? 'Empresa de demostración — acceso completo.' : ($referenceMode ? 'Empresa de referencia — solo lectura.' : 'Tu empresa virtual está lista. Completa el ciclo contable completo.')) }}
                        </p>
                    </div>
                    <div class="shrink-0 w-14 h-14 bg-forest-700/50 rounded-2xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-gold-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                        </svg>
                    </div>
                </div>
            </div>

            {{-- ── KPIs ─────────────────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                {{-- Ventas --}}
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total facturado</p>
                        <div class="w-8 h-8 bg-green-50 rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">
                        ${{ number_format($summary?->monto_total_ventas ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $summary?->total_facturas_venta ?? 0 }} {{ ($summary?->total_facturas_venta ?? 0) === 1 ? 'factura emitida' : 'facturas emitidas' }}
                    </p>
                </div>

                {{-- Compras --}}
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total comprado</p>
                        <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-bold text-slate-800">
                        ${{ number_format($summary?->monto_total_compras ?? 0, 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ $summary?->total_facturas_compra ?? 0 }} {{ ($summary?->total_facturas_compra ?? 0) === 1 ? 'factura de compra' : 'facturas de compra' }}
                    </p>
                </div>

                {{-- Balance --}}
                <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-5">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Balance contable</p>
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center
                            {{ ($summary?->balance_cuadrado ?? false) ? 'bg-forest-50' : 'bg-amber-50' }}">
                            @if($summary?->balance_cuadrado ?? false)
                                <svg class="w-4 h-4 text-forest-600" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                </svg>
                            @else
                                <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                            @endif
                        </div>
                    </div>
                    <p class="text-2xl font-bold {{ ($summary?->balance_cuadrado ?? false) ? 'text-forest-700' : 'text-amber-600' }}">
                        {{ ($summary?->balance_cuadrado ?? false) ? 'Cuadrado' : 'Pendiente' }}
                    </p>
                    <p class="text-xs text-slate-400 mt-1">
                        {{ ($summary?->balance_cuadrado ?? false) ? 'Débitos = Créditos ✓' : 'Revisa el libro diario' }}
                    </p>
                </div>
            </div>

            {{-- ── Progreso + Anuncios ──────────────────────────────────────── --}}
            @if(! $readOnly)
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    {{-- Indicador de progreso --}}
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-sm font-bold text-slate-700">Progreso del ciclo contable</h4>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full
                                {{ $completedCount === count($progress) ? 'bg-forest-100 text-forest-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $completedCount }} / {{ count($progress) }}
                            </span>
                        </div>

                        {{-- Barra de progreso --}}
                        <div class="w-full bg-slate-100 rounded-full h-2 mb-5">
                            <div class="h-2 rounded-full transition-all duration-500
                                {{ $completedCount === count($progress) ? 'bg-forest-600' : 'bg-forest-500' }}"
                                style="width: {{ round(($completedCount / count($progress)) * 100) }}%">
                            </div>
                        </div>

                        {{-- Checklist --}}
                        <div class="space-y-2">
                            @foreach($progress as $item)
                                <a href="{{ $r[$item['key']] }}"
                                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition group
                                        {{ $item['done'] ? 'bg-forest-50/50' : 'hover:bg-slate-50' }}">
                                    <div class="w-5 h-5 rounded-full flex items-center justify-center shrink-0
                                        {{ $item['done'] ? 'bg-forest-600' : 'border-2 border-slate-300 bg-white' }}">
                                        @if($item['done'])
                                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                            </svg>
                                        @endif
                                    </div>
                                    <span class="text-sm {{ $item['done'] ? 'text-slate-600 line-through decoration-slate-300' : 'text-slate-700 group-hover:text-forest-800' }} transition">
                                        {{ $item['label'] }}
                                    </span>
                                    @if(! $item['done'])
                                        <svg class="w-3.5 h-3.5 text-slate-400 ml-auto opacity-0 group-hover:opacity-100 transition" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                        </svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Anuncios del docente --}}
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-6">
                        <h4 class="text-sm font-bold text-slate-700 mb-4">Anuncios del docente</h4>

                        @if($announcements->isEmpty())
                            <div class="flex flex-col items-center justify-center py-8 text-center">
                                <div class="w-12 h-12 bg-slate-50 rounded-2xl flex items-center justify-center mb-3">
                                    <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.34 15.84c-.688-.06-1.386-.09-2.09-.09H7.5a4.5 4.5 0 1 1 0-9h.75c.704 0 1.402-.03 2.09-.09m0 9.18c.253.962.584 1.892.985 2.783.247.55.06 1.21-.463 1.511l-.657.38c-.551.318-1.26.117-1.527-.461a20.845 20.845 0 0 1-1.44-4.282m3.102.069a18.03 18.03 0 0 1-.59-4.59c0-1.586.205-3.124.59-4.59m0 9.18a23.848 23.848 0 0 1 8.835 2.535M10.34 6.66a23.847 23.847 0 0 1 8.835-2.535m0 0A23.74 23.74 0 0 1 18.795 3m.38 1.125a23.91 23.91 0 0 1 1.014 5.395m-1.014 8.855c-.118.38-.245.754-.38 1.125m.38-1.125a23.91 23.91 0 0 0 1.014-5.395m0-3.46c.495.413.811 1.035.811 1.73 0 .695-.316 1.317-.811 1.73m0-3.46a24.347 24.347 0 0 1 0 3.46" />
                                    </svg>
                                </div>
                                <p class="text-sm text-slate-400">Sin anuncios por ahora</p>
                            </div>
                        @else
                            <div class="space-y-3">
                                @foreach($announcements as $ann)
                                    <div class="px-4 py-3 rounded-xl border
                                        {{ $ann->due_date && $ann->due_date->isPast() ? 'bg-red-50 border-red-100' : 'bg-forest-50/60 border-forest-100' }}">
                                        <div class="flex items-start justify-between gap-2">
                                            <p class="text-sm font-semibold text-slate-800">{{ $ann->title }}</p>
                                            @if($ann->due_date)
                                                <span class="text-xs font-medium shrink-0
                                                    {{ $ann->due_date->isPast() ? 'text-red-600' : 'text-gold-700' }}">
                                                    📅 {{ $ann->due_date->format('d/m/Y') }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($ann->body)
                                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ $ann->body }}</p>
                                        @endif
                                        <p class="text-xs text-slate-400 mt-1.5">{{ $ann->created_at->diffForHumans() }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- ── Retroalimentación del docente ───────────────────────── --}}
                @if($scores->isNotEmpty())
                    <div class="bg-white rounded-2xl border border-cream-200 shadow-card p-6">
                        <h4 class="text-sm font-bold text-slate-700 mb-4">Calificaciones del docente</h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($moduleLabels as $key => $label)
                                @if($scores->has($key))
                                    @php $score = $scores[$key]; @endphp
                                    <div class="flex items-start gap-3 px-4 py-3 rounded-xl
                                        {{ (float) $score->score >= 3.0 ? 'bg-forest-50 border border-forest-100' : 'bg-red-50 border border-red-100' }}">
                                        <div class="shrink-0 text-center">
                                            <p class="text-2xl font-bold {{ (float) $score->score >= 3.0 ? 'text-forest-700' : 'text-red-600' }}">
                                                {{ number_format((float) $score->score, 1) }}
                                            </p>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-sm font-semibold text-slate-700">{{ $label }}</p>
                                            @if($score->notes)
                                                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ $score->notes }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @php
                            $avg = $scores->avg(fn ($s) => (float) $s->score);
                        @endphp
                        <div class="mt-4 pt-4 border-t border-cream-100 flex items-center justify-between">
                            <p class="text-sm text-slate-500">Promedio de módulos calificados</p>
                            <p class="text-xl font-bold {{ $avg >= 3.0 ? 'text-forest-700' : 'text-red-600' }}">
                                {{ number_format($avg, 1) }}
                            </p>
                        </div>
                    </div>
                @endif
            @endif

            {{-- ── Módulos ────────────────────────────────────────────────── --}}
            <div>
                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Maestros contables</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ $r['config'] }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-forest-300 hover:shadow-sm transition group">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 rounded-lg bg-forest-50 flex items-center justify-center group-hover:bg-forest-100 transition">
                                <svg class="w-5 h-5 text-forest-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.764-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                            </div>
                            <h4 class="font-semibold text-slate-700 group-hover:text-forest-800 transition">Configuración</h4>
                        </div>
                        <p class="text-sm text-slate-400">Datos de la empresa y facturación</p>
                    </a>

                    <a href="{{ $r['cuentas'] }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-forest-300 hover:shadow-sm transition group">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 rounded-lg bg-gold-50 flex items-center justify-center group-hover:bg-gold-100 transition">
                                <svg class="w-5 h-5 text-gold-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg>
                            </div>
                            <h4 class="font-semibold text-slate-700 group-hover:text-forest-800 transition">Plan de cuentas</h4>
                        </div>
                        <p class="text-sm text-slate-400">PUC colombiano con subcuentas</p>
                    </a>

                    <a href="{{ $r['terceros'] }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-forest-300 hover:shadow-sm transition group">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 rounded-lg bg-forest-50 flex items-center justify-center group-hover:bg-forest-100 transition">
                                <svg class="w-5 h-5 text-forest-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                            </div>
                            <h4 class="font-semibold text-slate-700 group-hover:text-forest-800 transition">Terceros</h4>
                        </div>
                        <p class="text-sm text-slate-400">Clientes y proveedores</p>
                    </a>

                    <a href="{{ $r['productos'] }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-forest-300 hover:shadow-sm transition group">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 rounded-lg bg-gold-50 flex items-center justify-center group-hover:bg-gold-100 transition">
                                <svg class="w-5 h-5 text-gold-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                            </div>
                            <h4 class="font-semibold text-slate-700 group-hover:text-forest-800 transition">Productos</h4>
                        </div>
                        <p class="text-sm text-slate-400">Inventario con cuentas contables</p>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Operaciones</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <a href="{{ $r['facturas'] }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-forest-300 hover:shadow-sm transition group">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition">
                                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-slate-700 group-hover:text-forest-800 transition">Facturación</h4>
                        </div>
                        <p class="text-sm text-slate-400">Facturas de venta, notas crédito/débito y recibos de caja</p>
                        @if(($summary?->total_facturas_venta ?? 0) > 0)
                            <p class="text-xs font-semibold text-green-600 mt-2">{{ $summary->total_facturas_venta }} emitidas</p>
                        @endif
                    </a>
                    <a href="{{ $r['compras'] }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-forest-300 hover:shadow-sm transition group">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition">
                                <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-slate-700 group-hover:text-forest-800 transition">Compras</h4>
                        </div>
                        <p class="text-sm text-slate-400">Órdenes de compra y pagos a proveedores</p>
                        @if(($summary?->total_facturas_compra ?? 0) > 0)
                            <p class="text-xs font-semibold text-blue-600 mt-2">{{ $summary->total_facturas_compra }} registradas</p>
                        @endif
                    </a>
                    <a href="{{ $r['reportes'] }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-forest-300 hover:shadow-sm transition group">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="w-9 h-9 rounded-lg bg-forest-50 flex items-center justify-center group-hover:bg-forest-100 transition">
                                <svg class="w-5 h-5 text-forest-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                            </div>
                            <h4 class="font-semibold text-slate-700 group-hover:text-forest-800 transition">Reportes</h4>
                        </div>
                        <p class="text-sm text-slate-400">Libro diario, balance general y estado de resultados</p>
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-tenant-layout>
