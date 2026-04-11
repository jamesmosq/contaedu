<x-tenant-layout :title="isset($resolucion) ? 'Editar Resolución' : 'Nueva Resolución'">

    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-forest-400 text-xs font-medium uppercase tracking-widest mb-1">Facturación electrónica</p>
                <h2 class="font-display text-2xl font-bold text-white">
                    {{ isset($resolucion) ? 'Editar Resolución' : 'Nueva Resolución de Autorización' }}
                </h2>
            </div>
            <a href="{{ fe_route('resoluciones.index') }}" class="shrink-0 text-sm text-forest-300 hover:text-white transition mt-1">← Resoluciones</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Explicación educativa --}}
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg text-sm text-blue-800">
                <strong>Ambiente de Pruebas (código 02).</strong>
                En ContaEdu siempre se opera en ambiente de pruebas. La clave técnica es generada automáticamente si no se especifica.
                Al registrar esta resolución, <strong>las anteriores quedan desactivadas</strong>.
            </div>

            @if($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-800 rounded-lg text-sm">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST"
                  action="{{ isset($resolucion) ? fe_route('resoluciones.update', $resolucion) : fe_route('resoluciones.store') }}"
                  class="bg-white rounded-xl border border-slate-200 p-6 space-y-4">
                @csrf
                @if(isset($resolucion))
                    @method('PUT')
                @endif

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Número de resolución DIAN <span class="text-red-500">*</span></label>
                    <input type="text" name="numero_resolucion"
                           value="{{ old('numero_resolucion', $resolucion->numero_resolucion ?? '18760000001') }}"
                           required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"
                           placeholder="Ej: 18760000001">
                    <p class="text-xs text-slate-400 mt-1">En educación se usa un número simulado.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Prefijo</label>
                    <input type="text" name="prefijo"
                           value="{{ old('prefijo', $resolucion->prefijo ?? 'SEDU') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"
                           placeholder="Ej: SEDU, FE, FV" maxlength="10">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Número desde <span class="text-red-500">*</span></label>
                        <input type="number" name="numero_desde"
                               value="{{ old('numero_desde', $resolucion->numero_desde ?? 1) }}"
                               required min="1" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Número hasta <span class="text-red-500">*</span></label>
                        <input type="number" name="numero_hasta"
                               value="{{ old('numero_hasta', $resolucion->numero_hasta ?? 1000) }}"
                               required min="1" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha desde <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_desde"
                               value="{{ old('fecha_desde', isset($resolucion) ? $resolucion->fecha_desde->toDateString() : now()->toDateString()) }}"
                               required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Fecha hasta <span class="text-red-500">*</span></label>
                        <input type="date" name="fecha_hasta"
                               value="{{ old('fecha_hasta', isset($resolucion) ? $resolucion->fecha_hasta->toDateString() : now()->addYear()->toDateString()) }}"
                               required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Clave técnica</label>
                    <input type="text" name="clave_tecnica"
                           value="{{ old('clave_tecnica', $resolucion->clave_tecnica ?? '') }}"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm font-mono"
                           placeholder="Se genera automáticamente si está vacío">
                    <p class="text-xs text-slate-400 mt-1">Identificador secreto asignado por la DIAN. En ContaEdu se simula con un UUID.</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Notas</label>
                    <textarea name="notas" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm"
                              placeholder="Opcional">{{ old('notas', $resolucion->notas ?? '') }}</textarea>
                </div>

                <div class="flex gap-3 justify-end pt-2">
                    <a href="{{ fe_route('resoluciones.index') }}"
                       class="px-5 py-2 bg-slate-100 text-slate-700 text-sm font-medium rounded-lg hover:bg-slate-200 transition">
                        Cancelar
                    </a>
                    <button type="submit"
                            class="px-5 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition">
                        {{ isset($resolucion) ? 'Guardar cambios' : 'Registrar resolución' }}
                    </button>
                </div>

            </form>

        </div>
    </div>
</x-tenant-layout>
