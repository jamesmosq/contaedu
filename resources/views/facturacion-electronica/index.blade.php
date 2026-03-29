<x-tenant-layout title="Facturación Electrónica">

    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800">Facturación Electrónica</h2>
        <p class="text-sm text-slate-500 mt-0.5">Simulador DIAN — Ambiente de Pruebas (código 02)</p>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Resolución activa --}}
            @if($resolucionActiva)
                <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg flex flex-wrap gap-4 text-sm">
                    <div>
                        <span class="font-semibold text-blue-800">Resolución activa:</span>
                        <span class="text-blue-700">{{ $resolucionActiva->numero_resolucion }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-blue-800">Prefijo:</span>
                        <span class="text-blue-700">{{ $resolucionActiva->prefijo ?? '—' }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-blue-800">Rango disponible:</span>
                        <span class="text-blue-700">{{ $resolucionActiva->numero_actual }} / {{ $resolucionActiva->numero_hasta }}</span>
                    </div>
                    <div>
                        <span class="font-semibold text-blue-800">Vigencia:</span>
                        <span class="{{ $resolucionActiva->estaVigente() ? 'text-green-700' : 'text-red-700' }}">
                            {{ $resolucionActiva->fecha_desde->format('d/m/Y') }} — {{ $resolucionActiva->fecha_hasta->format('d/m/Y') }}
                            @if(! $resolucionActiva->estaVigente()) <strong>(VENCIDA)</strong> @endif
                        </span>
                    </div>
                </div>
            @else
                <div class="mb-6 p-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm">
                    No hay resolución activa. <a href="{{ route('student.fe.resoluciones.create') }}" class="underline font-semibold">Registrar resolución</a> para poder emitir facturas.
                </div>
            @endif

            {{-- Acciones --}}
            @if(! session('audit_mode'))
            <div class="flex gap-3 mb-6 justify-end">
                <a href="{{ route('student.fe.resoluciones.index') }}"
                   class="px-4 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition">
                    Resoluciones
                </a>
                @if($resolucionActiva && $resolucionActiva->estaVigente())
                <a href="{{ route('student.fe.crear') }}"
                   class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                    + Nueva Factura Electrónica
                </a>
                @endif
            </div>
            @endif

            {{-- Tabla de facturas --}}
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">N° Factura</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Adquirente</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Fecha</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Total</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Estado</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($facturas as $factura)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 font-mono font-semibold text-slate-800">
                                {{ $factura->numero_completo !== 'PENDIENTE' ? $factura->numero_completo : '(borrador)' }}
                            </td>
                            <td class="px-4 py-3 text-slate-700">
                                <div class="font-medium">{{ $factura->nombre_adquirente }}</div>
                                <div class="text-xs text-slate-400">{{ $factura->num_doc_adquirente }}</div>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $factura->fecha_emision->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right font-semibold text-slate-800">
                                ${{ number_format((float) $factura->total, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $factura->estado->badgeClasses() }}">
                                    {{ $factura->estado->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('student.fe.show', $factura) }}"
                                   class="text-brand-700 hover:underline text-xs font-medium">
                                    Ver detalle
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-400">
                                No hay facturas electrónicas registradas.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if($facturas->hasPages())
                <div class="px-4 py-3 border-t border-slate-200">
                    {{ $facturas->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>
</x-tenant-layout>
