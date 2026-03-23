<x-tenant-layout title="Resoluciones DIAN">

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Resoluciones de Autorización DIAN</h2>
                <p class="text-sm text-slate-500 mt-0.5">Gestión de rangos de numeración autorizados</p>
            </div>
            <a href="{{ route('student.fe.index') }}" class="text-sm text-slate-500 hover:text-slate-700">← Facturación Electrónica</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-4 p-4 bg-green-50 border border-green-200 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
            @endif

            @if(! session('audit_mode'))
            <div class="flex justify-end mb-6">
                <a href="{{ route('student.fe.resoluciones.create') }}"
                   class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                    + Nueva Resolución
                </a>
            </div>
            @endif

            {{-- Explicación educativa --}}
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                <strong>¿Qué es una resolución de autorización?</strong>
                La DIAN autoriza un prefijo (Ej: "SEDU") y un rango de números (Ej: 1 al 1000) por un período de tiempo.
                Sin resolución vigente <strong>no se puede emitir ninguna factura electrónica</strong>.
                Al registrar una nueva resolución, la anterior queda desactivada automáticamente.
            </div>

            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">N° Resolución</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Prefijo</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Rango</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Disponibles</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Vigencia</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Estado</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($resoluciones as $res)
                        <tr class="hover:bg-slate-50 transition {{ $res->activa ? '' : 'opacity-60' }}">
                            <td class="px-4 py-3 font-mono font-semibold text-slate-800">{{ $res->numero_resolucion }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $res->prefijo ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-600">{{ $res->numero_desde }} — {{ $res->numero_hasta }}</td>
                            <td class="px-4 py-3">
                                @if($res->rangoAgotado())
                                    <span class="text-red-600 font-semibold">AGOTADO</span>
                                @else
                                    <span class="{{ $res->rangoDisponible() <= 5 ? 'text-amber-600 font-semibold' : 'text-slate-700' }}">
                                        {{ $res->rangoDisponible() }} números
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 text-xs">
                                {{ $res->fecha_desde->format('d/m/Y') }} al {{ $res->fecha_hasta->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($res->activa && $res->estaVigente())
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-800">Activa</span>
                                @elseif($res->activa && ! $res->estaVigente())
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">Vencida</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-500">Inactiva</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if(! session('audit_mode'))
                                <a href="{{ route('student.fe.resoluciones.edit', $res) }}"
                                   class="text-brand-700 hover:underline text-xs font-medium">Editar</a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-slate-400">
                                No hay resoluciones registradas.
                                <a href="{{ route('student.fe.resoluciones.create') }}" class="text-brand-700 hover:underline ml-1">Registrar la primera</a>.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-tenant-layout>
