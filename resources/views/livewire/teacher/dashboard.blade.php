<div>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ auth()->user()->name }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">Panel del docente</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('teacher.comparativo') }}" class="px-3 py-1.5 border border-slate-200 text-slate-600 text-xs font-medium rounded-lg hover:bg-slate-50 transition">
                    Ver comparativo
                </a>
                <span class="px-3 py-1 bg-accent-100 text-accent-800 text-xs font-semibold rounded-full uppercase tracking-wide">Docente</span>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Flash --}}
            @if(session('success'))
                <div class="mb-4 p-4 bg-accent-50 border border-accent-200 rounded-xl text-accent-700 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @forelse($groups as $group)
                {{-- Cabecera del grupo --}}
                <div class="bg-white rounded-xl border border-slate-200 p-6 mb-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-800">{{ $group->name }}</h3>
                            <p class="text-sm text-slate-500">{{ $group->institution->name }} · Período {{ $group->period }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="text-right">
                                <p class="text-2xl font-bold text-brand-800">{{ $group->tenants->count() }}</p>
                                <p class="text-xs text-slate-500">estudiantes</p>
                            </div>
                            <button wire:click="openCreate('single')" class="px-3 py-1.5 bg-brand-800 text-white text-xs font-medium rounded-lg hover:bg-brand-700 transition">
                                + Crear empresa
                            </button>
                            <button wire:click="openCreate('bulk')" class="px-3 py-1.5 border border-brand-800 text-brand-800 text-xs font-medium rounded-lg hover:bg-brand-50 transition">
                                ↑ Carga masiva
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Tabla de estudiantes con métricas --}}
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-8">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-slate-50 border-b border-slate-100">
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estudiante</th>
                                <th class="text-left px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Empresa</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Facturas</th>
                                <th class="text-right px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Total facturado</th>
                                <th class="text-center px-6 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                                <th class="px-6 py-3"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($students as $s)
                                @if($s['group']->id === $group->id)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-brand-100 rounded-full flex items-center justify-center text-brand-700 font-semibold text-xs">
                                                    {{ strtoupper(mb_substr($s['tenant']->student_name, 0, 2)) }}
                                                </div>
                                                <div>
                                                    <p class="font-medium text-slate-700">{{ $s['tenant']->student_name }}</p>
                                                    <p class="text-xs text-slate-400">{{ $s['tenant']->id }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <p class="text-slate-700">{{ $s['tenant']->company_name }}</p>
                                            <p class="text-xs text-slate-400">NIT {{ $s['tenant']->nit_empresa }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-right text-slate-700 font-medium">
                                            {{ number_format($s['metrics']['invoices_count']) }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-slate-700 font-medium">
                                            $ {{ number_format($s['metrics']['invoices_total'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $s['tenant']->active ? 'bg-accent-100 text-accent-700' : 'bg-red-100 text-red-700' }}">
                                                {{ $s['tenant']->active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <a href="{{ route('teacher.rubrica', $s['tenant']->id) }}" class="text-xs text-slate-500 hover:text-slate-700 font-medium transition">
                                                    Calificar
                                                </a>
                                                <a href="{{ route('teacher.auditar.start', $s['tenant']->id) }}" class="text-xs text-brand-700 hover:text-brand-900 font-medium transition">
                                                    Auditar →
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>

                    @if($group->tenants->isEmpty())
                        <div class="px-6 py-10 text-center text-slate-400 text-sm">
                            No hay empresas en este grupo.
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                    <div class="w-12 h-12 bg-slate-100 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                        </svg>
                    </div>
                    <p class="text-slate-500">Aún no tienes grupos asignados.</p>
                </div>
            @endforelse

        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════
         Modal: Crear empresa (individual o carga masiva)
    ════════════════════════════════════════════════════════════════ --}}
    @if($showCreateForm)
        <div class="fixed inset-0 bg-slate-900/60 z-40 flex items-center justify-center p-4" wire:click.self="$set('showCreateForm', false)">
            <div class="bg-white rounded-xl shadow-xl w-full {{ $createMode === 'bulk' ? 'max-w-3xl' : 'max-w-md' }}">

                {{-- Cabecera con tabs --}}
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex gap-1 bg-slate-100 rounded-lg p-1">
                        <button wire:click="switchMode('single')"
                            class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $createMode === 'single' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                            Crear uno
                        </button>
                        <button wire:click="switchMode('bulk')"
                            class="px-3 py-1.5 text-xs font-medium rounded-md transition {{ $createMode === 'bulk' ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                            Carga masiva
                        </button>
                    </div>
                    <button wire:click="$set('showCreateForm', false)" class="text-slate-400 hover:text-slate-600">✕</button>
                </div>

                {{-- ─── Tab individual ─────────────────────────────────── --}}
                @if($createMode === 'single')
                    <div class="px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Cédula del estudiante</label>
                            <input wire:model="cedula" type="text" placeholder="Ej: 1023456789"
                                class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                            @error('cedula') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nombre del estudiante</label>
                            <input wire:model="studentName" type="text" placeholder="Nombre completo"
                                class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                            @error('studentName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Razón social de la empresa</label>
                            <input wire:model="companyName" type="text" placeholder="Nombre de la empresa"
                                class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                            @error('companyName') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">NIT empresa</label>
                            <input wire:model="nitEmpresa" type="text" placeholder="Ej: 900123456-1"
                                class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                            @error('nitEmpresa') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Contraseña inicial</label>
                            <input wire:model="password" type="password" placeholder="Mínimo 6 caracteres"
                                class="block w-full rounded-lg border-slate-200 text-sm focus:ring-brand-500 focus:border-brand-500" />
                            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3">
                        <button wire:click="$set('showCreateForm', false)" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">Cancelar</button>
                        <button wire:click="createCompany" wire:loading.attr="disabled" class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition disabled:opacity-50">
                            <span wire:loading.remove wire:target="createCompany">Crear empresa</span>
                            <span wire:loading wire:target="createCompany">Creando…</span>
                        </button>
                    </div>

                {{-- ─── Tab carga masiva ────────────────────────────────── --}}
                @else
                    <div class="px-6 py-5 space-y-5">

                        {{-- Instrucciones + descarga plantilla --}}
                        <div class="bg-slate-50 rounded-lg p-4 text-sm text-slate-600">
                            <p class="font-medium text-slate-700 mb-1">Instrucciones</p>
                            <ol class="list-decimal list-inside space-y-1 text-xs">
                                <li>Descarga la plantilla CSV y ábrela en Excel o Google Sheets.</li>
                                <li>Completa una fila por estudiante (no modifiques los encabezados).</li>
                                <li>Guarda como <strong>CSV (separado por comas)</strong>.</li>
                                <li>Sube el archivo y revisa la vista previa antes de confirmar.</li>
                            </ol>
                            <a href="{{ route('teacher.plantilla') }}" target="_blank"
                                class="inline-flex items-center gap-1.5 mt-3 px-3 py-1.5 bg-white border border-slate-200 rounded-lg text-xs font-medium text-brand-700 hover:bg-brand-50 transition">
                                ↓ Descargar plantilla CSV
                            </a>
                        </div>

                        {{-- Selector de archivo --}}
                        @if(empty($bulkPreview) && empty($bulkResults))
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Archivo CSV</label>
                                <input wire:model="bulkFile" type="file" accept=".csv,text/csv"
                                    class="block w-full text-sm text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-50 file:text-brand-700 hover:file:bg-brand-100" />
                                @error('bulkFile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                                @if($bulkError)
                                    <p class="mt-2 text-xs text-red-600">{{ $bulkError }}</p>
                                @endif
                            </div>
                        @endif

                        {{-- Vista previa --}}
                        @if(!empty($bulkPreview))
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <p class="text-sm font-medium text-slate-700">
                                        Vista previa — {{ count($bulkPreview) }} fila(s)
                                    </p>
                                    <button wire:click="$set('bulkPreview', [])" class="text-xs text-slate-400 hover:text-slate-600">
                                        ← Cambiar archivo
                                    </button>
                                </div>
                                <div class="overflow-x-auto rounded-lg border border-slate-200">
                                    <table class="w-full text-xs">
                                        <thead>
                                            <tr class="bg-slate-50 border-b border-slate-100">
                                                <th class="text-left px-3 py-2 font-semibold text-slate-500">Cédula</th>
                                                <th class="text-left px-3 py-2 font-semibold text-slate-500">Estudiante</th>
                                                <th class="text-left px-3 py-2 font-semibold text-slate-500">Empresa</th>
                                                <th class="text-left px-3 py-2 font-semibold text-slate-500">NIT</th>
                                                <th class="text-center px-3 py-2 font-semibold text-slate-500">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($bulkPreview as $row)
                                                <tr class="{{ !empty($row['error']) ? 'bg-red-50' : 'bg-white' }}">
                                                    <td class="px-3 py-2 text-slate-700">{{ $row['cedula'] }}</td>
                                                    <td class="px-3 py-2 text-slate-700">{{ $row['student_name'] }}</td>
                                                    <td class="px-3 py-2 text-slate-700">{{ $row['company_name'] }}</td>
                                                    <td class="px-3 py-2 text-slate-700">{{ $row['nit_empresa'] }}</td>
                                                    <td class="px-3 py-2 text-center">
                                                        @if(empty($row['error']))
                                                            <span class="px-2 py-0.5 bg-accent-100 text-accent-700 rounded-full">OK</span>
                                                        @else
                                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full" title="{{ $row['error'] }}">Error</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @php $invalidCount = count(array_filter($bulkPreview, fn($r) => !empty($r['error']))); @endphp
                                @if($invalidCount > 0)
                                    <p class="mt-2 text-xs text-red-600">{{ $invalidCount }} fila(s) con errores no serán creadas.</p>
                                @endif
                            </div>
                        @endif

                        {{-- Resultados después de crear --}}
                        @if(!empty($bulkResults))
                            <div>
                                <p class="text-sm font-medium text-slate-700 mb-2">Resultados</p>
                                <div class="overflow-x-auto rounded-lg border border-slate-200">
                                    <table class="w-full text-xs">
                                        <thead>
                                            <tr class="bg-slate-50 border-b border-slate-100">
                                                <th class="text-left px-3 py-2 font-semibold text-slate-500">Cédula</th>
                                                <th class="text-left px-3 py-2 font-semibold text-slate-500">Estudiante</th>
                                                <th class="text-left px-3 py-2 font-semibold text-slate-500">Empresa</th>
                                                <th class="text-center px-3 py-2 font-semibold text-slate-500">Resultado</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            @foreach($bulkResults as $row)
                                                <tr class="{{ $row['status'] === 'ok' ? 'bg-white' : 'bg-red-50' }}">
                                                    <td class="px-3 py-2 text-slate-700">{{ $row['cedula'] }}</td>
                                                    <td class="px-3 py-2 text-slate-700">{{ $row['student_name'] }}</td>
                                                    <td class="px-3 py-2 text-slate-700">{{ $row['company_name'] }}</td>
                                                    <td class="px-3 py-2 text-center">
                                                        @if($row['status'] === 'ok')
                                                            <span class="px-2 py-0.5 bg-accent-100 text-accent-700 rounded-full">✓ Creado</span>
                                                        @else
                                                            <span class="px-2 py-0.5 bg-red-100 text-red-700 rounded-full" title="{{ $row['message'] }}">✗ Error</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- Acciones del tab bulk --}}
                    <div class="px-6 py-4 border-t border-slate-100 flex justify-between items-center">
                        <button wire:click="$set('showCreateForm', false)" class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800">
                            {{ empty($bulkResults) ? 'Cancelar' : 'Cerrar' }}
                        </button>

                        <div class="flex gap-3">
                            @if(empty($bulkPreview) && empty($bulkResults))
                                <button wire:click="processBulkFile" wire:loading.attr="disabled"
                                    class="px-4 py-2 bg-slate-700 text-white text-sm font-semibold rounded-lg hover:bg-slate-600 transition disabled:opacity-50">
                                    <span wire:loading.remove wire:target="processBulkFile">Vista previa</span>
                                    <span wire:loading wire:target="processBulkFile">Procesando…</span>
                                </button>
                            @endif

                            @if(!empty($bulkPreview))
                                @php $validCount = count(array_filter($bulkPreview, fn($r) => empty($r['error']))); @endphp
                                @if($validCount > 0)
                                    <button wire:click="confirmBulkCreate" wire:loading.attr="disabled"
                                        wire:confirm="¿Confirmar la creación de {{ $validCount }} empresa(s)? Esta acción no se puede deshacer."
                                        class="px-4 py-2 bg-brand-800 text-white text-sm font-semibold rounded-lg hover:bg-brand-700 transition disabled:opacity-50">
                                        <span wire:loading.remove wire:target="confirmBulkCreate">Crear {{ $validCount }} empresa(s)</span>
                                        <span wire:loading wire:target="confirmBulkCreate">Creando…</span>
                                    </button>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </div>
    @endif
</div>
