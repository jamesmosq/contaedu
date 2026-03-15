<x-tenant-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $student->company_name }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">NIT {{ $student->nit_empresa }}</p>
            </div>
            <span class="px-3 py-1 bg-slate-100 text-slate-600 text-xs font-semibold rounded-full uppercase tracking-wide">Mi empresa</span>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Bienvenida --}}
            <div class="bg-gradient-to-r from-brand-900 to-brand-700 rounded-xl p-6 mb-6 text-white">
                <h3 class="text-lg font-bold mb-1">Bienvenido, {{ $student->student_name }}</h3>
                <p class="text-brand-200 text-sm">Tu empresa virtual está lista. Navega por los módulos para completar el ciclo contable.</p>
            </div>

            {{-- Módulos activos (Fase 2) --}}
            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Maestros contables</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <a href="{{ route('student.config') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-sm transition group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-lg bg-brand-50 flex items-center justify-center group-hover:bg-brand-100 transition">
                            <svg class="w-5 h-5 text-brand-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.764-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </div>
                        <h4 class="font-semibold text-slate-700 group-hover:text-brand-800 transition">Configuración</h4>
                    </div>
                    <p class="text-sm text-slate-400">Datos de la empresa y facturación</p>
                </a>

                <a href="{{ route('student.cuentas') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-sm transition group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-lg bg-accent-50 flex items-center justify-center group-hover:bg-accent-100 transition">
                            <svg class="w-5 h-5 text-accent-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg>
                        </div>
                        <h4 class="font-semibold text-slate-700 group-hover:text-brand-800 transition">Plan de cuentas</h4>
                    </div>
                    <p class="text-sm text-slate-400">PUC colombiano con subcuentas</p>
                </a>

                <a href="{{ route('student.terceros') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-sm transition group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-lg bg-brand-50 flex items-center justify-center group-hover:bg-brand-100 transition">
                            <svg class="w-5 h-5 text-brand-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                        </div>
                        <h4 class="font-semibold text-slate-700 group-hover:text-brand-800 transition">Terceros</h4>
                    </div>
                    <p class="text-sm text-slate-400">Clientes y proveedores</p>
                </a>

                <a href="{{ route('student.productos') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-sm transition group">
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-lg bg-accent-50 flex items-center justify-center group-hover:bg-accent-100 transition">
                            <svg class="w-5 h-5 text-accent-700" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                        </div>
                        <h4 class="font-semibold text-slate-700 group-hover:text-brand-800 transition">Productos</h4>
                    </div>
                    <p class="text-sm text-slate-400">Inventario con cuentas contables</p>
                </a>
            </div>

            {{-- Módulos Fase 3 --}}
            <h4 class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-3">Operaciones</h4>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <a href="{{ route('student.facturas') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-sm transition group">
                    <h4 class="font-semibold text-slate-700 group-hover:text-brand-800 mb-1">Facturación</h4>
                    <p class="text-sm text-slate-400">Facturas de venta y recibos de caja</p>
                </a>
                <a href="{{ route('student.compras') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-sm transition group">
                    <h4 class="font-semibold text-slate-700 group-hover:text-brand-800 mb-1">Compras</h4>
                    <p class="text-sm text-slate-400">Órdenes de compra y pagos a proveedores</p>
                </a>
                <a href="{{ route('student.reportes') }}" class="bg-white rounded-xl border border-slate-200 p-5 hover:border-brand-300 hover:shadow-sm transition group">
                    <h4 class="font-semibold text-slate-700 group-hover:text-brand-800 mb-1">Reportes</h4>
                    <p class="text-sm text-slate-400">Libro diario, balance general y estado de resultados</p>
                </a>
            </div>

        </div>
    </div>
</x-tenant-layout>
